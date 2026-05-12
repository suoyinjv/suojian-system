<?php
namespace Admin\Controller;

/**
 * 课后反馈控制器
 */
class FeedbackController extends AdminController {
    
    /**
     * 反馈列表
     */
    public function index() {
        $map = array();
        
        $classId = I('class_id', 0, 'intval');
        if ($classId) {
            $map['class_id'] = $classId;
        }
        
        $teacherId = I('teacher_id', 0, 'intval');
        if ($teacherId) {
            $map['teacher_id'] = $teacherId;
        }
        
        $startDate = I('start_date');
        $endDate = I('end_date');
        if ($startDate && $endDate) {
            $map['create_time'] = array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59');
        }
        
        $count = M('feedback')->where($map)->count();
        $page = $this->showPage($count, 20);
        $list = M('feedback')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        // 获取关联数据
        if ($list) {
            $classIds = array_filter(array_unique(array_column($list, 'class_id')));
            $teacherIds = array_filter(array_unique(array_column($list, 'teacher_id')));
            
            $classes = M('class')->where(array('id' => array('in', $classIds)))->index('id')->select();
            $teachers = M('teacher')->where(array('id' => array('in', $teacherIds)))->index('id')->select();
            
            foreach ($list as &$item) {
                $item['class_name'] = $classes[$item['class_id']]['name'] ?: '';
                $item['teacher_name'] = $teachers[$item['teacher_id']]['name'] ?: '';
            }
        }
        
        // 获取班级列表
        $classes = M('class')->select();
        $this->assign('classes', $classes);
        
        // 获取老师列表
        $teachers = M('teacher')->select();
        $this->assign('teachers', $teachers);
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 添加反馈
     */
    public function add() {
        if (IS_POST) {
            $data = array(
                'class_id' => I('class_id', 0, 'intval'),
                'teacher_id' => I('teacher_id', 0, 'intval'),
                'content' => I('content'),
                'attachments' => I('attachments'),
                'create_time' => time()
            );
            
            if (empty($data['class_id']) || empty($data['teacher_id'])) {
                $this->error('班级和老师不能为空');
            }
            
            $id = M('feedback')->add($data);
            if ($id) {
                // 通知家长
                $this->notifyParents($data['class_id'], $id);
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->display();
        }
    }
    
    /**
     * 编辑反馈
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = array(
                'class_id' => I('class_id', 0, 'intval'),
                'teacher_id' => I('teacher_id', 0, 'intval'),
                'content' => I('content'),
                'attachments' => I('attachments'),
            );
            
            if (M('feedback')->where(array('id' => $id))->save($data)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = M('feedback')->find($id);
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    /**
     * 删除反馈
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        
        if (M('feedback')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
    
    /**
     * 查看反馈详情
     */
    public function view() {
        $id = I('id', 0, 'intval');
        
        $feedback = M('feedback')->find($id);
        
        // 获取班级学生
        $students = M('class_student')->where(array('class_id' => $feedback['class_id']))->select();
        $studentIds = array_column($students, 'student_id');
        $studentList = M('student')->where(array('id' => array('in', $studentIds)))->select();
        
        $this->assign('feedback', $feedback);
        $this->assign('studentList', $studentList);
        $this->display();
    }
    
    /**
     * 通知家长
     */
    private function notifyParents($classId, $feedbackId) {
        // 获取班级学生
        $students = M('class_student')->where(array('class_id' => $classId))->select();
        
        $feedback = M('feedback')->find($feedbackId);
        $class = M('class')->find($classId);
        
        $content = "【课后反馈】{$class['name']}课后反馈：" . mb_substr($feedback['content'], 0, 50);
        
        foreach ($students as $cs) {
            $student = M('student')->find($cs['student_id']);
            if ($student && $student['phone']) {
                // 发送短信
                D('Sms')->sendSms($student['phone'], $content);
            }
        }
    }
}