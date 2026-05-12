<?php
namespace Admin\Controller;
use Think\Controller;

class StudentCourseController extends Controller {
    
    // 学员课时列表
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $keyword = I('keyword', '');
        
        $where = [];
        if ($keyword) {
            $where['s.name|stc.course_name'] = ['like', "%{$keyword}%"];
        }
        
        $join = 'LEFT JOIN sc_student s ON stc.student_id=s.id';
        $field = 'stc.*,s.name as student_name,s.phone';
        
        $list = M('student_course')
            ->alias('stc')
            ->field($field)
            ->join($join)
            ->where($where)
            ->page($page, $rows)
            ->select();
            
        $total = M('student_course')
            ->alias('stc')
            ->join($join)
            ->where($where)
            ->count();
            
        foreach ($list as &$v) {
            $v['used_percent'] = $v['total_hours'] > 0 ? round($v['used_hours']/$v['total_hours']*100) : 0;
        }
        
        $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
    }
    
    // 学员课时详情
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
    
    // 手动添加课时
    public function add() {
        $data = I('post.');
        $data['create_time'] = time();
        $data['remaining_hours'] = $data['total_hours'];
        $data['used_hours'] = 0;
        
        $result = M('student_course')->add($data);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'添加成功':'添加失败']);
    }
    
    // 课消记录
    public function consumption() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        
        $list = M('consumption')
            ->alias('c')
            ->field('c.*,s.name as student_name,co.name as course_name,e.name as teacher_name')
            ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
            ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
            ->join('LEFT JOIN sc_teacher e ON c.teacher_id=e.id')
            ->order('c.create_time desc')
            ->page($page, $rows)
            ->select();
            
        $total = M('consumption')->count();
        
        $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
    }
    
    // 赠送课时
    public function gift() {
        $data = I('post.');
        $data['type'] = 'gift';
        $data['before_hours'] = 0;
        $data['after_hours'] = $data['hours'];
        $data['create_time'] = time();
        
        M('consumption')->add($data);
        
        // 更新学员课时
        M('student_course')->where(['id'=>$data['student_course_id']])->setInc('total_hours', $data['hours']);
        M('student_course')->where(['id'=>$data['student_course_id']])->setInc('remaining_hours', $data['hours']);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'赠送成功']);
    }
}