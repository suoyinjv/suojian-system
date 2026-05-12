<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 排课管理 - HTML页面 + JSON API
 */
class ScheduleController extends Controller {
    
    /**
     * 排课列表（页面 / JSON API）
     */
    public function index() {
        $is_json = I('json', 0, 'intval');
        
        if ($is_json) {
            $page = I('page', 1, 'intval');
            $rows = I('rows', 20, 'intval');
            
            $list = M('schedule')
                ->alias('s')
                ->field('s.*,c.name as class_name,co.course_name as course_name,t.username as teacher_name')
                ->join('LEFT JOIN sc_class c ON s.class_id=c.id')
                ->join('LEFT JOIN sc_course co ON s.course_id=co.id')
                ->join('LEFT JOIN sc_teacher t ON s.teacher_id=t.id')
                ->order('s.week_day,s.start_time')
                ->page($page, $rows)
                ->select();
                
            $total = M('schedule')->count();
            
            $week_arr = [1=>'周一',2=>'周二',3=>'周三',4=>'周四',5=>'周五',6=>'周六',7=>'周日'];
            foreach ($list as &$v) {
                $v['week_text'] = $week_arr[$v['week_day']] ?: '-';
                $v['time_text'] = ($v['start_time'] ? substr($v['start_time'],0,5) : '') . '-' . ($v['end_time'] ? substr($v['end_time'],0,5) : '');
                $v['status_text'] = $v['status'] ? '启用' : '禁用';
            }
            
            $this->ajaxReturn(['total'=>$total, 'rows'=>$list]);
        }
        
        $this->display();
    }
    
    /**
     * 添加排课（API only）
     */
    public function add() {
        $data = I('post.');
        $data['create_time'] = time();
        
        $conflict = M('schedule')->where([
            'teacher_id'=>$data['teacher_id'],
            'week_day'=>$data['week_day'],
            'start_time'=>$data['start_time'],
            'status'=>1
        ])->find();
        
        if ($conflict) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'该时间段老师已有课程']);
        }
        
        $result = M('schedule')->add($data);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'添加成功':'添加失败']);
    }
    
    /**
     * 编辑排课（API only）
     */
    public function edit() {
        $data = I('post.');
        $data['id'] = I('id', 0, 'intval');
        
        $result = M('schedule')->save($data);
        $this->ajaxReturn(['code'=>$result!==false?1:0, 'msg'=>$result!==false?'更新成功':'更新失败']);
    }
    
    /**
     * 删除排课（API only）
     */
    public function del() {
        $id = I('id', 0, 'intval');
        $result = M('schedule')->delete($id);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'删除成功':'删除失败']);
    }
    
    /**
     * 课表视图（页面 / JSON API）
     */
    public function scheduleView() {
        $is_json = I('json', 0, 'intval');
        
        if ($is_json) {
            $week_day = I('week_day', date('N'), 'intval');
            
            $list = M('schedule')
                ->alias('s')
                ->field('s.*,c.name as class_name,co.course_name as course_name,t.username as teacher_name')
                ->join('LEFT JOIN sc_class c ON s.class_id=c.id')
                ->join('LEFT JOIN sc_course co ON s.course_id=co.id')
                ->join('LEFT JOIN sc_teacher t ON s.teacher_id=t.id')
                ->where(['s.week_day'=>$week_day, 's.status'=>1])
                ->select();
                
            $this->ajaxReturn(['list'=>$list]);
        }
        
        $this->display();
    }
    
    /**
     * 获取老师可排课时间（API only）
     */
    public function teacherFreeTime() {
        $teacher_id = I('teacher_id', 0, 'intval');
        $week_day = I('week_day', 0, 'intval');
        
        if (!$teacher_id || !$week_day) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'参数错误']);
        }
        
        $busy = M('schedule')->where([
            'teacher_id'=>$teacher_id,
            'week_day'=>$week_day,
            'status'=>1
        ])->select();
        
        $time_slots = [];
        for ($h=8; $h<21; $h++) {
            $start = sprintf('%02d:00', $h);
            $end = sprintf('%02d:00', $h+1);
            $busy_flag = false;
            foreach ($busy as $b) {
                if ($b['start_time'] < $end && $b['end_time'] > $start) {
                    $busy_flag = true;
                    break;
                }
            }
            if (!$busy_flag) {
                $time_slots[] = ['start'=>$start, 'end'=>$end];
            }
        }
        
        $this->ajaxReturn(['code'=>1, 'slots'=>$time_slots, 'busy'=>$busy]);
    }
    
    /**
     * 转向课表视图
     */
    public function timetable() {
        $this->redirect('scheduleView');
    }
}
