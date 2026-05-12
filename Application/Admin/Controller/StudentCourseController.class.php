<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 学员课时管理 - HTML页面 + JSON API
 */
class StudentCourseController extends Controller {
    
    /**
     * 学员课时列表（页面 / JSON API）
     */
    public function index() {
        $is_json = I('json', 0, 'intval');
        
        if ($is_json) {
            $page = I('page', 1, 'intval');
            $rows = I('rows', 20, 'intval');
            $keyword = I('keyword', '');
            
            $where = [];
            if ($keyword) {
                $where['s.username|stc.course_name'] = ['like', "%{$keyword}%"];
            }
            
            $list = M('student_course')
                ->alias('stc')
                ->field('stc.*,s.username as student_name,s.my_mobile as phone')
                ->join('LEFT JOIN sc_student s ON stc.student_id=s.id')
                ->where($where)
                ->page($page, $rows)
                ->select();
                
            $total = M('student_course')
                ->alias('stc')
                ->join('LEFT JOIN sc_student s ON stc.student_id=s.id')
                ->where($where)
                ->count();
                
            foreach ($list as &$v) {
                $v['used_percent'] = $v['total_hours'] > 0 ? round($v['used_hours']/$v['total_hours']*100) : 0;
                $v['student_name'] = $v['student_name'] ?: '-';
                $v['status_text'] = $v['status'] == 1 ? '正常' : '已用完';
            }
            
            $this->ajaxReturn(['total'=>$total, 'rows'=>$list]);
        }
        
        $this->display();
    }
    
    /**
     * 学员课时详情（API only）
     */
    public function detail() {
        $student_id = I('student_id', 0, 'intval');
        
        $courses = M('student_course')
            ->where(['student_id'=>$student_id, 'status'=>1])
            ->select();
            
        $consumptions = M('consumption')
            ->where(['student_id'=>$student_id])
            ->order('create_time desc')
            ->limit(20)
            ->select();
            
        $this->ajaxReturn(['courses'=>$courses, 'consumptions'=>$consumptions]);
    }
    
    /**
     * 手动添加课时（API only）
     */
    public function add() {
        $data = I('post.');
        $data['create_time'] = time();
        $data['remaining_hours'] = $data['total_hours'];
        $data['used_hours'] = 0;
        
        $result = M('student_course')->add($data);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'添加成功':'添加失败']);
    }
    
    /**
     * 课消记录（页面 / JSON API）
     */
    public function consumption() {
        $is_json = I('json', 0, 'intval');
        
        $keyword = I('keyword', '');
        $course_id = I('course_id', 0, 'intval');
        $type = I('type', '');
        $start_date = I('start_date', '');
        $end_date = I('end_date', '');
        
        // 构建查询条件
        $where = [];
        if ($keyword) {
            $where['s.username|s.my_mobile'] = ['like', "%{$keyword}%"];
        }
        if ($course_id) {
            $where['c.course_id'] = $course_id;
        }
        if ($type) {
            $where['c.type'] = $type;
        }
        if ($start_date) {
            $where['c.create_time'][] = ['egt', strtotime($start_date)];
        }
        if ($end_date) {
            $where['c.create_time'][] = ['elt', strtotime($end_date) + 86399];
        }
        
        // 统计数据
        $today_start = strtotime('today');
        $today_end = $today_start + 86399;
        $month_start = strtotime('first day of this month');
        $month_end = strtotime('last day of this month') + 86399;
        
        $today_count = M('consumption')->alias('c')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->where(array_merge($where, ['c.create_time'=>[['egt', $today_start], ['elt', $today_end]]]))
            ->count();
        $today_hours = M('consumption')->alias('c')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->where(array_merge($where, ['c.create_time'=>[['egt', $today_start], ['elt', $today_end]]]))
            ->sum('c.hours');
        $month_count = M('consumption')->alias('c')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->where(array_merge($where, ['c.create_time'=>[['egt', $month_start], ['elt', $month_end]]]))
            ->count();
        $month_hours = M('consumption')->alias('c')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->where(array_merge($where, ['c.create_time'=>[['egt', $month_start], ['elt', $month_end]]]))
            ->sum('c.hours');
        
        if ($is_json) {
            $page = I('page', 1, 'intval');
            $rows = I('rows', 20, 'intval');
            
            $list = M('consumption')
                ->alias('c')
                ->field('c.*,s.username as student_name,s.my_mobile as phone,co.course_name,t.username as teacher_name')
                ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
                ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
                ->join('LEFT JOIN sc_teacher t ON c.teacher_id=t.id')
                ->where($where)
                ->order('c.create_time desc')
                ->page($page, $rows)
                ->select();
                
            $total = M('consumption')
                ->alias('c')
                ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
                ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
                ->where($where)
                ->count();
            
            $type_map = ['attendance'=>'考勤消课', 'gift'=>'赠送', 'manual'=>'手动调整', 'refund'=>'退费'];
            foreach ($list as &$v) {
                $v['type_text'] = $type_map[$v['type']] ?: $v['type'];
                $v['course_name'] = $v['course_name'] ?: '-';
                $v['phone'] = $v['phone'] ?: '-';
            }
            
            $this->ajaxReturn(['total'=>$total, 'rows'=>$list, 
                'today_count'=>intval($today_count), 'today_hours'=>floatval($today_hours),
                'month_count'=>intval($month_count), 'month_hours'=>floatval($month_hours)]);
        }
        
        // 页面模式 - 按页渲染
        import('ORG.Util.Page');
        $count = M('consumption')
            ->alias('c')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
            ->where($where)
            ->count();
        $Page = new \Think\Page($count, 20);
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        
        $list = M('consumption')
            ->alias('c')
            ->field('c.*,s.username as student_name,s.my_mobile as phone,co.course_name,t.username as teacher_name')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
            ->join('LEFT JOIN sc_teacher t ON c.teacher_id=t.id')
            ->where($where)
            ->order('c.create_time desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        
        $type_map = ['attendance'=>'考勤消课', 'gift'=>'赠送', 'manual'=>'手动调整', 'refund'=>'退费'];
        foreach ($list as &$v) {
            $v['type_text'] = $type_map[$v['type']] ?: $v['type'];
            $v['course_name'] = $v['course_name'] ?: '-';
            $v['phone'] = $v['phone'] ?: '-';
        }
        
        $courses = M('course')->field('id,name')->select();
        
        $this->assign('today_count', intval($today_count));
        $this->assign('today_hours', floatval($today_hours));
        $this->assign('month_count', intval($month_count));
        $this->assign('month_hours', floatval($month_hours));
        $this->assign('list', $list);
        $this->assign('courses', $courses);
        $this->assign('page', $Page->show());
        $this->display();
    }
    
    /**
     * 赠送课时（API only）
     */
    public function gift() {
        $data = I('post.');
        $data['type'] = 'gift';
        $data['before_hours'] = 0;
        $data['after_hours'] = $data['hours'];
        $data['create_time'] = time();
        
        M('consumption')->add($data);
        
        M('student_course')->where(['id'=>$data['student_course_id']])->setInc('total_hours', $data['hours']);
        M('student_course')->where(['id'=>$data['student_course_id']])->setInc('remaining_hours', $data['hours']);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'赠送成功']);
    }
}
