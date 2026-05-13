<?php
namespace Admin\Controller;

/**
 * 数据统计分析控制器
 * 经营分析-数据报表
 */
class StatsController extends CommonController {
    
    // 租户校区ID
    protected $tenant_campus_id = 0;
    
    /**
     * [__construct 构造方法]
     */
    public function __construct() {
        parent::__construct();
        // 租户校区过滤
        $this->tenant_campus_id = GetTenantCampusId();
    }
    
    /**
     * 数据统计首页
     */
    public function index() {
        $this->dashboard();
        $this->display('dashboard');
    }
    
    /**
     * 数据座舱/经营看板
     */
    public function dashboard() {
        // 今日数据
        $today = strtotime(date('Y-m-d'));
        
        // 今日新增学员
        $today_new_students = M('student')->where(['campus_id'=>$this->tenant_campus_id, 'add_time'=>['egt', $today]])->count();
        
        // 今日营收
        $today_income = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $today],
            'status'=>['in', '1,2']
        ])->sum('money');
        
        // 今日上课消耗
        $today_consumption = M('hour_consumption')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $today],
            'type'=>1
        ])->sum('hours');
        
        // 今日考勤
        $today_attendance = M('attendance')->where(['campus_id'=>$this->tenant_campus_id, 'add_time'=>['egt', $today]])->count();
        
        // 本月数据
        $month_start = strtotime(date('Y-m-01'));
        
        $month_new_students = M('student')->where(['campus_id'=>$this->tenant_campus_id, 'add_time'=>['egt', $month_start]])->count();
        $month_income = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $month_start],
            'status'=>['in', '1,2']
        ])->sum('money');
        $month_consumption = M('hour_consumption')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $month_start],
            'type'=>1
        ])->sum('hours');
        
        // 学员总数
        $total_students = M('student')->where(['campus_id'=>$this->tenant_campus_id])->count();
        
        // 在读学员
        $active_students = M('student_package')->where(['campus_id'=>$this->tenant_campus_id, 'status'=>1])->count();
        
        // 老师总数
        $total_teachers = M('teacher')->where(['campus_id'=>$this->tenant_campus_id])->count();
        
        // 班级总数
        $total_classes = M('class')->where(['campus_id'=>$this->tenant_campus_id])->count();
        
        // 课时消耗趋势（最近7天）
        for ($i = 6; $i >= 0; $i--) {
            $day = strtotime("-{$i} days");
            $day_end = $day + 86400;
            $date = date('m-d', $day);
            
            $day_consumption = M('hour_consumption')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$day, $day_end]],
                'type'=>1
            ])->sum('hours');
            
            $consumption_trend[] = ['date'=>$date, 'hours'=>$day_consumption ?: 0];
        }
        
        // 营收趋势（最近7天）
        for ($i = 6; $i >= 0; $i--) {
            $day = strtotime("-{$i} days");
            $day_end = $day + 86400;
            $date = date('m-d', $day);
            
            $day_income = M('order')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$day, $day_end]],
                'status'=>['in', '1,2']
            ])->sum('money');
            
            $income_trend[] = ['date'=>$date, 'money'=>$day_income ?: 0];
        }
        
        // 当前校区数据（租户过滤）
        $campus_data = [];
        if ($this->tenant_campus_id > 0) {
            $campus = M('campus')->where(['id'=>$this->tenant_campus_id, 'status'=>1])->find();
            if ($campus) {
                $campus['student_count'] = M('student')->where(['campus_id'=>$this->tenant_campus_id])->count();
                $campus['class_count'] = M('class')->where(['campus_id'=>$this->tenant_campus_id])->count();
                $campus['income'] = M('order')->where([
                    'campus_id'=>$this->tenant_campus_id,
                    'add_time'=>['egt', $month_start],
                    'status'=>['in', '1,2']
                ])->sum('money');
                $campus_data[] = $campus;
            }
        }
        
        $this->assign('today_new_students', $today_new_students);
        $this->assign('today_income', $today_income ?: 0);
        $this->assign('today_consumption', $today_consumption ?: 0);
        $this->assign('today_attendance', $today_attendance);
        
        $this->assign('month_new_students', $month_new_students);
        $this->assign('month_income', $month_income ?: 0);
        $this->assign('month_consumption', $month_consumption ?: 0);
        
        $this->assign('total_students', $total_students);
        $this->assign('active_students', $active_students);
        $this->assign('total_teachers', $total_teachers);
        $this->assign('total_classes', $total_classes);
        
        $this->assign('consumption_trend', json_encode($consumption_trend));
        $this->assign('income_trend', json_encode($income_trend));
        $this->assign('campuses', $campus_data);
        
        $this->display();
    }
    
    /**
     * 招生数据报表
     */
    public function recruitment() {
        $month_start = strtotime(date('Y-m-01'));
        $year_start = strtotime(date('Y-01-01'));
        
        // 线索统计
        $total_leads = M('lead')->where(['campus_id'=>$this->tenant_campus_id])->count();
        $month_leads = M('lead')->where(['campus_id'=>$this->tenant_campus_id, 'add_time'=>['egt', $month_start]])->count();
        
        // 线索转化率
        $converted = M('lead')->where(['campus_id'=>$this->tenant_campus_id, 'status'=>4])->count();
        $conversion_rate = $total_leads > 0 ? round($converted / $total_leads * 100, 2) : 0;
        
        // 来源分布
        $sources = M('lead')->where(['campus_id'=>$this->tenant_campus_id])->group('source')->getField('source, count(*) as count', true);
        $source_map = [1=>'线上推广', 2=>'电话咨询', 3=>'地推', 4=>'转介绍', 5=>'其他'];
        $source_data = [];
        foreach ($sources as $k => $v) {
            $source_data[] = ['name'=>$source_map[$k]?:'未知', 'value'=>$v['count']];
        }
        
        // 线索状态分布
        $status_data = M('lead')->where(['campus_id'=>$this->tenant_campus_id])->group('status')->getField('status, count(*) as count', true);
        $status_map = [1=>'新线索', 2=>'已联系', 3=>'有意向', 4=>'已成交', 5=>'无效'];
        $status_distribution = [];
        foreach ($status_data as $k => $v) {
            $status_distribution[] = ['name'=>$status_map[$k]?:'未知', 'value'=>$v['count']];
        }
        
        // 每月新增趋势
        for ($i = 11; $i >= 0; $i--) {
            $month = strtotime("-{$i} months");
            $month_end = strtotime('+1 month', $month);
            $month_name = date('Y-m', $month);
            
            $count = M('lead')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$month, $month_end]]
            ])->count();
            
            $month_trend[] = ['month'=>$month_name, 'count'=>$count];
        }
        
        $this->assign('total_leads', $total_leads);
        $this->assign('month_leads', $month_leads);
        $this->assign('converted', $converted);
        $this->assign('conversion_rate', $conversion_rate);
        $this->assign('source_data', json_encode($source_data));
        $this->assign('status_distribution', json_encode($status_distribution));
        $this->assign('month_trend', json_encode($month_trend));
        
        $this->display();
    }
    
    /**
     * 营收数据报表
     */
    public function revenue() {
        $year_start = strtotime(date('Y-01-01'));
        
        // 年度营收
        $year_income = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'status'=>['in', '1,2']
        ])->sum('money');
        
        // 月度营收趋势
        for ($i = 11; $i >= 0; $i--) {
            $month = strtotime("-{$i} months");
            $month_end = strtotime('+1 month', $month);
            $month_name = date('Y-m', $month);
            
            $income = M('order')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$month, $month_end]],
                'status'=>['in', '1,2']
            ])->sum('money');
            
            $month_income_trend[] = ['month'=>$month_name, 'money'=>$income ?: 0];
        }
        
        // 课程营收占比
        $course_income = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'status'=>['in', '1,2']
        ])->group('course_id')->getField('course_id, sum(money) as total', true);
        
        $course_ids = array_keys($course_income);
        $courses = !empty($course_ids) ? M('course')->where(['id'=>['in', $course_ids]])->getField('id,course_name', true) : [];
        
        $course_data = [];
        foreach ($course_income as $k => $v) {
            $course_data[] = ['name'=>$courses[$k]?:'未知', 'value'=>$v['total']];
        }
        
        // 支付方式分布
        $pay_types = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'status'=>['in', '1,2']
        ])->group('pay_type')->getField('pay_type, sum(money) as total', true);
        
        $pay_map = [1=>'微信', 2=>'支付宝', 3=>'现金', 4=>'银行卡'];
        $pay_data = [];
        foreach ($pay_types as $k => $v) {
            $pay_data[] = ['name'=>$pay_map[$k]?:'其他', 'value'=>$v['total']];
        }
        
        $this->assign('year_income', $year_income ?: 0);
        $this->assign('month_income_trend', json_encode($month_income_trend));
        $this->assign('course_data', json_encode($course_data));
        $this->assign('pay_data', json_encode($pay_data));
        
        $this->display();
    }
    
    /**
     * 学员数据报表
     */
    public function student() {
        $year_start = strtotime(date('Y-01-01'));
        
        // 学员总数
        $total_students = M('student')->where(['campus_id'=>$this->tenant_campus_id])->count();
        
        // 新增学员
        $year_new_students = M('student')->where(['campus_id'=>$this->tenant_campus_id, 'add_time'=>['egt', $year_start]])->count();
        
        // 学员增长趋势
        for ($i = 11; $i >= 0; $i--) {
            $month = strtotime("-{$i} months");
            $month_end = strtotime('+1 month', $month);
            $month_name = date('Y-m', $month);
            
            $count = M('student')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$month, $month_end]]
            ])->count();
            
            $month_trend[] = ['month'=>$month_name, 'count'=>$count];
        }
        
        // 学员来源
        $sources = M('student')->where(['campus_id'=>$this->tenant_campus_id])->group('source')->getField('source, count(*) as count', true);
        $source_data = [];
        foreach ($sources as $k => $v) {
            $source_data[] = ['name'=>$k?:'未知', 'value'=>$v['count']];
        }
        
        // 课时消耗排名
        $top_consumption = M('hour_consumption')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'type'=>1
        ])->group('student_id')->getField('student_id, sum(hours) as total', true);
        
        arsort($top_consumption);
        $top_students = array_slice($top_consumption, 0, 10, true);
        
        $student_ids = array_keys($top_students);
        $students = !empty($student_ids) ? M('student')->where(['id'=>['in', $student_ids], 'campus_id'=>$this->tenant_campus_id])->getField('id,student_name', true) : [];
        
        $consumption_rank = [];
        foreach ($top_students as $k => $v) {
            $consumption_rank[] = ['name'=>$students[$k]?:'', 'hours'=>$v['total']];
        }
        
        $this->assign('total_students', $total_students);
        $this->assign('year_new_students', $year_new_students);
        $this->assign('month_trend', json_encode($month_trend));
        $this->assign('source_data', json_encode($source_data));
        $this->assign('consumption_rank', $consumption_rank);
        
        $this->display();
    }
    
    /**
     * 课消数据报表
     */
    public function consumption() {
        $year_start = strtotime(date('Y-01-01'));
        
        // 年度总课消
        $year_consumption = M('hour_consumption')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'type'=>1
        ])->sum('hours');
        
        // 月度课消趋势
        for ($i = 11; $i >= 0; $i--) {
            $month = strtotime("-{$i} months");
            $month_end = strtotime('+1 month', $month);
            $month_name = date('Y-m', $month);
            
            $hours = M('hour_consumption')->where([
                'campus_id'=>$this->tenant_campus_id,
                'add_time'=>['between', [$month, $month_end]],
                'type'=>1
            ])->sum('hours');
            
            $month_trend[] = ['month'=>$month_name, 'hours'=>$hours ?: 0];
        }
        
        // 课程课消占比
        $course_consumption = M('hour_consumption')->where([
            'campus_id'=>$this->tenant_campus_id,
            'add_time'=>['egt', $year_start],
            'type'=>1
        ])->group('course_id')->getField('course_id, sum(hours) as total', true);
        
        $course_ids = array_keys($course_consumption);
        $courses = !empty($course_ids) ? M('course')->where(['id'=>['in', $course_ids]])->getField('id,course_name', true) : [];
        
        $course_data = [];
        foreach ($course_consumption as $k => $v) {
            $course_data[] = ['name'=>$courses[$k]?:'未知', 'value'=>$v['total']];
        }
        
        $this->assign('year_consumption', $year_consumption ?: 0);
        $this->assign('month_trend', json_encode($month_trend));
        $this->assign('course_data', json_encode($course_data));
        
        $this->display();
    }
    
    /**
     * 班级数据报表
     */
    public function class() {
        $list = M('class')->where(['campus_id'=>$this->tenant_campus_id])->select();
        
        foreach ($list as &$item) {
            // 学员数
            $item['student_count'] = M('class_student')->where(['class_id'=>$item['id']])->count();
            
            // 出勤率
            $class_students = M('class_student')->where(['class_id'=>$item['id']])->select();
            $student_ids = array_column($class_students, 'student_id');
            
            if ($student_ids) {
                $today = strtotime(date('Y-m-d'));
                $present = M('attendance')->where([
                    'campus_id'=>$this->tenant_campus_id,
                    'class_id'=>$item['id'],
                    'add_time'=>['egt', $today],
                    'status'=>['in', [1,2]]
                ])->count();
                
                $total = count($student_ids);
                $item['attendance_rate'] = $total > 0 ? round($present / $total * 100, 1) : 0;
            } else {
                $item['attendance_rate'] = 0;
            }
            
            // 课消
            $month_start = strtotime(date('Y-m-01'));
            $item['month_consumption'] = M('hour_consumption')->where([
                'campus_id'=>$this->tenant_campus_id,
                'class_id'=>$item['id'],
                'add_time'=>['egt', $month_start],
                'type'=>1
            ])->sum('hours') ?: 0;
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 导出报表
     */
    public function export() {
        $type = I('type', 'revenue', 'trim');
        
        switch ($type) {
            case 'revenue':
                $this->exportRevenue();
                break;
            case 'student':
                $this->exportStudent();
                break;
            case 'consumption':
                $this->exportConsumption();
                break;
            default:
                $this->error('无效的报表类型');
        }
    }
    
    private function exportRevenue() {
        $year_start = strtotime(date('Y-01-01'));
        
        $orders = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'create_time'=>['egt', $year_start],
            'status'=>['in', '1,2']
        ])->select();
        
        $data = [];
        foreach ($orders as $order) {
            $student_name = M('student')->where(['id'=>$order['student_id'], 'campus_id'=>$this->tenant_campus_id])->getField('username');
            $data[] = [
                'order_no' => $order['order_no'],
                'student_name' => $student_name ?: '',
                'course' => $order['course_name'] ?: '',
                'money' => $order['pay_amount'],
                'pay_type' => $order['pay_type'] == '1' ? '微信' : ($order['pay_type']=='2'?'支付宝':($order['pay_type']=='3'?'现金':'银行卡')),
                'add_time' => date('Y-m-d H:i', $order['create_time']),
            ];
        }
        
        exportExcel(['订单号', '学员', '课程', '金额', '支付方式', '时间'], $data, '营收报表');
    }
    
    private function exportStudent() {
        $students = M('student')->where(['campus_id'=>$this->tenant_campus_id])->select();
        
        $data = [];
        foreach ($students as $stu) {
            $data[] = [
                'student_name' => $stu['username'],
                'phone' => $stu['my_mobile'],
                'source' => isset($stu['source']) ? $stu['source'] : '',
                'balance_hours' => 0,
                'add_time' => date('Y-m-d', $stu['add_time']),
            ];
        }
        
        exportExcel(['学员姓名', '电话', '来源', '剩余课时', '添加时间'], $data, '学员报表');
    }
    
    private function exportConsumption() {
        $year_start = strtotime(date('Y-01-01'));
        
        // hour_consumption表可能不存在，使用订单数据
        $consumptions = M('order')->where([
            'campus_id'=>$this->tenant_campus_id,
            'pay_time'=>['egt', $year_start],
            'status'=>['in', '1,2']
        ])->field('student_id,course_name,total_hours,pay_amount,pay_time')->select();
        
        $students = M('student')->where(['campus_id'=>$this->tenant_campus_id])->getField('id,username', true);
        $type_map = [0=>'购买', 1=>'上课消费', 2=>'冻结', 3=>'解冻', 4=>'退费'];
        
        $data = [];
        foreach ($consumptions as $c) {
            $data[] = [
                'student_name' => $students[$c['student_id']] ?: '',
                'course' => $c['course_name'] ?: '',
                'hours' => $c['total_hours'],
                'type' => '购买',
                'remark' => '课时购买',
                'add_time' => date('Y-m-d H:i', $c['pay_time']),
            ];
        }
        
        exportExcel(['学员', '课程', '课时', '类型', '备注', '时间'], $data, '课消报表');
    }
}