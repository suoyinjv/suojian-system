<?php
namespace Admin\Controller;
use Think\Controller;

class OrderController extends Controller {
    
    // 订单列表
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $status = I('status', -1, 'intval');
        $keyword = I('keyword', '');
        $start_date = I('start_date', '');
        $end_date = I('end_date', '');
        
        $where = [];
        if ($status >= 0) $where['o.status'] = $status;
        if ($keyword) {
            $where['o.order_no|s.name|st.phone'] = ['like', "%{$keyword}%"];
        }
        if ($start_date) $where['o.create_time'] = ['>=', strtotime($start_date)];
        if ($end_date) $where['o.create_time'] = ['<=', strtotime($end_date . ' 23:59:59')];
        
        $list = M('order')
            ->alias('o')
            ->field('o.*,s.name as student_name,s.phone')
            ->join('LEFT JOIN sc_student s ON o.student_id=s.id')
            ->where($where)
            ->order('o.create_time desc')
            ->page($page, $rows)
            ->select();
            
        $total = M('order')
            ->alias('o')
            ->where($where)
            ->count();
            
        $status_arr = [0=>'待支付',1=>'已支付',2=>'已取消',3=>'已退款'];
        $pay_type_arr = ['cash'=>'现金', 'wechat'=>'微信', 'alipay'=>'支付宝', 'bank'=>'银行转账'];
        
        foreach ($list as &$v) {
            $v['status_text'] = $status_arr[$v['status']];
            $v['pay_type_text'] = $pay_type_arr[$v['pay_type']] ?: '-';
            $v['create_date'] = date('Y-m-d H:i', $v['create_time']);
            $v['pay_date'] = $v['pay_time'] ? date('Y-m-d H:i', $v['pay_time']) : '-';
        }
        
        $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
    }
    
    // 创建订单
    public function add() {
        $data = I('post.');
        $data['order_no'] = 'ORD' . date('YmdHis') . rand(100,999);
        $data['create_time'] = time();
        
        $result = M('order')->add($data);
        if ($result) {
            // 同步创建学员课时
            if ($data['total_hours'] > 0) {
                M('student_course')->add([
                    'student_id' => $data['student_id'],
                    'course_id' => $data['course_id'],
                    'order_id' => $result,
                    'total_hours' => $data['total_hours'],
                    'remaining_hours' => $data['total_hours'],
                    'used_hours' => 0,
                    'status' => 1,
                    'create_time' => time()
                ]);
            }
        }
        
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'创建成功':'创建失败']);
    }
    
    // 支付
    public function pay() {
        $id = I('id', 0, 'intval');
        $pay_type = I('pay_type', 'wechat');
        
        $order = M('order')->find($id);
        if (!$order) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'订单不存在']);
        }
        if ($order['status'] != 0) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'订单状态不正确']);
        }
        
        $result = M('order')->save([
            'id' => $id,
            'status' => 1,
            'pay_type' => $pay_type,
            'pay_time' => time(),
            'pay_amount' => $order['total_amount']
        ]);
        
        $this->ajaxReturn(['code'=>$result!==false?1:0, 'msg'=>$result!==false?'支付成功':'支付失败']);
    }
    
    // 退款
    public function refund() {
        $id = I('id', 0, 'intval');
        $remark = I('remark', '');
        
        $order = M('order')->find($id);
        if ($order['status'] != 1) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'只有已支付订单可退款']);
        }
        
        $result = M('order')->save([
            'id' => $id,
            'status' => 3,
            'remark' => $remark
        ]);
        
        // TODO: 扣减学员课时
        
        $this->ajaxReturn(['code'=>$result!==false?1:0, 'msg'=>$result!==false?'退款成功':'退款失败']);
    }
    
    // 统计
    public function statistics() {
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));
        
        $where = "create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59')";
        
        // 订单统计
        $total_orders = M('order')->where($where)->count();
        $paid_orders = M('order')->where($where . " AND status=1")->count();
        $total_amount = M('order')->where($where . " AND status=1")->sum('total_amount');
        $pay_amount = M('order')->where($where . " AND status=1")->sum('pay_amount');
        
        // 每日趋势
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, 
                       COUNT(*) as cnt, SUM(pay_amount) as amount 
                FROM sc_order 
                WHERE {$where} AND status=1 
                GROUP BY DATE(FROM_UNIXTIME(create_time))";
        $daily_stats = M()->query($sql);
        
        // 支付方式分布
        $pay_type_stats = M('order')->field('pay_type, COUNT(*) as cnt, SUM(pay_amount) as amount')
            ->where($where . " AND status=1 AND pay_type!=''")
            ->group('pay_type')
            ->select();
        
        $this->ajaxReturn([
            'total_orders' => $total_orders,
            'paid_orders' => $paid_orders,
            'total_amount' => $total_amount ?: 0,
            'pay_amount' => $pay_amount ?: 0,
            'daily_stats' => $daily_stats,
            'pay_type_stats' => $pay_type_stats
        ]);
    }
}