<?php
namespace Admin\Controller;
use Think\Controller;

class AttendanceController extends Controller {
    
    // 考勤记录列表
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $student_id = I('student_id', 0, 'intval');
        $start_date = I('start_date', '');
        $end_date = I('end_date', '');
        
        $where = [];
        if ($student_id) $where['a.student_id'] = $student_id;
        if ($start_date) $where['a.attend_date'] = ['>=', $start_date];
        if ($end_date) $where['a.attend_date'] = ['<=', $end_date];
        
        $list = M('attendance')
            ->alias('a')
            ->field('a.*,s.name as student_name,s.phone,c.name as course_name')
            ->join('LEFT JOIN sc_student s ON a.student_id=s.id')
            ->join('LEFT JOIN sc_course c ON a.course_id=c.id')
            ->where($where)
            ->order('a.attend_date desc,a.id desc')
            ->page($page, $rows)
            ->select();
            
        $total = M('attendance')
            ->alias('a')
            ->where($where)
            ->count();
            
        $status_arr = [1=>'正常', 2=>'请假', 3=>'旷课', 4=>'迟到'];
        foreach ($list as &$v) {
            $v['status_text'] = $status_arr[$v['status']];
            $v['auto_deduct_text'] = $v['auto_deduct'] ? '是' : '否';
        }
        
        $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
    }
    
    // 签到/签退
    public function checkin() {
        $student_id = I('student_id', 0, 'intval');
        $schedule_id = I('schedule_id', 0, 'intval');
        $course_id = I('course_id', 0, 'intval');
        $status = I('status', 1, 'intval'); // 1正常 2请假 3旷课 4迟到
        
        // 获取配置
        $hours = M('config')->where(['name'=>'default_hours'])->getField('value');
        $hours = $hours ?: 1;
        $auto_deduct = M('config')->where(['name'=>'auto_deduct_hours'])->getField('value');
        
        $data = [
            'student_id' => $student_id,
            'schedule_id' => $schedule_id,
            'course_id' => $course_id,
            'attend_date' => date('Y-m-d'),
            'status' => $status,
            'hours' => $status == 1 ? $hours : 0,
            'auto_deduct' => ($status == 1 && $auto_deduct) ? 1 : 0,
            'create_time' => time()
        ];
        
        // 检查是否已考勤
        $exist = M('attendance')->where([
            'student_id'=>$student_id, 
            'attend_date'=>date('Y-m-d'),
            'schedule_id'=>$schedule_id
        ])->find();
        
        if ($exist) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'今日已考勤']);
        }
        
        $result = M('attendance')->add($data);
        
        // 自动消课
        if ($result && $data['auto_deduct'] && $course_id) {
            $this->autoConsumption($student_id, $course_id, $hours);
        }
        
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'签到成功':'签到失败']);
    }
    
    // 刷卡考勤（模拟）
    public function scanCard() {
        $phone = I('phone', '');
        $schedule_id = I('schedule_id', 0, 'intval');
        
        $student = M('student')->where(['phone'=>$phone])->find();
        if (!$student) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'学员不存在']);
        }
        
        // 获取排课信息
        $schedule = M('schedule')->find($schedule_id);
        
        return $this->checkin([
            'student_id' => $student['id'],
            'schedule_id' => $schedule_id,
            'course_id' => $schedule['course_id'],
            'status' => 1
        ]);
    }
    
    // 自动消课
    private function autoConsumption($student_id, $course_id, $hours) {
        // 找到学员的该课程课时
        $student_course = M('student_course')
            ->where(['student_id'=>$student_id, 'course_id'=>$course_id, 'status'=>1])
            ->find();
            
        if ($student_course && $student_course['remaining_hours'] > 0) {
            $before_hours = $student_course['remaining_hours'];
            $actual_hours = min($hours, $before_hours);
            $after_hours = $before_hours - $actual_hours;
            
            // 记录课消
            M('consumption')->add([
                'student_id' => $student_id,
                'course_id' => $course_id,
                'student_course_id' => $student_course['id'],
                'hours' => $actual_hours,
                'before_hours' => $before_hours,
                'after_hours' => $after_hours,
                'type' => 'attendance',
                'create_time' => time()
            ]);
            
            // 更新课时
            M('student_course')->where(['id'=>$student_course['id']])->setInc('used_hours', $actual_hours);
            M('student_course')->where(['id'=>$student_course['id']])->setDec('remaining_hours', $actual_hours);
            
            // 如果已用完，更新状态
            if ($after_hours <= 0) {
                M('student_course')->where(['id'=>$student_course['id']])->save(['status'=>0]);
            }
        }
    }
    
    // 考勤统计
    public function statistics() {
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));
        
        // 按状态统计
        $sql = "SELECT status, COUNT(*) as cnt, SUM(hours) as hours 
                FROM sc_attendance 
                WHERE attend_date BETWEEN '{$start_date}' AND '{$end_date}' 
                GROUP BY status";
        $status_stats = M()->query($sql);
        
        // 每日趋势
        $sql = "SELECT attend_date, COUNT(*) as cnt, SUM(hours) as hours 
                FROM sc_attendance 
                WHERE attend_date BETWEEN '{$start_date}' AND '{$end_date}' 
                GROUP BY attend_date 
                ORDER BY attend_date";
        $daily_stats = M()->query($sql);
        
        // 课消统计
        $sql = "SELECT DATE(FROM_UNIXTIME(create_time)) as date, 
                       SUM(hours) as hours, COUNT(*) as cnt 
                FROM sc_consumption 
                WHERE create_time >= UNIX_TIMESTAMP('{$start_date}') 
                AND create_time <= UNIX_TIMESTAMP('{$end_date}')
                GROUP BY DATE(FROM_UNIXTIME(create_time))";
        $consumption_stats = M()->query($sql);
        
        $this->ajaxReturn([
            'status_stats' => $status_stats,
            'daily_stats' => $daily_stats,
            'consumption_stats' => $consumption_stats
        ]);
    }
}