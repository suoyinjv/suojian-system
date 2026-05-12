<?php
namespace Admin\Controller;

class HomeworkController extends CommonController {
    
    public function index() {
        $page = I('page', 1, 'intval');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $course_id = I('course_id', 0, 'intval');
        $where = [];
        if ($course_id) {
            $where['course_id'] = $course_id;
        }
        
        $count = M('homework')->where($where)->count();
        $list = M('homework')->where($where)
            ->order('id DESC')
            ->limit($offset, $limit)
            ->select();
        
        $course_ids = array_unique(array_column($list, 'course_id'));
        $course_map = [];
        if (!empty($course_ids)) {
            $rows = M('course')->where(['id'=>['in', $course_ids]])->field('id,course_name')->select();
            foreach ($rows as $r) {
                $course_map[$r['id']] = $r['course_name'];
            }
        }
        
        foreach ($list as &$item) {
            $item['course_name'] = $course_map[$item['course_id']] ?: '';
            $item['submit_deadline'] = $item['submit_deadline'] ? date('Y-m-d H:i', $item['submit_deadline']) : '-';
        }
        
        $all_courses = M('course')->field('id,course_name')->select();
        $course_select = [];
        foreach ($all_courses as $c) {
            $course_select[$c['id']] = $c['course_name'];
        }
        
        $this->assign('courses', $course_select);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        $this->display();
    }
    
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
        $all_courses = M('course')->field('id,course_name')->select();
        $course_select = [];
        foreach ($all_courses as $c) {
            $course_select[$c['id']] = $c['course_name'];
        }
        $this->assign('courses', $course_select);
        $this->display();
    }
    
    public function edit() {
        $id = I('id', 0, 'intval');
        if (IS_POST) {
            M('homework')->where(['id'=>$id])->save([
                'course_id' => I('post.course_id', 0, 'intval'),
                'class_id' => I('post.class_id', 0, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'submit_deadline' => I('post.submit_deadline', 0, 'strtotime'),
                'status' => I('post.status', 1, 'intval'),
            ]);
            $this->success('更新成功', U('index'));
        }
        $info = M('homework')->find($id);
        $all_courses = M('course')->field('id,course_name')->select();
        $course_select = [];
        foreach ($all_courses as $c) {
            $course_select[$c['id']] = $c['course_name'];
        }
        $this->assign('info', $info);
        $this->assign('courses', $course_select);
        $this->display();
    }
    
    public function delete() {
        if (!IS_AJAX) $this->error('非法请求');
        $id = I('id', 0, 'intval');
        M('homework')->where(['id'=>$id])->delete();
        M('homework_submit')->where(['homework_id'=>$id])->delete();
        $this->success('删除成功');
    }
    
    public function review() {
        $homework_id = I('homework_id', 0, 'intval');
        if (IS_POST) {
            $submit_id = I('submit_id', 0, 'intval');
            M('homework_submit')->where(['id'=>$submit_id])->save([
                'score' => I('score', 0, 'intval'),
                'teacher_review' => I('teacher_review', '', 'trim'),
                'teacher_remark' => I('teacher_remark', '', 'trim'),
            ]);
            $this->success('批改成功');
        }
        $homework = M('homework')->find($homework_id);
        $submits = M('homework_submit')->where(['homework_id'=>$homework_id])->select();
        $this->assign('homework', $homework);
        $this->assign('list', $submits);
        $this->display();
    }
    
    public function submitList() {
        $homework_id = I('homework_id', 0, 'intval');
        $list = M('homework_submit')->where(['homework_id'=>$homework_id])->select();
        $this->assign('list', $list);
        $this->display();
    }
    
    public function assignHomework() {
        $id = I('id', 0, 'intval');
        if (IS_POST) {
            $student_ids = I('student_ids', '');
            if (empty($student_ids)) $this->error('请选择学员');
            $ids = explode(',', $student_ids);
            foreach ($ids as $sid) {
                $sid = intval($sid);
                if ($sid <= 0) continue;
                $exist = M('homework_submit')->where(['homework_id'=>$id, 'student_id'=>$sid])->find();
                if (!$exist) {
                    M('homework_submit')->add([
                        'homework_id' => $id, 'student_id' => $sid,
                        'status' => 0, 'add_time' => time()
                    ]);
                }
            }
            $this->success('布置成功');
        }
        $students = M('student')->field('id,username')->select();
        $this->assign('students', $students);
        $this->assign('homework_id', $id);
        $this->display();
    }
}
