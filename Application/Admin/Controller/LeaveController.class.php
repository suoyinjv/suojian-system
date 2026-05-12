<?php
namespace Admin\Controller;

/**
 * 请假管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class LeaveController extends CommonController
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
     * [Index 请假列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function index()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $status = I('status', -1, 'intval');
        $keyword = I('keyword', '', 'trim');

        $where = array();
        if ($status >= 0) {
            $where['l.status'] = $status;
        }
        if ($keyword) {
            $where[] = array(
                's.username' => array('like', '%' . $keyword . '%'),
                's.my_mobile' => array('like', '%' . $keyword . '%'),
                '_logic' => 'or'
            );
        }

        $prefix = C('DB_PREFIX');
        $count = M('leave')->alias('l')
            ->join('LEFT JOIN ' . $prefix . 'student s ON l.student_id=s.id')
            ->where($where)->count();

        $list = M('leave')->alias('l')
            ->field('l.*, s.username as student_name, s.my_mobile as student_phone, c.class_name, u.nickname as approver_name')
            ->join('LEFT JOIN ' . $prefix . 'student s ON l.student_id=s.id')
            ->join('LEFT JOIN ' . $prefix . 'class c ON l.class_id=c.id')
            ->join('LEFT JOIN ' . $prefix . 'admin u ON l.approver_id=u.id')
            ->where($where)
            ->order('l.id DESC')
            ->page($page, $rows)
            ->select();

        $status_map = array(
            0 => '待审批',
            1 => '已批准',
            2 => '已拒绝',
            3 => '已取消'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['start_date'] = $item['start_date'] ?: '-';
            $item['end_date'] = $item['end_date'] ?: '-';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
            $item['approve_time'] = $item['approve_time'] ? date('Y-m-d H:i', $item['approve_time']) : '-';
            // 解析图片
            if ($item['images']) {
                $item['images'] = json_decode($item['images'], true);
            }
        }

        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Save 申请请假]
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
                'student_id' => I('post.student_id', 0, 'intval'),
                'class_id' => I('post.class_id', 0, 'intval'),
                'start_date' => I('post.start_date', ''),
                'end_date' => I('post.end_date', ''),
                'reason' => I('post.reason', '', 'trim'),
                'images' => json_encode(I('post.images', array())),
                'parent_id' => I('post.parent_id', 0, 'intval')
            );

            if (empty($data['student_id'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择学生'));
            }
            if (empty($data['start_date']) || empty($data['end_date'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择请假时间'));
            }
            if (empty($data['reason'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请填写请假事由'));
            }

            // 检查时间是否合法
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '结束时间不能早于开始时间'));
            }

            if ($id > 0) {
                // 编辑（只能是取消）
                $result = M('leave')->where(array('id' => $id))->save(array(
                    'status' => 3,
                    'update_time' => time()
                ));
                if ($result !== false) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '请假已取消'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
                }
            } else {
                // 新增申请
                $data['status'] = 0;
                $data['create_time'] = time();
                $id = M('leave')->add($data);
                if ($id > 0) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '请假申请已提交'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '提交失败'));
                }
            }
        } else {
            $id = I('id', 0, 'intval');
            if ($id > 0) {
                $info = M('leave')->find($id);
                $this->assign('info', $info);
            }

            // 获取学生列表
            $students = M('student')->field('id, name, phone')->select();
            // 获取班级列表
            $classes = M('class')->field('id, class_name')->select();
            $this->assign('students', $students);
            $this->assign('classes', $classes);
            $this->display();
        }
    }

    /**
     * [Approve 审批请假]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function approve()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $remark = I('remark', '', 'trim');

        $leave = M('leave')->find($id);
        if (empty($leave)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请假记录不存在'));
        }

        if ($leave['status'] != 0) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '该请假已处理'));
        }

        $result = M('leave')->where(array('id' => $id))->save(array(
            'status' => 1,
            'approver_id' => session('admin.id'),
            'approve_time' => time(),
            'approve_remark' => $remark,
            'update_time' => time()
        ));

        if ($result !== false) {
            // 如果是考勤关联的请假，更新考勤记录
            $this->updateAttendance($leave);

            // 发送通知给家长
            $this->sendNotify($id, 1);

            $this->ajaxReturn(array('code' => 1, 'msg' => '请假已批准'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
        }
    }

    /**
     * [Reject 拒绝请假]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function reject()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $remark = I('remark', '', 'trim');

        $leave = M('leave')->find($id);
        if (empty($leave)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请假记录不存在'));
        }

        if ($leave['status'] != 0) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '该请假已处理'));
        }

        $result = M('leave')->where(array('id' => $id))->save(array(
            'status' => 2,
            'approver_id' => session('admin.id'),
            'approve_time' => time(),
            'approve_remark' => $remark,
            'update_time' => time()
        ));

        if ($result !== false) {
            // 发送通知给家长
            $this->sendNotify($id, 2);

            $this->ajaxReturn(array('code' => 1, 'msg' => '请假已拒绝'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
        }
    }

    /**
     * [BatchApprove 批量审批]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function batchApprove()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $ids = I('ids', '');
        $operation = I('operation', 'approve');
        $remark = I('remark', '', 'trim');

        if (empty($ids)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择要操作的记录'));
        }

        $status = ($operation == 'approve') ? 1 : 2;
        $id_arr = explode(',', $ids);
        $success = 0;

        foreach ($id_arr as $id) {
            $id = intval($id);
            if ($id > 0) {
                $leave = M('leave')->find($id);
                if ($leave && $leave['status'] == 0) {
                    M('leave')->where(array('id' => $id))->save(array(
                        'status' => $status,
                        'approver_id' => session('admin.id'),
                        'approve_time' => time(),
                        'approve_remark' => $remark,
                        'update_time' => time()
                    ));

                    if ($status == 1) {
                        $this->updateAttendance($leave);
                    }
                    $this->sendNotify($id, $status);
                    $success++;
                }
            }
        }

        $this->ajaxReturn(array('code' => 1, 'msg' => '批量操作成功', 'data' => array('success' => $success)));
    }

    /**
     * [GetLeaveRecord 历史请假查询]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function getLeaveRecord()
    {
        $student_id = I('student_id', 0, 'intval');

        $where = array('student_id' => $student_id);
        $list = M('leave')->where($where)
            ->order('id DESC')
            ->limit(20)
            ->select();

        $status_map = array(
            0 => '待审批',
            1 => '已批准',
            2 => '已拒绝',
            3 => '已取消'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
        }

        $this->ajaxReturn(array('code' => 1, 'data' => $list));
    }

    /**
     * [Cancel 取消请假]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function cancel()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');

        $leave = M('leave')->find($id);
        if (empty($leave)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请假记录不存在'));
        }

        if ($leave['status'] != 0) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '只能取消待审批的请假'));
        }

        $result = M('leave')->where(array('id' => $id))->save(array(
            'status' => 3,
            'update_time' => time()
        ));

        if ($result !== false) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '请假已取消'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
        }
    }

    /**
     * [Detail 请假详情]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function detail()
    {
        $id = I('id', 0, 'intval');

        $info = M('leave')->find($id);
        if (empty($info)) {
            $this->error('请假记录不存在');
        }

        // 获取学生信息
        $student = M('student')->find($info['student_id']);
        // 获取班级信息
        $class = M('class')->find($info['class_id']);
        // 获取审批人信息
        $approver = M('admin')->find($info['approver_id']);

        $this->assign('info', $info);
        $this->assign('student', $student);
        $this->assign('class', $class);
        $this->assign('approver', $approver);
        $this->display();
    }

    /**
     * [updateAttendance 更新关联的考勤记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [array] $leave [请假记录]
     */
    private function updateAttendance($leave)
    {
        // 更新请假期间的考勤记录为请假状态
        M('attendance')->where(array(
            'student_id' => $leave['student_id'],
            'attend_date' => array('between', $leave['start_date'] . ',' . $leave['end_date'])
        ))->save(array(
            'status' => 2,
            'leave_id' => $leave['id']
        ));
    }

    /**
     * [sendNotify 发送通知]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [int] $leave_id [请假ID]
     * @param    [int] $status [状态 1批准 2拒绝]
     */
    private function sendNotify($leave_id, $status)
    {
        $leave = M('leave')->find($leave_id);
        $student = M('student')->find($leave['student_id']);

        $status_text = ($status == 1) ? '已批准' : '已拒绝';
        $content = "【请假通知】{$student['name']}的请假申请{$status_text}。时间：{$leave['start_date']} 至 {$leave['end_date']}。";

        M('message_log')->add(array(
            'receiver_id' => $leave['parent_id'] ?: $student['id'],
            'receiver_type' => 2,
            'title' => '请假申请' . $status_text,
            'content' => $content,
            'type' => 2,
            'send_status' => 1,
            'send_time' => time(),
            'create_time' => time()
        ));
    }
}
?>
