<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 订单管理
 * HTML页面 + JSON API双模式
 */
class OrderController extends Controller {
    
    // 多租户 — 当前校区ID
    protected $tenant_campus_id = 0;
    
    /**
     * 初始化
     */
    protected function _initialize() {
        $this->tenant_campus_id = GetTenantCampusId();
    }
    
    /**
     * 订单列表（页面 / JSON API）
     */
    public function index() {
        $is_json = I('json', 0, 'intval');
        
        if ($is_json) {
            $page = I('page', 1, 'intval');
            $rows = I('rows', 20, 'intval');
            $status = I('status', -1, 'intval');
            $keyword = I('keyword', '');
            $start_date = I('start_date', '');
            $end_date = I('end_date', '');
            
            $where = ['o.campus_id' => $this->tenant_campus_id];
            if ($status >= 0) $where['o.status'] = $status;
            if ($keyword) {
                $where['o.order_no|s.username'] = ['like', "%{$keyword}%"];
            }
            if ($start_date) $where['o.create_time'] = ['>=', strtotime($start_date)];
            if ($end_date) $where['o.create_time'] = ['<=', strtotime($end_date . ' 23:59:59')];
            
            $list = M('order')
                ->alias('o')
                ->field('o.*,s.username as student_name,s.my_mobile as phone')
                ->join('LEFT JOIN sc_student s ON o.student_id=s.id')
                ->where($where)
                ->order('o.create_time desc')
                ->page($page, $rows)
                ->select();
                
            $total = M('order')
                ->alias('o')
                ->where($where)
                ->count();
                
            $status_arr = [0=>'待支付',1=>'已支付',2=>'已完成',3=>'已退款'];
            $pay_type_arr = ['1'=>'微信', '2'=>'支付宝', '3'=>'现金', '4'=>'银行卡'];
            
            foreach ($list as &$v) {
                $v['status_text'] = $status_arr[$v['status']] ?: '未知';
                $v['pay_type_text'] = $pay_type_arr[$v['pay_type']] ?: '-';
                $v['create_date'] = $v['create_time'] ? date('Y-m-d H:i', $v['create_time']) : '-';
                $v['pay_date'] = $v['pay_time'] ? date('Y-m-d H:i', $v['pay_time']) : '-';
                $v['course_name'] = $v['course_name'] ?: '-';
            }
            
            $this->ajaxReturn(['total'=>$total, 'rows'=>$list]);
        }
        
        $this->display();
    }
    
    /**
     * 创建订单
     */
    public function add() {
        $data = I('post.');
        $data['order_no'] = 'ORD' . date('YmdHis') . rand(100,999);
        $data['create_time'] = time();
        $data['campus_id'] = $this->tenant_campus_id;
        $data['pay_amount'] = I('post.total_amount', 0, 'floatval');
        
        $result = M('order')->add($data);
        
        if (I('json', 0, 'intval')) {
            $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'创建成功':'创建失败']);
        }
        
        if ($result) {
            $this->success('创建成功', U('index'));
        } else {
            $this->error('创建失败');
        }
    }
    
    /**
     * 支付
     */
    public function pay() {
        $id = I('id', 0, 'intval');
        $pay_type = I('pay_type', '1');
        
        $order = M('order')->where(['id' => $id, 'campus_id' => $this->tenant_campus_id])->find();
        if (!$order) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'订单不存在']);
        }
        if ($order['status'] != 0) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'订单状态不正确']);
        }
        
        M('order')->save([
            'id' => $id,
            'status' => 1,
            'pay_type' => $pay_type,
            'pay_time' => time(),
            'pay_amount' => $order['total_amount']
        ]);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'支付成功']);
    }
    
    /**
     * 退款
     */
    public function refund() {
        $id = I('id', 0, 'intval');
        $remark = I('remark', '');
        
        $order = M('order')->where(['id' => $id, 'campus_id' => $this->tenant_campus_id])->find();
        if ($order['status'] != 1) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'只有已支付订单可退款']);
        }
        
        M('order')->save([
            'id' => $id,
            'status' => 3,
            'remark' => $remark
        ]);
        
        M('refund')->add([
            'order_id' => $id,
            'student_id' => $order['student_id'],
            'amount' => $order['pay_amount'],
            'reason' => $remark,
            'status' => 0,
            'add_time' => time(),
            'campus_id' => $this->tenant_campus_id
        ]);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'退款成功']);
    }
    
    /**
     * 订单统计（页面 / JSON API）
     */
    public function statistics() {
        $is_json = I('json', 0, 'intval');
        
        if ($is_json) {
            $start_date = I('start_date', date('Y-m-01'));
            $end_date = I('end_date', date('Y-m-d'));
            
            $where = "campus_id = {$this->tenant_campus_id} AND create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59')";
            
            $total_orders = M('order')->where($where)->count();
            $paid_orders = M('order')->where($where . " AND status=1")->count();
            $total_amount = M('order')->where($where . " AND status=1")->sum('total_amount') ?: 0;
            $pay_amount = M('order')->where($where . " AND status=1")->sum('pay_amount') ?: 0;
            
            // 退款总额
            $refund_where = "campus_id = {$this->tenant_campus_id} AND add_time >= UNIX_TIMESTAMP('{$start_date}') AND add_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59') AND status=1";
            $refund_amount = M('refund')->where($refund_where)->sum('amount') ?: 0;
            
            // 净收入
            $net_amount = $pay_amount - $refund_amount;
            
            // 支付方式分布
            $pay_type_stats = M('order')->field('pay_type, COUNT(*) as cnt, SUM(pay_amount) as amount')
                ->where($where . " AND status=1 AND pay_type!=''")
                ->group('pay_type')
                ->select();
            
            $pay_types = [];
            foreach ($pay_type_stats as $pt) {
                $pay_types[$pt['pay_type']] = ['count'=>$pt['cnt'], 'amount'=>$pt['amount']];
            }
            
            // 订单状态统计
            $status_stats = [];
            $rows = M('order')->field('status, COUNT(*) as cnt')
                ->where($where)
                ->group('status')
                ->select();
            foreach ($rows as $r) {
                $status_stats[$r['status']] = $r['cnt'];
            }
            
            $this->ajaxReturn([
                'code' => 1,
                'data' => [
                    'total_orders' => $total_orders,
                    'paid_orders' => $paid_orders,
                    'total_amount' => $total_amount,
                    'pay_amount' => $pay_amount,
                    'refund_amount' => $refund_amount,
                    'net_amount' => $net_amount,
                    'daily_stats' => [], 
                    'pay_type_stats' => $pay_types,
                    'status_stats' => $status_stats,
                ]
            ]);
        }
        
        $this->display();
    }
    
    public function stats() {
        $this->redirect('statistics');
    }
}
