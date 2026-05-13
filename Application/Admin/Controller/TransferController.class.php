<?php
namespace Admin\Controller;

/**
 * 转校管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class TransferController extends CommonController
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
     * [Index 转校记录列表]
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
        // campus_id 过滤
        if ($this->tenant_campus_id > 0) {
            $where['t.from_campus_id'] = $this->tenant_campus_id;
        }
        if ($status >= 0) {
            $where['t.status'] = $status;
        }
        if ($keyword) {
            $where[] = array(
                's.username' => array('like', '%' . $keyword . '%'),
                's.my_mobile' => array('like', '%' . $keyword . '%'),
                '_logic' => 'or'
            );
        }

        $prefix = C('DB_PREFIX');
        $count = M('transfer')->alias('t')->where($where)->count();
        $list = M('transfer')->alias('t')
            ->field('t.*, s.username as student_name, s.my_mobile as student_phone, 
                     fc.name as from_campus_name, tc.name as to_campus_name,
                     u.name as operator_name')
            ->join('LEFT JOIN ' . $prefix . 'student s ON t.student_id=s.id')
            ->join('LEFT JOIN ' . $prefix . 'campus fc ON t.from_campus_id=fc.id')
            ->join('LEFT JOIN ' . $prefix . 'campus tc ON t.to_campus_id=tc.id')
            ->join('LEFT JOIN ' . $prefix . 'admin u ON t.operator_id=u.id')
            ->where($where)
            ->order('t.id DESC')
            ->page($page, $rows)
            ->select();

        $status_map = array(
            0 => '待审核',
            1 => '已转入',
            2 => '已拒绝',
            3 => '已取消'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['apply_time'] = $item['apply_time'] ? date('Y-m-d H:i', $item['apply_time']) : '-';
            $item['process_time'] = $item['process_time'] ? date('Y-m-d H:i', $item['process_time']) : '-';
        }

        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Transfer 执行转校操作]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function transfer()
    {
        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $status = I('status', 1, 'intval'); // 1批准 2拒绝
            $remark = I('remark', '', 'trim');

            $transfer = M('transfer')->find($id);
            if (empty($transfer)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '转校记录不存在'));
            }

            if ($transfer['status'] != 0) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '该转校已处理'));
            }

            // 开启事务
            M()->startTrans();

            try {
                if ($status == 1) {
                    // 批准转校
                    // 1. 更新学员校区
                    $student_result = M('student')->where(array('id' => $transfer['student_id']))->save(array(
                        'campus_id' => $transfer['to_campus_id'],
                        'update_time' => time()
                    ));

                    // 2. 转移学员的班级关系
                    M('class_student')->where(array('student_id' => $transfer['student_id']))->delete();

                    // 3. 在目标校区创建默认班级关系
                    if ($transfer['to_class_id'] > 0) {
                        M('class_student')->add(array(
                            'student_id' => $transfer['student_id'],
                            'class_id' => $transfer['to_class_id'],
                            'add_time' => time()
                        ));
                    }

                    // 4. 转移学员的课时
                    $this->transferHours($transfer);

                    // 5. 记录调课记录
                    M('schedule_transfer')->add(array(
                        'student_id' => $transfer['student_id'],
                        'from_campus_id' => $transfer['from_campus_id'],
                        'to_campus_id' => $transfer['to_campus_id'],
                        'transfer_id' => $id,
                        'create_time' => time()
                    ));
                }

                // 更新转校状态
                $update_data = array(
                    'status' => $status,
                    'process_remark' => $remark,
                    'process_time' => time(),
                    'operator_id' => session('admin.id'),
                    'update_time' => time()
                );
                M('transfer')->where(array('id' => $id))->save($update_data);

                M()->commit();
                $this->ajaxReturn(array('code' => 1, 'msg' => $status == 1 ? '转校已批准' : '转校已拒绝'));

            } catch (\Exception $e) {
                M()->rollback();
                $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败：' . $e->getMessage()));
            }
        }

        $id = I('id', 0, 'intval');
        $transfer = M('transfer')->find($id);
        
        // 验证校区权限
        if ($this->tenant_campus_id > 0 && $transfer && $transfer['from_campus_id'] != $this->tenant_campus_id) {
            $this->error('无权操作此转校记录');
        }

        // 获取学员信息
        $student = M('student')->find($transfer['student_id']);
        // 获取校区信息
        $from_campus = M('campus')->find($transfer['from_campus_id']);
        $to_campus = M('campus')->find($transfer['to_campus_id']);
        // 获取班级信息
        $from_class = M('class')->find($transfer['from_class_id']);
        $to_class = M('class')->find($transfer['to_class_id']);

        $this->assign('transfer', $transfer);
        $this->assign('student', $student);
        $this->assign('from_campus', $from_campus);
        $this->assign('to_campus', $to_campus);
        $this->assign('from_class', $from_class);
        $this->assign('to_class', $to_class);
        $this->display();
    }

    /**
     * [GetHistory 学员转校历史]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function getHistory()
    {
        $student_id = I('student_id', 0, 'intval');

        // 按校区过滤学员
        $student_where = array('student_id' => $student_id);
        if ($this->tenant_campus_id > 0) {
            $student_where['from_campus_id'] = $this->tenant_campus_id;
        }
        $list = M('transfer')->where($student_where)
            ->order('id DESC')
            ->select();

        $status_map = array(
            0 => '待审核',
            1 => '已转入',
            2 => '已拒绝',
            3 => '已取消'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['apply_time'] = $item['apply_time'] ? date('Y-m-d H:i', $item['apply_time']) : '-';
            $item['process_time'] = $item['process_time'] ? date('Y-m-d H:i', $item['process_time']) : '-';
        }

        $this->ajaxReturn(array('code' => 1, 'data' => $list));
    }

    /**
     * [Add 申请转校]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function add()
    {
        if (IS_POST) {
            // 添加转校记录时自动设置源校区
            $data = array(
                'student_id' => I('post.student_id', 0, 'intval'),
                'from_campus_id' => $this->tenant_campus_id > 0 ? $this->tenant_campus_id : I('post.from_campus_id', 0, 'intval'),
                'to_campus_id' => I('post.to_campus_id', 0, 'intval'),
                'from_class_id' => I('post.from_class_id', 0, 'intval'),
                'to_class_id' => I('post.to_class_id', 0, 'intval'),
                'reason' => I('post.reason', '', 'trim'),
                'status' => 0,
                'apply_time' => time(),
                'create_time' => time()
            );

            if (empty($data['student_id'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择学员'));
            }
            if ($data['from_campus_id'] == $data['to_campus_id']) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '源校区和目标校区不能相同'));
            }

            $id = M('transfer')->add($data);
            if ($id > 0) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '转校申请已提交'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '提交失败'));
            }
        }

        // 获取学员列表（按校区过滤）
        $student_where = array();
        if ($this->tenant_campus_id > 0) {
            $student_where['campus_id'] = $this->tenant_campus_id;
        }
        $studentList = M('student')->field('id, username, number, my_mobile, campus_id')->where($student_where)->select();
        // 获取校区列表
        $campuses = M('campus')->field('id, name')->select();

        $this->assign('studentList', $studentList);
        $this->assign('campusList', $campuses);
        $this->display();
    }

    /**
     * [GetClassesByCampus 根据校区获取班级]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function getClassesByCampus()
    {
        $campus_id = I('campus_id', 0, 'intval');

        $classes = M('class')->field('id, class_name as name')
            ->where(array('campus_id' => $campus_id))
            ->select();

        $this->ajaxReturn(array('code' => 1, 'data' => $classes));
    }

    /**
     * [Cancel 取消转校申请]
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

        $transfer = M('transfer')->find($id);
        // 验证校区权限
        if ($this->tenant_campus_id > 0 && $transfer && $transfer['from_campus_id'] != $this->tenant_campus_id) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '无权操作此转校记录'));
        }
        
        if (empty($transfer)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '转校记录不存在'));
        }

        if ($transfer['status'] != 0) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '只能取消待审核的转校'));
        }

        $result = M('transfer')->where(array('id' => $id))->save(array(
            'status' => 3,
            'update_time' => time()
        ));

        if ($result !== false) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '转校申请已取消'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
        }
    }

    /**
     * [transferHours 转移学员课时]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [array] $transfer [转校记录]
     */
    private function transferHours($transfer)
    {
        // 将学员的课时记录转移到目标校区
        M('student_package')->where(array(
            'student_id' => $transfer['student_id']
        ))->save(array(
            'campus_id' => $transfer['to_campus_id']
        ));

        M('student_course')->where(array(
            'student_id' => $transfer['student_id']
        ))->save(array(
            'campus_id' => $transfer['to_campus_id']
        ));
    }
}
?>
