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
        
        if ($is_json) {
            $page = I('page', 1, 'intval');
            $rows = I('rows', 20, 'intval');
            
            $list = M('consumption')
                ->alias('c')
                ->field('c.*,s.username as student_name,co.course_name,co.course_name as course_name,t.username as teacher_name')
                ->join('LEFT JOIN sc_student s ON c.student_id=s.id')
                ->join('LEFT JOIN sc_course co ON c.course_id=co.id')
                ->join('LEFT JOIN sc_teacher t ON c.teacher_id=t.id')
                ->order('c.create_time desc')
                ->page($page, $rows)
                ->select();
                
            $total = M('consumption')->count();
            
            $type_map = ['attendance'=>'考勤消课', 'gift'=>'赠送', 'manual'=>'手动调整', 'refund'=>'退费'];
            foreach ($list as &$v) {
                $v['type_text'] = $type_map[$v['type']] ?: $v['type'];
                $v['course_name'] = $v['course_name'] ?: '-';
            }
            
            $this->ajaxReturn(['total'=>$total, 'rows'=>$list]);
        }
        
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
