<?php
namespace Admin\Controller;

/**
 * 消息通知控制器
 * 家校互动-消息通知
 */
class MessageController extends CommonController {
    
    /**
     * 发送消息
     */
    public function send() {
        if (IS_POST) {
            $type = I('post.type', 1, 'intval'); // 1-考勤 2-成绩 3-活动 4-课时预警 5-系统
            $receiver_type = I('post.receiver_type', 1, 'intval'); // 1-学员 2-家长 3-老师
            $receiver_ids = I('post.receiver_ids', '');
            $title = I('post.title', '', 'trim');
            $content = I('post.content', '', 'trim');
            
            if (empty($content)) {
                $this->error('消息内容不能为空');
            }
            
            // 获取接收者
            $receivers = [];
            if ($receiver_type == 1) { // 学员
                if ($receiver_ids) {
                    $receivers = explode(',', $receiver_ids);
                }
            } elseif ($receiver_type == 2) { // 家长
                // TODO: 获取家长用户
            } elseif ($receiver_type == 3) { // 老师
                $receivers = M('teacher')->getField('id', true);
            }
            
            // 批量发送
            foreach ($receivers as $receiver_id) {
                $data = [
                    'receiver_id' => $receiver_id,
                    'receiver_type' => $receiver_type,
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'send_status' => 1,
                    'send_time' => time(),
                    'add_time' => time(),
                ];
                M('message_log')->add($data);
            }
            
            $this->success('发送成功，共发送'.count($receivers).'条');
        }
        
        // 获取模板
        $templates = M('message_template')->where(['status'=>1])->select();
        $this->assign('templates', $templates);
        
        // 获取学员列表
        $students = M('student')->field('id,student_name')->limit(100)->select();
        $this->assign('students', $students);
        
        $this->display();
    }
    
    /**
     * 消息模板
     */
    public function template() {
        $list = M('message_template')->order('id DESC')->select();
        
        $type_map = [1=>'考勤通知', 2=>'成绩通知', 3=>'活动通知', 4=>'课时预警', 5=>'系统通知'];
        
        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']];
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 添加模板
     */
    public function addTemplate() {
        if (IS_POST) {
            $data = [
                'title' => I('post.title', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'content' => I('post.content', '', 'trim'),
                'is_sms' => I('post.is_sms', 0, 'intval'),
                'is_wechat' => I('post.is_wechat', 1, 'intval'),
                'add_time' => time(),
            ];
            
            M('message_template')->add($data);
            $this->success('添加成功', U('template'));
        }
        
        $this->display();
    }
    
    /**
     * 消息记录
     */
    public function log() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $count = M('message_log')->count();
        $list = M('message_log')->order('send_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $type_map = [1=>'考勤', 2=>'成绩', 3=>'活动', 4=>'课时预警', 5=>'系统'];
        $status_map = [0=>'待发送', 1=>'已发送', 2=>'失败'];
        
        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']];
            $item['status_text'] = $status_map[$item['send_status']];
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        $this->display();
    }
    
    /**
     * 自动发送考勤通知
     */
    public function autoSendAttendance() {
        $date = date('Y-m-d');
        
        // 获取今日考勤记录
        $attendances = M('attendance')
            ->where("FROM_UNIXTIME(add_time, '%Y-%m-%d') = '{$date}'")
            ->select();
        
        foreach ($attendances as $att) {
            $student = M('student')->find($att['student_id']);
            $course = M('course')->find($att['course_id']);
            
            // 发送通知
            $content = "【考勤通知】{$student['student_name']}同学于{$date} {$att['status_text']}课程：{$course['course_name']}";
            
            M('message_log')->add([
                'receiver_id' => $student['id'],
                'receiver_type' => 1,
                'title' => '考勤通知',
                'content' => $content,
                'type' => 1,
                'send_status' => 1,
                'send_time' => time(),
                'add_time' => time()
            ]);
        }
        
        echo '考勤通知已发送';
    }
    
    /**
     * 课时预警通知
     */
    public function hourWarning() {
        // 获取课时不足的学员（少于5课时）
        $packages = M('student_package')
            ->where(['status'=>1, 'balance_hours'=>['elt', 5]])
            ->select();
        
        foreach ($packages as $pkg) {
            $student = M('student')->find($pkg['student_id']);
            $package = M('package')->find($pkg['package_id']);
            
            $content = "【课时预警】{$student['student_name']}同学您好，您购买的{$package['name']}剩余课时不足{$pkg['balance_hours']}节，请及时续费！";
            
            M('message_log')->add([
                'receiver_id' => $student['id'],
                'receiver_type' => 1,
                'title' => '课时不足提醒',
                'content' => $content,
                'type' => 4,
                'send_status' => 1,
                'send_time' => time(),
                'add_time' => time()
            ]);
        }
        
        echo '课时预警已发送';
    }

    /**
     * 消息中心首页 - 转向发送页面
     */
    public function index() {
        $this->redirect('send');
    }
}