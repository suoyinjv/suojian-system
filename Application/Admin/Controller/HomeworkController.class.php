<?php
namespace Admin\Controller;

/**
 * 作业管理控制器
 * 教学管理-课后督学-作业系统
 */
class HomeworkController extends CommonController {
    
    /**
     * 作业列表
     */
    public function index() {
        $page = I('page', 1, 'intval');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $course_id = I('course_id', 0, 'intval');
        $where = [];
        if ($course_id) {
            $where['course_id'] = $course_id;
        }
        
        $prefix = C('DB_PREFIX');
        $count = M('homework')->where($where)->count();
        
        $list = M('homework')->where($where)
            ->order('id DESC')
            ->limit($offset, $limit)
            ->select();
        
        // 获取课程和班级名称
        $course_ids = array_unique(array_column($list, 'course_id'));
        $courses = M('course')->where(['id'=>['in', $course_ids]])->getField('id,course_name', true);
        
        $class_ids = array_unique(array_column($list, 'class_id'));
        $classes = M('class')->where(['id'=>['in', $class_ids]])->getField('id,class_name', true);
        
        foreach ($list as &$item) {
            $item['course_name'] = $courses[$item['course_id']] ?: '';
            $item['class_name'] = $classes[$item['class_id']] ?: '';
            $item['submit_deadline'] = $item['submit_deadline'] ? date('Y-m-d H:i', $item['submit_deadline']) : '-';
        }
        
        // 获取课程列表
        $courses = M('course')->getField('id,course_name', true);
        $this->assign('courses', $courses);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        $this->display();
    }
    
    /**
     * 添加作业
     */
    public function add() {
        if (IS_POST) {
            $data = [
                'course_id' => I('post.course_id', 0, 'intval'),
                'class_id' => I('post.class_id', 0, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'attachments' => I('post.attachments', '', 'trim'),
                'submit_deadline' => I('post.submit_deadline', 0, 'strtotime'),
                'status' => I('post.status', 1, 'intval'),
                'add_time' => time(),
            ];
            
            if (empty($data['title']) || empty($data['course_id'])) {
                $this->error('请填写完整信息');
            }
            
            M('homework')->add($data);
            $this->success('添加成功', U('index'));
        }
        
        // 获取课程列表
        $courses = M('course')->getField('id,course_name', true);
        $this->assign('courses', $courses);
        
        // 获取班级列表
        $classes = M('class')->getField('id,class_name', true);
        $this->assign('classes', $classes);
        
        $this->display();
    }
    
    /**
     * 编辑作业
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = [
                'course_id' => I('post.course_id', 0, 'intval'),
                'class_id' => I('post.class_id', 0, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'attachments' => I('post.attachments', '', 'trim'),
                'submit_deadline' => I('post.submit_deadline', 0, 'strtotime'),
                'status' => I('post.status', 1, 'intval'),
                'upd_time' => time(),
            ];
            
            M('homework')->where(['id'=>$id])->save($data);
            $this->success('更新成功', U('index'));
        }
        
        $info = M('homework')->find($id);
        $this->assign('info', $info);
        
        $courses = M('course')->getField('id,course_name', true);
        $this->assign('courses', $courses);
        
        $classes = M('class')->getField('id,class_name', true);
        $this->assign('classes', $classes);
        
        $this->display();
    }
    
    /**
     * 删除作业
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        M('homework')->where(['id'=>$id])->delete();
        M('homework_submit')->where(['homework_id'=>$id])->delete();
        $this->success('删除成功');
    }
    
    /**
     * 批改作业
     */
    public function review() {
        $submit_id = I('submit_id', 0, 'intval');
        
        if (IS_POST) {
            $data = [
                'score' => I('post.score', 0, 'intval'),
                'teacher_remark' => I('post.teacher_remark', '', 'trim'),
                'status' => 2,
                'submit_time' => time(),
                'upd_time' => time(),
            ];
            
            M('homework_submit')->where(['id'=>$submit_id])->save($data);
            $this->success('批改完成');
        }
        
        $submit = M('homework_submit')->find($submit_id);
        $homework = M('homework')->find($submit['homework_id']);
        $student = M('student')->find($submit['student_id']);
        
        $this->assign('submit', $submit);
        $this->assign('homework', $homework);
        $this->assign('student', $student);
        $this->display();
    }
    
    /**
     * 作业提交情况
     */
    public function submitList() {
        $homework_id = I('homework_id', 0, 'intval');
        
        $prefix = C('DB_PREFIX');
        $list = M('homework_submit')->table($prefix.'homework_submit hs')
            ->join($prefix.'student s ON hs.student_id=s.id')
            ->field('hs.*, s.student_name')
            ->where(['hs.homework_id'=>$homework_id])
            ->select();
        
        $status_map = [0=>'待提交', 1=>'已提交', 2=>'已批改'];
        
        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']];
            $item['submit_time'] = $item['submit_time'] ? date('Y-m-d H:i', $item['submit_time']) : '-';
        }
        
        $homework = M('homework')->find($homework_id);
        
        $this->assign('list', $list);
        $this->assign('homework', $homework);
        $this->display();
    }
    
    /**
     * 布置作业
     */
    public function assign() {
        $class_id = I('class_id', 0, 'intval');
        
        if (IS_POST) {
            $homework_id = I('post.homework_id', 0, 'intval');
            
            // 获取班级所有学员
            $students = M('class_student')->where(['class_id'=>$class_id])->select();
            
            foreach ($students as $stu) {
                // 检查是否已存在提交记录
                $exist = M('homework_submit')->where([
                    'homework_id'=>$homework_id,
                    'student_id'=>$stu['student_id']
                ])->find();
                
                if (!$exist) {
                    M('homework_submit')->add([
                        'homework_id' => $homework_id,
                        'student_id' => $stu['student_id'],
                        'status' => 0,
                        'add_time' => time()
                    ]);
                }
            }
            
            $this->success('布置成功');
        }
        
        // 获取班级学员
        $students = M('class_student')->where(['class_id'=>$class_id])->select();
        $student_ids = array_column($students, 'student_id');
        $student_list = M('student')->where(['id'=>['in', $student_ids]])->getField('id,student_name', true);
        
        $this->assign('student_list', $student_list);
        $this->display();
    }
}