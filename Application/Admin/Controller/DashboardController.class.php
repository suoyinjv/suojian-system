<?php
namespace Admin\Controller;
use Think\Controller;

class DashboardController extends Controller {
    
    public function index() {
        $this->assignData();
        $this->display();
    }
    
    public function overview() {
        $this->assignData();
        $this->display();
    }
    
    // 招生数据
    public function enrollment() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, COUNT(*) as cnt 
                FROM sc_student 
                WHERE create_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time))
                ORDER BY date";
        $daily_new = M()->query($sql);
        
        $source_stats = M('student')
            ->field('source, COUNT(*) as cnt')
            ->where("create_time >= UNIX_TIMESTAMP('{$start_date}')")
            ->group('source')
            ->select();
        
        $leads_stats = M('leads')
            ->field('status, COUNT(*) as cnt')
            ->group('status')
            ->select();
        
        $this->assign('daily_new', $daily_new ?: []);
        $this->assign('source_stats', $source_stats ?: []);
        $this->assign('leads_stats', $leads_stats ?: []);
        $this->display();
    }
    
    // 教务数据
    public function academic() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $sql = "SELECT attend_date as date, COUNT(*) as cnt, SUM(hours) as hours 
                FROM sc_attendance 
                WHERE attend_date >= '{$start_date}'
                GROUP BY attend_date ORDER BY date";
        $attendance_stats = M()->query($sql);
        
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, 
                       SUM(hours) as hours, COUNT(*) as cnt 
                FROM sc_consumption 
                WHERE create_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time)) ORDER BY date";
        $consumption_stats = M()->query($sql);
        
        $course_stats = M('consumption')
            ->alias('c')
            ->field('co.course_name, SUM(c.hours) as hours')
            ->join('LEFT JOIN sc_course co ON c.course_id = co.id')
            ->group('c.course_id')
            ->order('hours DESC')
            ->limit(10)
            ->select();
        
        $class_stats = M('attendance')
            ->alias('a')
            ->field('cl.name, COUNT(*) as cnt, SUM(a.hours) as hours')
            ->join('LEFT JOIN sc_class cl ON a.class_id = cl.id')
            ->group('a.class_id')
            ->order('hours DESC')
            ->limit(10)
            ->select();
        
        $this->assign('attendance_stats', $attendance_stats ?: []);
        $this->assign('consumption_stats', $consumption_stats ?: []);
        $this->assign('course_stats', $course_stats ?: []);
        $this->assign('class_stats', $class_stats ?: []);
        $this->display();
    }
    
    // 财务数据
    public function finance() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, 
                       SUM(pay_amount) as amount, COUNT(*) as cnt 
                FROM sc_order 
                WHERE status=1 AND create_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time)) ORDER BY date";
        $daily_revenue = M()->query($sql);
        
        $pay_type_stats = M('order')
            ->field('pay_type, COUNT(*) as cnt, SUM(pay_amount) as amount')
            ->where(['status'=>1])
            ->group('pay_type')
            ->select();
        
        $course_revenue = M('order')
            ->alias('o')
            ->field('co.course_name, SUM(o.pay_amount) as amount')
            ->join('LEFT JOIN sc_course co ON o.course_id = co.id')
            ->where(['o.status'=>1])
            ->group('o.course_id')
            ->order('amount DESC')
            ->limit(5)
            ->select();
        
        $this->assign('daily_revenue', $daily_revenue ?: []);
        $this->assign('pay_type_stats', $pay_type_stats ?: []);
        $this->assign('course_revenue', $course_revenue ?: []);
        $this->display();
    }
    
    // 实时数据（备用）
    public function realtime() {
        $this->display();
    }
    
    // 公共方法：统计数据并赋值给视图
    private function assignData() {
        $today = date('Y-m-d');
        $month_start = date('Y-m-01');
        
        // 今日
        $today_attendance = M('attendance')->where(['attend_date'=>$today])->count();
        $today_consumption = M('consumption')->where("FROM_UNIXTIME(create_time,'%Y-%m-%d')='{$today}'")->sum('hours');
        $today_new_students = M('student')->where("FROM_UNIXTIME(create_time,'%Y-%m-%d')='{$today}'")->count();
        $today_orders = M('order')->where("status=1 AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')='{$today}'")->count();
        $today_revenue = M('order')->where("status=1 AND FROM_UNIXTIME(pay_time,'%Y-%m-%d')='{$today}'")->sum('pay_amount');
        
        // 本月
        $month_revenue = M('order')->where("status=1 AND pay_time>=".strtotime($month_start))->sum('pay_amount');
        $month_orders = M('order')->where("status=1 AND create_time>=".strtotime($month_start))->count();
        $month_new = M('student')->where("create_time>=".strtotime($month_start))->count();
        $month_attendance = M('attendance')->where("attend_date>='{$month_start}'")->count();
        
        // 累计
        $total_students = M('student')->count();
        $active_students = M('student')->where(['status'=>1])->count();
        $total_teachers = M('teacher')->where(['status'=>1])->count();
        $total_remaining = M('student_course')->where(['status'=>1])->sum('remaining_hours');
        $total_revenue = M('order')->where(['status'=>1])->sum('pay_amount');
        $total_orders = M('order')->where(['status'=>1])->count();
        
        // 待处理
        $expire_courses = M('student_course')
            ->where("expire_date <= DATE_ADD('{$today}', INTERVAL 7 DAY) AND status=1")
            ->count();
        $pending_leads = M('leads')->where(['status'=>1])->count();
        
        $this->assign('today_attendance', $today_attendance ?: 0);
        $this->assign('today_consumption', $today_consumption ?: 0);
        $this->assign('today_new_students', $today_new_students ?: 0);
        $this->assign('today_orders', $today_orders ?: 0);
        $this->assign('today_revenue', $today_revenue ?: 0);
        $this->assign('month_revenue', $month_revenue ?: 0);
        $this->assign('month_orders', $month_orders ?: 0);
        $this->assign('month_new', $month_new ?: 0);
        $this->assign('month_attendance', $month_attendance ?: 0);
        $this->assign('total_students', $total_students ?: 0);
        $this->assign('active_students', $active_students ?: 0);
        $this->assign('total_teachers', $total_teachers ?: 0);
        $this->assign('total_remaining', $total_remaining ?: 0);
        $this->assign('total_revenue', $total_revenue ?: 0);
        $this->assign('total_orders', $total_orders ?: 0);
        $this->assign('expire_courses', $expire_courses ?: 0);
        $this->assign('pending_leads', $pending_leads ?: 0);
    }
}
