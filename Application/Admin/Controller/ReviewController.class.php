<?php
namespace Admin\Controller;

class ReviewController extends AdminController {
    
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        
        $where = array();
        $keyword = I('keyword');
        if ($keyword) {
            $where['content'] = array('like', '%' . $keyword . '%');
        }
        
        $teacherId = I('teacher_id', 0, 'intval');
        if ($teacherId) {
            $where['teacher_id'] = $teacherId;
        }
        
        $score = I('score', 0, 'intval');
        if ($score) {
            $where['score'] = $score;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $where['status'] = $status;
        }
        
        $count = M('review')->where($where)->count();
        $list = M('review')->alias('r')
            ->field('r.*, s.username as student_name, t.username as teacher_name, co.course_name')
            ->join('LEFT JOIN sc_student s ON r.student_id=s.id')
            ->join('LEFT JOIN sc_teacher t ON r.teacher_id=t.id')
            ->join('LEFT JOIN sc_course co ON r.course_id=co.id')
            ->where($where)
            ->order('r.id desc')
            ->page($page, $rows)
            ->select();
        
        // JSON mode
        if (I('json', 0, 'intval')) {
            $this->ajaxReturn(['total' => $count, 'rows' => $list]);
        }
        
        // 获取教师列表（用于筛选下拉）
        $teachers = M('teacher')->field('id, username as name')->select();
        
        $this->assign('list', $list);
        $this->assign('teachers', $teachers);
        $this->assign('count', $count);
        $this->display();
    }
    
    public function save() {
        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $data = array(
                'student_id' => I('student_id', 0, 'intval'),
                'teacher_id' => I('teacher_id', 0, 'intval'),
                'course_id' => I('course_id', 0, 'intval'),
                'score' => I('score', 5, 'intval'),
                'content' => I('content'),
                'status' => I('status', 1, 'intval'),
            );
            
            if (empty($data['student_id']) || empty($data['teacher_id'])) {
                $this->ajaxReturn(['code' => 1, 'msg' => '学生和老师不能为空']);
            }
            
            if ($id) {
                M('review')->where(['id' => $id])->save($data);
                $this->ajaxReturn(['code' => 0, 'msg' => '修改成功']);
            } else {
                $data['create_time'] = time();
                M('review')->add($data);
                $this->ajaxReturn(['code' => 0, 'msg' => '添加成功']);
            }
        }
        
        $id = I('id', 0, 'intval');
        $info = M('review')->find($id);
        $this->assign('info', $info);
        
        $students = M('student')->field('id, username')->select();
        $teachers = M('teacher')->field('id, username')->select();
        $this->assign('students', $students);
        $this->assign('teachers', $teachers);
        $this->display();
    }
    
    public function delete() {
        $id = I('id', 0, 'intval');
        if (M('review')->delete($id)) {
            $this->ajaxReturn(['code' => 0, 'msg' => '删除成功']);
        } else {
            $this->ajaxReturn(['code' => 1, 'msg' => '删除失败']);
        }
    }
    
    public function statistics() {
        $where = array('status' => 1);
        
        $avgScore = M('review')->where($where)->avg('score');
        $scoreDist = array();
        for ($i = 1; $i <= 5; $i++) {
            $scoreDist[$i] = M('review')->where($where)->where(array('score' => $i))->count();
        }
        
        $this->ajaxReturn([
            'code' => 0,
            'data' => [
                'avgScore' => round($avgScore, 1),
                'scoreDist' => $scoreDist
            ]
        ]);
    }
}
