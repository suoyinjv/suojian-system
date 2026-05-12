<?php
namespace Admin\Controller;

/**
 * 财务管理控制器
 */
class FinanceController extends CommonController {
    
    /**
     * 财务首页 — 数据概览
     */
    public function index() {
        // 总收入
        $total_income = M('order')->where(['status'=>['in', '1,2']])->sum('pay_amount');
        // 总支出
        $total_expense = M('expense')->sum('amount');
        // 净利润
        $total_profit = ($total_income ?: 0) - ($total_expense ?: 0);
        // 待退款数
        $refund_count = M('refund')->where(['status'=>0])->count();
        
        $this->assign('total_income', $total_income ?: 0);
        $this->assign('total_expense', $total_expense ?: 0);
        $this->assign('total_profit', $total_profit);
        $this->assign('refund_count', $refund_count ?: 0);
        $this->display();
    }
    
    /**
     * 收入报表
     */
    public function income() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $where = ['status'=>['in', '1,2']];
        
        $keyword = I('keyword', '', 'trim');
        if ($keyword) {
            $where['order_no'] = ['like', "%{$keyword}%"];
        }
        $type = I('type', 0, 'intval');
        if ($type > 0) {
            $where['type'] = $type;
        }
        
        // 搜索参数回传
        $this->assign('param', ['keyword'=>$keyword, 'type'=>$type]);
        
        $prefix = C('DB_PREFIX');
        $count = M('order')->where($where)->count();
        
        $list = M('order')->where($where)
            ->order('create_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $students = M('student')->getField('id,student_name', true);
        $type_map = [1=>'课程报名', 2=>'资料购买', 3=>'续费', 4=>'其他'];
        $pay_map = [1=>'微信', 2=>'支付宝', 3=>'现金', 4=>'银行卡'];
        
        foreach ($list as &$item) {
            $item['student_name'] = $students[$item['student_id']] ?: '';
            $item['amount'] = $item['pay_amount'];
            $item['pay_method'] = $pay_map[$item['pay_type']] ?: $item['pay_type'];
            $item['add_time'] = date('Y-m-d H:i', $item['create_time']);
            $item['type_text'] = $type_map[$item['type']] ?: '其他';
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page);
        
        $totalPage = ceil($count/$limit);
        $this->assign('total', $totalPage);
        
        // 生成分页HTML
        $pageHtml = '';
        if ($totalPage > 1) {
            $pageHtml = '<div class="am-pagination"><ul>';
            for ($i = 1; $i <= $totalPage; $i++) {
                $active = $i == $page ? ' class="am-active"' : '';
                $url = U('Admin/Finance/Income', ['page'=>$i, 'keyword'=>$keyword, 'type'=>$type]);
                $pageHtml .= "<li{$active}><a href=\"{$url}\">{$i}</a></li>";
            }
            $pageHtml .= '</ul></div>';
        }
        $this->assign('page_html', $pageHtml);
        
        // 统计
        $total = M('order')->where($where)->sum('pay_amount');
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
        $pay_type_map = [1=>'微信', 2=>'支付宝', 3=>'现金', 4=>'银行卡'];
        
        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '其他';
            $item['pay_type_text'] = $pay_type_map[$item['pay_type']] ?: '其他';
            $item['add_time_text'] = date('Y-m-d H:i', $item['add_time']);
        }
        
        $this->assign('list', $list);
        
        $totalPage = ceil($count/$limit);
        // 分页
        $pageHtml = '';
        if ($totalPage > 1) {
            $pageHtml = '<div class="am-pagination"><ul>';
            for ($i = 1; $i <= $totalPage; $i++) {
                $active = $i == $page ? ' class="am-active"' : '';
                $url = U('Admin/Finance/Expense', ['page'=>$i]);
                $pageHtml .= "<li{$active}><a href=\"{$url}\">{$i}</a></li>";
            }
            $pageHtml .= '</ul></div>';
        }
        $this->assign('page_html', $pageHtml);
        $this->assign('total_expense', M('expense')->sum('amount') ?: 0);
        
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
            $item['add_time_text'] = date('Y-m-d H:i', $item['add_time']);
            $item['audit_time_text'] = $item['audit_time'] ? date('Y-m-d H:i', $item['audit_time']) : '-';
        }
        
        $this->assign('list', $list);
        
        $totalPage = ceil($count/$limit);
        $pageHtml = '';
        if ($totalPage > 1) {
            $pageHtml = '<div class="am-pagination"><ul>';
            for ($i = 1; $i <= $totalPage; $i++) {
                $active = $i == $page ? ' class="am-active"' : '';
                $url = U('Admin/Finance/Refund', ['page'=>$i]);
                $pageHtml .= "<li{$active}><a href=\"{$url}\">{$i}</a></li>";
            }
            $pageHtml .= '</ul></div>';
        }
        $this->assign('page_html', $pageHtml);
        
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
        $year_start = strtotime(date('Y-01-01'));
        
        // 总统计
        $total_income = M('order')->where(['status'=>['in', '1,2']])->sum('pay_amount');
        $total_expense = M('expense')->sum('amount');
        $total_profit = ($total_income ?: 0) - ($total_expense ?: 0);
        
        // 本月收入
        $month_income = M('order')->where([
            'create_time'=>['egt', $month_start],
            'status'=>['in', '1,2']
        ])->sum('pay_amount');
        
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
        $month_profit = ($month_income ?: 0) - ($month_expense ?: 0) - ($month_refund ?: 0);
        
        // 近30天每日收支明细（图表数据）
        $days = [];
        $income_list = [];
        $expense_list = [];
        $profit_list = [];
        $list = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $day_start = strtotime(date('Y-m-d', strtotime("-{$i} days")));
            $day_end = $day_start + 86400;
            $date_str = date('Y-m-d', $day_start);
            
            // 当日收入
            $day_income = M('order')->where([
                'create_time'=>[['egt', $day_start], ['lt', $day_end]],
                'status'=>['in', '1,2']
            ])->sum('pay_amount');
            
            // 当日支出
            $day_expense = M('expense')->where([
                'add_time'=>[['egt', $day_start], ['lt', $day_end]]
            ])->sum('amount');
            
            $day_income = $day_income ?: 0;
            $day_expense = $day_expense ?: 0;
            $day_profit = $day_income - $day_expense;
            
            $days[] = "'{$date_str}'";
            $income_list[] = $day_income;
            $expense_list[] = $day_expense;
            $profit_list[] = $day_profit;
            
            $list[] = [
                'date' => $date_str,
                'income' => $day_income,
                'expense' => $day_expense,
                'profit' => $day_profit,
            ];
        }
        
        $this->assign('total_income', $total_income ?: 0);
        $this->assign('total_expense', $total_expense ?: 0);
        $this->assign('total_profit', $total_profit);
        $this->assign('month_income', $month_income ?: 0);
        $this->assign('month_expense', $month_expense ?: 0);
        $this->assign('month_refund', $month_refund ?: 0);
        $this->assign('month_profit', $month_profit);
        $this->assign('list', $list);
        $this->assign('date_list', '[' . implode(',', $days) . ']');
        $this->assign('income_list', '[' . implode(',', $income_list) . ']');
        $this->assign('expense_list', '[' . implode(',', $expense_list) . ']');
        $this->assign('profit_list', '[' . implode(',', $profit_list) . ']');
        
        $this->display();
    }
}
