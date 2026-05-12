<?php
namespace Admin\Controller;
use Think\Controller;

class DashboardController extends Controller {
    
    // 首页数据
    public function index() {
        $today = date('Y-m-d');
        $month_start = date('Y-m-01');
        $year_start = date('Y-01-01');
        
        // 今日数据
        $today_attendance = M('attendance')->where(['attend_date'=>$today])->count();
        $today_consumption = M('consumption')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d')='{$today}'")->sum('hours');
        $today_new_students = M('student')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d')='{$today}'")->count();
        
        // 本月数据
        $month_revenue = M('order')->where("status=1 AND pay_time>=" . strtotime($month_start))->sum('pay_amount');
        $month_orders = M('order')->where("status=1 AND create_time>=" . strtotime($month_start))->count();
        
        // 学员统计
        $total_students = M('student')->count();
        $active_students = M('student')->where(['status'=>1])->count();
        
        // 课时统计
        $total_remaining = M('student_course')->where(['status'=>1])->sum('remaining_hours');
        
        // 老师数量
        $total_teachers = M('teacher')->where(['status'=>1])->count();
        
        // 待处理事项
        $expire_courses = M('student_course')
            ->where("expire_date <= DATE_ADD('{$today}', INTERVAL 7 DAY) AND status=1")
            ->count();
            
        $pending_leads = M('leads')->where(['status'=>1])->count();
        
        $this->ajaxReturn([
            'today' => [
                'attendance' => $today_attendance ?: 0,
                'consumption' => $today_consumption ?: 0,
                'new_students' => $today_new_students ?: 0
            ],
            'month' => [
                'revenue' => $month_revenue ?: 0,
                'orders' => $month_orders ?: 0
            ],
            'total' => [
                'students' => $total_students,
                'active_students' => $active_students,
                'remaining_hours' => $total_remaining ?: 0,
                'teachers' => $total_teachers
            ],
            'pending' => [
                'expire_courses' => $expire_courses,
                'pending_leads' => $pending_leads
            ]
        ]);
    }
    
    // 招生数据
    public function enrollment() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        // 每日新增学员
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, COUNT(*) as cnt 
                FROM sc_student 
                WHERE create_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time))
                ORDER BY date";
        $daily_new = M()->query($sql);
        
        // 来源统计
        $source_stats = M('student')
            ->field('source, COUNT(*) as cnt')
            ->where("create_time >= UNIX_TIMESTAMP('{$start_date}')")
            ->group('source')
            ->select();
        
        // 线索转化
        $leads_stats = M('leads')
            ->field('status, COUNT(*) as cnt')
            ->group('status')
            ->select();
        
        $this->ajaxReturn([
            'daily_new' => $daily_new,
            'source_stats' => $source_stats,
            'leads_stats' => $leads_stats
        ]);
    }
    
    // 教务数据
    public function academic() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        // 考勤趋势
        $sql = "SELECT attend_date as date, COUNT(*) as cnt, SUM(hours) as hours 
                FROM sc_attendance 
                WHERE attend_date >= '{$start_date}'
                GROUP BY attend_date
                ORDER BY date";
        $attendance_stats = M()->query($sql);
        
        // 课消趋势
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, 
                       SUM(hours) as hours, COUNT(*) as cnt 
                FROM sc_consumption 
                WHERE create_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time))
                ORDER BY date";
        $consumption_stats = M()->query($sql);
        
        // 课程消耗排名
        $course_stats = M('consumption')
            ->alias('c')
            ->field('co.name, SUM(c.hours) as hours')
            ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
            ->where("c.create_time >= UNIX_TIMESTAMP('{$start_date}')")
            ->group('c.course_id')
            ->order('hours desc')
            ->limit(10)
            ->select();
        
        $this->ajaxReturn([
            'attendance_stats' => $attendance_stats,
            'consumption_stats' => $consumption_stats,
            'course_stats' => $course_stats
        ]);
    }
    
    // 财务数据
    public function finance() {
        $days = I('days', 30, 'intval');
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        // 收入趋势
        $sql = "SELECT DATE(FROM_UNIXTIME(pay_time)) as date, 
                       SUM(pay_amount) as amount, COUNT(*) as cnt 
                FROM sc_order 
                WHERE status=1 AND pay_time >= UNIX_TIMESTAMP('{$start_date}')
                GROUP BY DATE(FROM_UNIXTIME(pay_time))
                ORDER BY date";
        $revenue_stats = M()->query($sql);
        
        // 课程收入排名
        $course_revenue = M('order')
            ->alias('o')
            ->field('o.course_name, SUM(o.pay_amount) as amount, COUNT(*) as cnt')
            ->where("o.status=1 AND o.pay_time >= UNIX_TIMESTAMP('{$start_date}')")
            ->group('o.course_id')
            ->order('amount desc')
            ->limit(10)
            ->select();
        
        // 支付方式
        $pay_type_stats = M('order')
            ->field('pay_type, SUM(pay_amount) as amount, COUNT(*) as cnt')
            ->where("status=1 AND pay_time >= UNIX_TIMESTAMP('{$start_date}')")
            ->group('pay_type')
            ->select();
        
        $this->ajaxReturn([
            'revenue_stats' => $revenue_stats,
            'course_revenue' => $course_revenue,
            'pay_type_stats' => $pay_type_stats
        ]);
    }
    
    // 经营概览
    public function overview() {
        $today = date('Y-m-d');
        $month_start = date('Y-m-01');

        // 今日数据
        $today_attendance   = M('attendance')->where(['attend_date' => $today])->count();
        $today_consumption  = M('consumption')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d')='{$today}'")->sum('hours');
        $today_new_students = M('student')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d')='{$today}'")->count();
        $today_revenue      = M('order')->where("status=1 AND FROM_UNIXTIME(pay_time, '%Y-%m-%d')='{$today}'")->sum('pay_amount');
        $today_orders       = M('order')->where("status=1 AND FROM_UNIXTIME(pay_time, '%Y-%m-%d')='{$today}'")->count();

        // 本月数据
        $month_revenue = M('order')->where("status=1 AND pay_time>=" . strtotime($month_start))->sum('pay_amount');
        $month_orders  = M('order')->where("status=1 AND create_time>=" . strtotime($month_start))->count();
        $month_new     = M('student')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d')>='{$month_start}'")->count();
        $month_attendance = M('attendance')->where("attend_date>='{$month_start}'")->count();

        // 累计数据
        $total_students      = M('student')->count();
        $active_students     = M('student')->where(['status' => 1])->count();
        $total_teachers      = M('teacher')->where(['status' => 1])->count();
        $total_remaining     = M('student_course')->where(['status' => 1])->sum('remaining_hours');
        $total_revenue       = M('order')->where(['status' => 1])->sum('pay_amount');
        $total_orders        = M('order')->where(['status' => 1])->count();
        $expire_courses      = M('student_course')->where("expire_date <= DATE_ADD('{$today}', INTERVAL 7 DAY) AND status=1")->count();
        $pending_leads       = M('leads')->where(['status' => 1])->count();

        $this->assign('today_attendance', $today_attendance ?: 0);
        $this->assign('today_consumption', $today_consumption ?: 0);
        $this->assign('today_new_students', $today_new_students ?: 0);
        $this->assign('today_revenue', $today_revenue ?: 0);
        $this->assign('today_orders', $today_orders ?: 0);
        $this->assign('month_revenue', $month_revenue ?: 0);
        $this->assign('month_orders', $month_orders ?: 0);
        $this->assign('month_new', $month_new ?: 0);
        $this->assign('month_attendance', $month_attendance ?: 0);
        $this->assign('total_students', $total_students);
        $this->assign('active_students', $active_students);
        $this->assign('total_teachers', $total_teachers);
        $this->assign('total_remaining', $total_remaining ?: 0);
        $this->assign('total_revenue', $total_revenue ?: 0);
        $this->assign('total_orders', $total_orders);
        $this->assign('expire_courses', $expire_courses);
        $this->assign('pending_leads', $pending_leads);

        $this->display();
    }

    // 实时看板
    public function realtime() {
        // 当前在班学员
        $current_hour = date('H');
        $week_day = date('N');
        
        // 今日课程
        $today_courses = M('schedule')
            ->alias('s')
            ->field('s.*,c.name as class_name,co.name as course_name,t.name as teacher_name')
            ->join('LEFT JOIN sc_class c ON s.class_id=c.id')
            ->join('LEFT JOIN sc_course co ON s.course_id=co.id')
            ->join('LEFT JOIN sc_teacher t ON s.teacher_id=t.id')
            ->where(['s.week_day'=>$week_day, 's.status'=>1])
            ->select();
            
        // 今日考勤
        $today_attendance = M('attendance')
            ->where(['attend_date'=>date('Y-m-d')])
            ->select();
            
        $status_cnt = [];
        foreach ($today_attendance as $a) {
            $status_cnt[$a['status']] = ($status_cnt[$a['status']] ?? 0) + 1;
        }
        
        $this->ajaxReturn([
            'current_time' => date('H:i'),
            'courses' => $today_courses,
            'attendance_status' => $status_cnt
        ]);
    }
}