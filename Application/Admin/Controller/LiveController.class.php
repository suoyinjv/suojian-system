<?php
namespace Admin\Controller;

/**
 * 直播课程控制器
 * 在线教学模块
 */
class LiveController extends CommonController {
    
    /**
     * 直播课程列表
     */
    public function index() {
        $page = I('page', 1, 'intval');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $status = I('status', 0, 'intval');
        if ($status) {
            $where['status'] = $status;
        }
        
        $count = M('live_course')->where($where)->count();
        $list = M('live_course')->where($where)
            ->order('id DESC')
            ->limit($offset, $limit)
            ->select();
        
        // 获取课程和老师名称
        $teacher_ids = array_unique(array_column($list, 'teacher_id'));
        $teachers = M('teacher')->where(['id'=>['in', $teacher_ids]])->getField('id,teacher_name', true);
        
        $type_map = [1=>'1v1', 2=>'小班课', 3=>'大班课'];
        $status_map = [0=>'未开始', 1=>'进行中', 2=>'已结束', 3=>'已取消'];
        
        foreach ($list as &$item) {
            $item['teacher_name'] = $teachers[$item['teacher_id']] ?: '';
            $item['type_text'] = $type_map[$item['type']];
            $item['status_text'] = $status_map[$item['status']];
            $item['start_time'] = $item['start_time'] ? date('Y-m-d H:i', $item['start_time']) : '-';
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        $this->display();
    }
    
    /**
     * 添加直播课程
     */
    public function add() {
        if (IS_POST) {
            $data = [
                'title' => I('post.title', '', 'trim'),
                'course_id' => I('post.course_id', 0, 'intval'),
                'teacher_id' => I('post.teacher_id', 0, 'intval'),
                'type' => I('post.type', 1, 'intval'),
                'start_time' => I('post.start_time', 0, 'strtotime'),
                'end_time' => I('post.end_time', 0, 'strtotime'),
                'max_students' => I('post.max_students', 0, 'intval'),
                'status' => 0,
                'add_time' => time(),
            ];
            
            $data['duration'] = round(($data['end_time'] - $data['start_time']) / 60);
            
            if (empty($data['title'])) {
                $this->error('课程标题不能为空');
            }
            
            M('live_course')->add($data);
            $this->success('添加成功', U('index'));
        }
        
        $courses = M('course')->getField('id,course_name', true);
        $teachers = M('teacher')->getField('id,teacher_name', true);
        
        $this->assign('courses', $courses);
        $this->assign('teachers', $teachers);
        $this->display();
    }
    
    /**
     * 编辑直播课程
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = [
                'title' => I('post.title', '', 'trim'),
                'course_id' => I('post.course_id', 0, 'intval'),
                'teacher_id' => I('post.teacher_id', 0, 'intval'),
                'type' => I('post.type', 1, 'intval'),
                'start_time' => I('post.start_time', 0, 'strtotime'),
                'end_time' => I('post.end_time', 0, 'strtotime'),
                'max_students' => I('post.max_students', 0, 'intval'),
                'upd_time' => time(),
            ];
            
            $data['duration'] = round(($data['end_time'] - $data['start_time']) / 60);
            
            M('live_course')->where(['id'=>$id])->save($data);
            $this->success('更新成功', U('index'));
        }
        
        $info = M('live_course')->find($id);
        $this->assign('info', $info);
        
        $courses = M('course')->getField('id,course_name', true);
        $teachers = M('teacher')->getField('id,teacher_name', true);
        
        $this->assign('courses', $courses);
        $this->assign('teachers', $teachers);
        $this->display();
    }
    
    /**
     * 预约学员
     */
    public function booking() {
        $live_id = I('live_id', 0, 'intval');
        
        if (IS_POST) {
            $student_ids = I('post.student_ids', '');
            
            if (empty($student_ids)) {
                $this->error('请选择学员');
            }
            
            $ids = explode(',', $student_ids);
            foreach ($ids as $student_id) {
                $exist = M('live_booking')->where([
                    'live_id'=>$live_id,
                    'student_id'=>$student_id
                ])->find();
                
                if (!$exist) {
                    M('live_booking')->add([
                        'live_id' => $live_id,
                        'student_id' => $student_id,
                        'status' => 1,
                        'add_time' => time()
                    ]);
                }
            }
            
            // 更新当前人数
            M('live_course')->where(['id'=>$live_id])->setInc('current_students', count($ids));
            
            $this->success('预约成功');
        }
        
        $live = M('live_course')->find($live_id);
        $this->assign('live', $live);
        
        // 获取可选学员（未预约的）
        $booked = M('live_booking')->where(['live_id'=>$live_id])->getField('student_id', true);
        
        $where = [];
        if ($booked) {
            $where['id'] = ['not in', $booked];
        }
        
        $students = M('student')->where($where)->getField('id,student_name', true);
        $this->assign('students', $students);
        
        $this->display();
    }
    
    /**
     * 直播预约列表
     */
    public function bookingList() {
        $live_id = I('live_id', 0, 'intval');
        
        $prefix = C('DB_PREFIX');
        $list = M('live_booking')->table($prefix.'live_booking lb')
            ->join($prefix.'student s ON lb.student_id=s.id')
            ->field('lb.*, s.student_name')
            ->where(['lb.live_id'=>$live_id])
            ->select();
        
        $status_map = [1=>'已预约', 2=>'已签到', 3=>'已取消'];
        
        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']];
        }
        
        $live = M('live_course')->find($live_id);
        
        $this->assign('list', $list);
        $this->assign('live', $live);
        $this->display();
    }
    
    /**
     * 删除直播课程
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        M('live_course')->where(['id'=>$id])->delete();
        M('live_booking')->where(['live_id'=>$id])->delete();
        $this->success('删除成功');
    }
}