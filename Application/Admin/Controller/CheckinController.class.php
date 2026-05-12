<?php
namespace Admin\Controller;

/**
 * 督学打卡管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class CheckinController extends CommonController
{
    /**
     * [_initialize 前置操作-继承公共前置方法]
     */
    public function _initialize()
    {
        // 调用父类前置方法
        parent::_initialize();

        // 登录校验
        $this->Is_Login();

        // 权限校验
        $this->Is_Power();
    }

    /**
     * [Index 打卡活动列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function index()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $type = I('type', 0, 'intval');
        $status = I('status', -1, 'intval');
        $keyword = I('keyword', '', 'trim');

        $where = array();
        if ($type > 0) {
            $where['type'] = $type;
        }
        if ($status >= 0) {
            $where['status'] = $status;
        }
        if ($keyword) {
            $where['title'] = array('like', '%' . $keyword . '%');
        }

        $count = M('checkin_activity')->where($where)->count();
        $list = M('checkin_activity')->where($where)
            ->order('id DESC')
            ->page($page, $rows)
            ->select();

        $type_map = array(
            1 => '日历打卡',
            2 => '闯关打卡',
            3 => '答题打卡'
        );
        $status_map = array(
            0 => '草稿',
            1 => '进行中',
            2 => '已结束'
        );

        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '未知';
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['start_date'] = $item['start_date'] ?: '-';
            $item['end_date'] = $item['end_date'] ?: '-';
            // 参与人数
            $item['join_count'] = M('checkin_progress')->where(array('activity_id' => $item['id']))->count();
            // 完成人数
            $item['complete_count'] = M('checkin_progress')->where(array('activity_id' => $item['id'], 'status' => 1))->count();
        }

        $this->assign('type_map', $type_map);
        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Save 创建/编辑打卡活动]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function save()
    {
        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $data = array(
                'title' => I('post.title', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'course_id' => I('post.course_id', 0, 'intval'),
                'class_id' => I('post.class_id', 0, 'intval'),
                'start_date' => I('post.start_date', ''),
                'end_date' => I('post.end_date', ''),
                'rules' => json_encode(I('post.')),
                'status' => I('post.status', 0, 'intval'),
                'cover_image' => I('post.cover_image', '', 'trim'),
                'share_title' => I('post.share_title', '', 'trim'),
                'share_desc' => I('post.share_desc', '', 'trim'),
                'description' => I('post.description', '', 'trim'),
                'update_time' => time()
            );

            // 日期转时间戳
            if ($data['start_date']) {
                $data['start_date'] = strtotime($data['start_date']);
            }
            if ($data['end_date']) {
                $data['end_date'] = strtotime($data['end_date']);
            }

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '活动名称不能为空'));
            }

            if ($id > 0) {
                $result = M('checkin_activity')->where(array('id' => $id))->save($data);
                if ($result !== false) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '更新成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
                }
            } else {
                $data['create_time'] = time();
                $id = M('checkin_activity')->add($data);
                if ($id > 0) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '添加失败'));
                }
            }
        } else {
            $id = I('id', 0, 'intval');
            if ($id > 0) {
                $info = M('checkin_activity')->find($id);
                $this->assign('info', $info);
            }

            // 获取课程和班级列表
            $courses = M('course')->field('id, course_name')->select();
            $classes = M('class')->field('id, class_name')->select();
            $this->assign('courses', $courses);
            $this->assign('classes', $classes);
            $this->display();
        }
    }

    /**
     * [Stages 闯关关卡管理]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function stages()
    {
        $activity_id = I('activity_id', 0, 'intval');

        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $data = array(
                'activity_id' => $activity_id,
                'title' => I('post.title', '', 'trim'),
                'day_from' => I('post.day_from', 0, 'intval'),
                'day_to' => I('post.day_to', 0, 'intval'),
                'sort_order' => I('post.sort_order', 0, 'intval'),
                'reward_points' => I('post.reward_points', 0, 'intval'),
                'badge_url' => I('post.badge_url', '', 'trim')
            );

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '关卡名称不能为空'));
            }

            if ($id > 0) {
                $result = M('checkin_stage')->where(array('id' => $id))->save($data);
            } else {
                $result = M('checkin_stage')->add($data);
            }

            if ($result !== false) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '保存成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
            }
        }

        // 获取关卡列表
        $stages = M('checkin_stage')->where(array('activity_id' => $activity_id))
            ->order('sort_order ASC, id ASC')
            ->select();

        // 获取活动信息
        $activity = M('checkin_activity')->find($activity_id);

        $this->assign('activity', $activity);
        $this->assign('stages', $stages);
        $this->assign('activity_id', $activity_id);
        $this->display();
    }

    /**
     * [DeleteStage 删除关卡]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function deleteStage()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $result = M('checkin_stage')->where(array('id' => $id))->delete();

        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [Tasks 任务配置]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function tasks()
    {
        $activity_id = I('activity_id', 0, 'intval');
        $stage_id = I('stage_id', 0, 'intval');

        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $data = array(
                'activity_id' => $activity_id,
                'stage_id' => $stage_id,
                'day_num' => I('post.day_num', 0, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'options' => json_encode(I('post.options', array())),
                'answer' => json_encode(I('post.answer', array()))
            );

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '任务名称不能为空'));
            }

            if ($id > 0) {
                $result = M('checkin_task')->where(array('id' => $id))->save($data);
            } else {
                $result = M('checkin_task')->add($data);
            }

            if ($result !== false) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '保存成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
            }
        }

        // 获取任务列表
        $where = array('activity_id' => $activity_id);
        if ($stage_id > 0) {
            $where['stage_id'] = $stage_id;
        }
        $tasks = M('checkin_task')->where($where)->order('day_num ASC, id ASC')->select();

        // 获取关卡列表
        $stages = M('checkin_stage')->where(array('activity_id' => $activity_id))->select();

        // 获取活动信息
        $activity = M('checkin_activity')->find($activity_id);

        $type_map = array(
            1 => '文字',
            2 => '图片',
            3 => '音频',
            4 => '视频',
            5 => '答题'
        );

        $this->assign('activity', $activity);
        $this->assign('stages', $stages);
        $this->assign('tasks', $tasks);
        $this->assign('type_map', $type_map);
        $this->assign('activity_id', $activity_id);
        $this->display();
    }

    /**
     * [DeleteTask 删除任务]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function deleteTask()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $result = M('checkin_task')->where(array('id' => $id))->delete();

        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [Records 学员打卡记录审核]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function records()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $activity_id = I('activity_id', 0, 'intval');
        $status = I('status', -1, 'intval');

        $where = array();
        if ($activity_id > 0) {
            $where['cr.activity_id'] = $activity_id;
        }
        if ($status >= 0) {
            $where['cr.status'] = $status;
        }

        $prefix = C('DB_PREFIX');
        $count = M('checkin_record')->alias('cr')->where($where)->count();
        $list = M('checkin_record')->alias('cr')
            ->field('cr.*, s.name as student_name, s.phone as student_phone, ct.title as task_title')
            ->join('LEFT JOIN ' . $prefix . 'student s ON cr.student_id=s.id')
            ->join('LEFT JOIN ' . $prefix . 'checkin_task ct ON cr.task_id=ct.id')
            ->where($where)
            ->order('cr.id DESC')
            ->page($page, $rows)
            ->select();

        $status_map = array(
            0 => '待审核',
            1 => '已通过',
            2 => '需重做'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['checkin_time'] = $item['checkin_time'] ? date('Y-m-d H:i', $item['checkin_time']) : '-';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
            // 解析图片
            if ($item['images']) {
                $item['images'] = json_decode($item['images'], true);
            }
        }

        // 获取活动列表
        $activities = M('checkin_activity')->field('id, title')->select();
        $this->assign('activities', $activities);
        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Stats 打卡数据统计]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function stats()
    {
        $activity_id = I('activity_id', 0, 'intval');

        // 活动统计
        $activity = M('checkin_activity')->find($activity_id);

        // 参与人数
        $total_participants = M('checkin_progress')->where(array('activity_id' => $activity_id))->count();
        // 完成人数
        $total_completed = M('checkin_progress')->where(array('activity_id' => $activity_id, 'status' => 1))->count();
        // 进行中人数
        $total_ongoing = M('checkin_progress')->where(array('activity_id' => $activity_id, 'status' => 0))->count();

        // 打卡记录总数
        $total_records = M('checkin_record')->where(array('activity_id' => $activity_id))->count();
        // 待审核数
        $total_pending = M('checkin_record')->where(array('activity_id' => $activity_id, 'status' => 0))->count();

        // 连续打卡排行
        $ranking = M('checkin_progress')->where(array('activity_id' => $activity_id))
            ->order('continuous_days DESC, total_points DESC')
            ->limit(20)
            ->select();

        // 获取学生姓名
        $student_ids = array_filter(array_unique(array_column($ranking, 'student_id')));
        if ($student_ids) {
            $students = M('student')->where(array('id' => array('in', $student_ids)))->getField('id,name', true);
            foreach ($ranking as &$item) {
                $item['student_name'] = $students[$item['student_id']] ?: '';
            }
        }

        // 每日打卡趋势
        $daily_trend = M('checkin_record')
            ->field("FROM_UNIXTIME(checkin_time, '%Y-%m-%d') as date, COUNT(*) as count")
            ->where(array('activity_id' => $activity_id))
            ->group("FROM_UNIXTIME(checkin_time, '%Y-%m-%d')")
            ->order('date ASC')
            ->select();

        $this->assign('activity', $activity);
        $this->assign('stats', array(
            'total_participants' => $total_participants,
            'total_completed' => $total_completed,
            'total_ongoing' => $total_ongoing,
            'total_records' => $total_records,
            'total_pending' => $total_pending,
            'completion_rate' => $total_participants > 0 ? round($total_completed / $total_participants * 100, 2) : 0
        ));
        $this->assign('ranking', $ranking);
        $this->assign('daily_trend', $daily_trend);
        $this->assign('activity_id', $activity_id);
        $this->display();
    }

    /**
     * [ApproveRecord 审核通过/拒绝打卡]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function approveRecord()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $status = I('status', 1, 'intval');
        $comment = I('comment', '', 'trim');

        $data = array(
            'status' => $status,
            'teacher_comment' => $comment,
            'review_time' => time()
        );

        $record = M('checkin_record')->find($id);
        if (empty($record)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '记录不存在'));
        }

        $result = M('checkin_record')->where(array('id' => $id))->save($data);

        if ($result !== false) {
            // 如果通过，更新学生进度
            if ($status == 1) {
                $this->updateProgress($record['student_id'], $record['activity_id'], $record['task_id']);
            }
            $this->ajaxReturn(array('code' => 1, 'msg' => '审核完成'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '审核失败'));
        }
    }

    /**
     * [BatchOperation 批量操作（批准多个打卡）]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function batchOperation()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $ids = I('ids', '');
        $operation = I('operation', 'approve'); // approve通过, reject拒绝, redo需重做
        $comment = I('comment', '', 'trim');

        if (empty($ids)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择要操作的记录'));
        }

        $status_map = array(
            'approve' => 1,
            'reject' => 1,  // 拒绝也是已通过状态
            'redo' => 2
        );

        $status = $status_map[$operation];
        $id_arr = explode(',', $ids);
        $success = 0;
        $updated_records = array();

        foreach ($id_arr as $id) {
            $id = intval($id);
            if ($id > 0) {
                M('checkin_record')->where(array('id' => $id))->save(array(
                    'status' => $status,
                    'teacher_comment' => $comment,
                    'review_time' => time()
                ));

                if ($status == 1) {
                    $record = M('checkin_record')->find($id);
                    if ($record) {
                        $updated_records[$record['student_id']][] = $record;
                    }
                }
                $success++;
            }
        }

        // 更新学生进度
        foreach ($updated_records as $student_id => $records) {
            foreach ($records as $record) {
                $this->updateProgress($student_id, $record['activity_id'], $record['task_id']);
            }
        }

        $this->ajaxReturn(array('code' => 1, 'msg' => '批量操作成功', 'data' => array('success' => $success)));
    }

    /**
     * [SetStatus 更改活动状态]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function setStatus()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $status = I('status', 0, 'intval');

        $result = M('checkin_activity')->where(array('id' => $id))->save(array(
            'status' => $status,
            'update_time' => time()
        ));

        if ($result !== false) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '状态更新成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '状态更新失败'));
        }
    }

    /**
     * [Delete 删除活动]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function delete()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');

        // 删除活动
        M('checkin_activity')->where(array('id' => $id))->delete();
        // 删除关卡
        M('checkin_stage')->where(array('activity_id' => $id))->delete();
        // 删除任务
        M('checkin_task')->where(array('activity_id' => $id))->delete();
        // 删除进度
        M('checkin_progress')->where(array('activity_id' => $id))->delete();
        // 删除记录
        M('checkin_record')->where(array('activity_id' => $id))->delete();

        $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
    }

    /**
     * [updateProgress 更新学生打卡进度]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    private function updateProgress($student_id, $activity_id, $task_id)
    {
        // 获取任务信息
        $task = M('checkin_task')->find($task_id);
        if (empty($task)) {
            return;
        }

        // 获取或创建进度记录
        $progress = M('checkin_progress')->where(array(
            'student_id' => $student_id,
            'activity_id' => $activity_id
        ))->find();

        if (empty($progress)) {
            $progress = array(
                'student_id' => $student_id,
                'activity_id' => $activity_id,
                'current_day' => $task['day_num'],
                'total_days' => 1,
                'continuous_days' => 1,
                'total_points' => 0,
                'last_checkin_time' => time(),
                'create_time' => time()
            );
            M('checkin_progress')->add($progress);
        } else {
            // 更新进度
            $update = array(
                'current_day' => $task['day_num'],
                'total_days' => $progress['total_days'] + 1,
                'last_checkin_time' => time()
            );

            // 检查是否连续打卡
            $last_date = date('Y-m-d', $progress['last_checkin_time']);
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            if ($last_date == $yesterday) {
                $update['continuous_days'] = $progress['continuous_days'] + 1;
            } elseif ($last_date != $today) {
                $update['continuous_days'] = 1;
            }

            M('checkin_progress')->where(array('id' => $progress['id']))->save($update);
        }

        // 更新积分
        $activity = M('checkin_activity')->find($activity_id);
        if ($activity && $activity['type'] == 2) { // 闯关模式
            // 检查是否完成关卡，获得关卡奖励
            $stage = M('checkin_stage')->find($task['stage_id']);
            if ($stage && $stage['day_to'] == $task['day_num']) {
                M('checkin_progress')->where(array(
                    'student_id' => $student_id,
                    'activity_id' => $activity_id
                ))->setInc('total_points', $stage['reward_points']);
            }
        }
    }
}
?>
