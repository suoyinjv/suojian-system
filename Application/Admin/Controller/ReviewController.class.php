<?php
namespace Admin\Controller;

class ReviewController extends AdminController {
    
    /**
     * 获取租户过滤条件
     */
    private function getCampusWhere() {
        $where = [];
        $is_super = !empty($this->admin['is_super']);
        $campus_id = intval($this->admin['campus_id']);
        if (!$is_super && $campus_id > 0) {
            $where['campus_id'] = $campus_id;
        }
        return $where;
    }
    
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        
        $where = $this->getCampusWhere();
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
        
        // 获取教师列表（用于筛选下拉）- 按租户过滤
        $teacherWhere = $this->getCampusWhere();
        $teachers = M('teacher')->field('id, username as name')->where($teacherWhere)->select();
        
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
            
            // 租户过滤
            $campusWhere = $this->getCampusWhere();
            
            if ($id) {
                $data['campus_id'] = $campusWhere['campus_id'];
                M('review')->where(array_merge(['id' => $id], $campusWhere))->save($data);
                $this->ajaxReturn(['code' => 0, 'msg' => '修改成功']);
            } else {
                $data['create_time'] = time();
                $data['campus_id'] = $campusWhere['campus_id'];
                M('review')->add($data);
                $this->ajaxReturn(['code' => 0, 'msg' => '添加成功']);
            }
        }
        
        $id = I('id', 0, 'intval');
        $campusWhere = $this->getCampusWhere();
        $info = M('review')->where(array_merge(['id' => $id], $campusWhere))->find();
        $this->assign('info', $info);
        
        // 学生和教师列表按租户过滤
        $studentWhere = $this->getCampusWhere();
        $teacherWhere = $this->getCampusWhere();
        $students = M('student')->field('id, username')->where($studentWhere)->select();
        $teachers = M('teacher')->field('id, username')->where($teacherWhere)->select();
        $this->assign('students', $students);
        $this->assign('teachers', $teachers);
        $this->display();
    }
    
    public function delete() {
        $id = I('id', 0, 'intval');
        $campusWhere = $this->getCampusWhere();
        if (M('review')->where(array_merge(['id' => $id], $campusWhere))->delete()) {
            $this->ajaxReturn(['code' => 0, 'msg' => '删除成功']);
        } else {
            $this->ajaxReturn(['code' => 1, 'msg' => '删除失败']);
        }
    }
    
    public function statistics() {
        $where = $this->getCampusWhere();
        $where['status'] = 1;
        
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
