<?php
namespace Admin\Controller;

/**
 * 学习计划控制器
 */
class StudyPlanController extends AdminController {
    
    /**
     * 学习计划列表
     */
    public function index() {
        $map = array();
        
        $studentId = I('student_id', 0, 'intval');
        if ($studentId) {
            $map['student_id'] = $studentId;
        }
        
        $courseId = I('course_id', 0, 'intval');
        if ($courseId) {
            $map['course_id'] = $courseId;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $map['status'] = $status;
        }
        
        $count = M('study_plan')->where($map)->count();
        $page = $this->showPage($count, 20);
        $list = M('study_plan')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        // 获取关联数据
        if ($list) {
            $studentIds = array_filter(array_unique(array_column($list, 'student_id')));
            $courseIds = array_filter(array_unique(array_column($list, 'course_id')));
            
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            $courses = M('course')->where(array('id' => array('in', $courseIds)))->index('id')->select();
            
            foreach ($list as &$item) {
                $item['student_name'] = $students[$item['student_id']]['name'] ?: '';
                $item['course_name'] = $courses[$item['course_id']]['name'] ?: '';
            }
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 添加学习计划
     */
    public function add() {
        if (IS_POST) {
            $data = array(
                'student_id' => I('student_id', 0, 'intval'),
                'course_id' => I('course_id', 0, 'intval'),
                'title' => I('title'),
                'content' => I('content'),
                'target_date' => I('target_date'),
                'status' => 0,
                'create_time' => time()
            );
            
            if (empty($data['student_id']) || empty($data['title'])) {
                $this->error('学生和计划标题不能为空');
            }
            
            $id = M('study_plan')->add($data);
            if ($id) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            // 获取学生列表
            $students = M('student')->where(array('status' => 1))->select();
            $this->assign('students', $students);
            
            // 获取课程列表
            $courses = M('course')->where(array('status' => 1))->select();
            $this->assign('courses', $courses);
            
            $this->display();
        }
    }
    
    /**
     * 编辑学习计划
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = array(
                'student_id' => I('student_id', 0, 'intval'),
                'course_id' => I('course_id', 0, 'intval'),
                'title' => I('title'),
                'content' => I('content'),
                'target_date' => I('target_date'),
                'status' => I('status', 0, 'intval'),
                'update_time' => time()
            );
            
            if (M('study_plan')->where(array('id' => $id))->save($data)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = M('study_plan')->find($id);
            $this->assign('info', $info);
            
            // 获取学生列表
            $students = M('student')->where(array('status' => 1))->select();
            $this->assign('students', $students);
            
            // 获取课程列表
            $courses = M('course')->where(array('status' => 1))->select();
            $this->assign('courses', $courses);
            
            $this->display();
        }
    }
    
    /**
     * 删除学习计划
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        
        if (M('study_plan')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
    
    /**
     * 完成计划
     */
    public function complete() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = array(
                'status' => 1,
                'update_time' => time()
            );
            
            if (M('study_plan')->where(array('id' => $id))->save($data)) {
                $this->success('设置完成成功');
            } else {
                $this->error('设置失败');
            }
        } else {
            $info = M('study_plan')->find($id);
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    /**
     * 学习计划统计
     */
    public function statistics() {
        $studentId = I('student_id', 0, 'intval');
        
        $where = array();
        if ($studentId) {
            $where['student_id'] = $studentId;
        }
        
        // 总计划数
        $total = M('study_plan')->where($where)->count();
        
        // 已完成
        $completed = M('study_plan')->where($where)->where(array('status' => 1))->count();
        
        // 进行中
        $ongoing = M('study_plan')->where($where)->where(array('status' => 0))->count();
        
        // 学生完成率排名
        $studentStats = M('study_plan')
            ->field('student_id, COUNT(*) as total, SUM(IF(status=1,1,0)) as completed')
            ->group('student_id')
            ->order('completed DESC')
            ->limit(20)
            ->select();
        
        if ($studentStats) {
            $studentIds = array_column($studentStats, 'student_id');
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            
            foreach ($studentStats as &$item) {
                $item['student_name'] = $students[$item['student_id']]['name'] ?: '';
                $item['rate'] = $item['total'] > 0 ? round($item['completed'] / $item['total'] * 100, 1) : 0;
            }
        }
        
        $this->assign('total', $total);
        $this->assign('completed', $completed);
        $this->assign('ongoing', $ongoing);
        $this->assign('rate', $total > 0 ? round($completed / $total * 100, 1) : 0);
        $this->assign('studentStats', $studentStats);
        $this->display();
    }
}