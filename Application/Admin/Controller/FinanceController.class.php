<?php
namespace Admin\Controller;

/**
 * 财务管理控制器
 */
class FinanceController extends CommonController {
    
    /**
     * 收入报表
     */
    public function income() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $start_time = I('start_time', 0, 'strtotime');
        $end_time = I('end_time', 0, 'strtotime');
        
        $where = ['status'=>['in', '1,2']];
        if ($start_time) {
            $where['add_time'] = ['egt', $start_time];
        }
        if ($end_time) {
            $where['add_time'] = ['elt', $end_time + 86400];
        }
        
        $prefix = C('DB_PREFIX');
        $count = M('order')->where($where)->count();
        
        $list = M('order')->where($where)
            ->order('add_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $courses = M('course')->getField('id,course_name', true);
        $students = M('student')->getField('id,student_name', true);
        
        $pay_map = [1=>'微信', 2=>'支付宝', 3=>'现金', 4=>'银行卡'];
        
        foreach ($list as &$item) {
            $item['course_name'] = $courses[$item['course_id']] ?: '';
            $item['student_name'] = $students[$item['student_id']] ?: '';
            $item['pay_type_text'] = $pay_map[$item['pay_type']] ?: '其他';
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        
        // 统计
        $total = M('order')->where($where)->sum('money');
        $this->assign('total_income', $total ?: 0);
        
        $this->display();
    }
    
    /**
     * 支出管理
     */
    public function expense() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $count = M('expense')->count();
        $list = M('expense')->order('add_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $type_map = [1=>'工资', 2=>'房租', 3=>'广告', 4=>'物料', 5=>'其他'];
        
        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '其他';
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 添加支出
     */
    public function addExpense() {
        if (IS_POST) {
            $data = [
                'title' => I('post.title', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'amount' => I('post.amount', 0, 'floatval'),
                'pay_type' => I('post.pay_type', 1, 'intval'),
                'campus_id' => I('post.campus_id', 0, 'intval'),
                'handler' => I('post.handler', '', 'trim'),
                'remark' => I('post.remark', '', 'trim'),
                'add_time' => time(),
            ];
            
            M('expense')->add($data);
            $this->success('添加成功', U('expense'));
        }
        
        $campuses = M('campus')->getField('id,name', true);
        $this->assign('campuses', $campuses);
        $this->display();
    }
    
    /**
     * 退款管理
     */
    public function refund() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $count = M('refund')->count();
        $list = M('refund')->order('add_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $students = M('student')->getField('id,student_name', true);
        $status_map = [0=>'待审核', 1=>'已退款', 2=>'已拒绝'];
        
        foreach ($list as &$item) {
            $item['student_name'] = $students[$item['student_id']] ?: '';
            $item['status_text'] = $status_map[$item['status']];
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 退款审核
     */
    public function auditRefund() {
        $id = I('id', 0, 'intval');
        $status = I('status', 1, 'intval');
        
        M('refund')->where(['id'=>$id])->save([
            'status' => $status,
            'audit_time' => time()
        ]);
        
        // 如果退款成功，更新原订单状态
        if ($status == 1) {
            $refund = M('refund')->find($id);
            M('order')->where(['id'=>$refund['order_id']])->save(['status'=>3]);
        }
        
        $this->success('审核完成');
    }
    
    /**
     * 财务统计
     */
    public function statistics() {
        $month_start = strtotime(date('Y-m-01'));
        
        // 本月收入
        $month_income = M('order')->where([
            'add_time'=>['egt', $month_start],
            'status'=>['in', '1,2']
        ])->sum('money');
        
        // 本月支出
        $month_expense = M('expense')->where([
            'add_time'=>['egt', $month_start]
        ])->sum('amount');
        
        // 本月退款
        $month_refund = M('refund')->where([
            'add_time'=>['egt', $month_start],
            'status'=>1
        ])->sum('amount');
        
        // 本月利润
        $month_profit = $month_income - $month_expense - $month_refund;
        
        $this->assign('month_income', $month_income ?: 0);
        $this->assign('month_expense', $month_expense ?: 0);
        $this->assign('month_refund', $month_refund ?: 0);
        $this->assign('month_profit', $month_profit);
        
        $this->display();
    }
}