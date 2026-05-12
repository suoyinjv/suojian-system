<?php
namespace Admin\Controller;

/**
 * 报名管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class EnrollmentController extends CommonController
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
     * [Index 报名列表]
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
        $activity_id = I('activity_id', 0, 'intval');

        $where = array();
        if ($status >= 0) {
            $where['e.status'] = $status;
        }
        if ($activity_id > 0) {
            $where['e.activity_id'] = $activity_id;
        }
        if ($keyword) {
            $where[] = array(
                'l.student_name' => array('like', '%' . $keyword . '%'),
                'l.phone' => array('like', '%' . $keyword . '%'),
                '_logic' => 'or'
            );
        }

        $prefix = C('DB_PREFIX');
        $count = M('enrollment')->alias('e')
            ->join('LEFT JOIN ' . $prefix . 'leads l ON e.leads_id=l.id')
            ->where($where)->count();

        $list = M('enrollment')->alias('e')
            ->field('e.*, l.student_name, l.phone as lead_phone, m.title as activity_title')
            ->join('LEFT JOIN ' . $prefix . 'leads l ON e.leads_id=l.id')
            ->join('LEFT JOIN ' . $prefix . 'marketing_activity m ON e.activity_id=m.id')
            ->where($where)
            ->order('e.id DESC')
            ->page($page, $rows)
            ->select();

        $status_map = array(
            0 => '待确认',
            1 => '已报名',
            2 => '已缴费',
            3 => '已退款'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['enroll_time'] = $item['enroll_time'] ? date('Y-m-d H:i', $item['enroll_time']) : '-';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
        }

        // 获取营销活动列表（用于筛选）
        $activities = M('marketing_activity')->field('id, title')->order('id DESC')->select();
        $this->assign('activities', $activities);
        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [ChangeStatus 变更报名状态]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function changeStatus()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $status = I('status', 0, 'intval');
        $remark = I('remark', '', 'trim');

        $data = array(
            'status' => $status,
            'update_time' => time()
        );

        // 如果是已缴费，记录缴费时间
        if ($status == 2) {
            $data['pay_time'] = time();
        }

        // 如果是已退款，记录退款时间
        if ($status == 3) {
            $data['refund_time'] = time();
            $data['refund_remark'] = $remark;
        }

        $result = M('enrollment')->where(array('id' => $id))->save($data);

        if ($result !== false) {
            // 如果转化为正式学员
            if ($status == 2) {
                $this->convertToStudent($id);
            }
            $this->ajaxReturn(array('code' => 1, 'msg' => '状态更新成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '状态更新失败'));
        }
    }

    /**
     * [BatchChangeStatus 批量变更状态]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function batchChangeStatus()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $ids = I('ids', '');
        $status = I('status', 0, 'intval');

        if (empty($ids)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择要操作的记录'));
        }

        $id_arr = explode(',', $ids);
        $success = 0;
        foreach ($id_arr as $id) {
            $id = intval($id);
            if ($id > 0) {
                M('enrollment')->where(array('id' => $id))->save(array(
                    'status' => $status,
                    'update_time' => time()
                ));
                $success++;
            }
        }

        $this->ajaxReturn(array('code' => 1, 'msg' => '批量操作成功', 'data' => array('success' => $success)));
    }

    /**
     * [Detail 报名详情]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function detail()
    {
        $id = I('id', 0, 'intval');

        $info = M('enrollment')->find($id);
        if (empty($info)) {
            $this->error('报名记录不存在');
        }

        // 获取线索信息
        $leads = M('leads')->find($info['leads_id']);
        // 获取活动信息
        $activity = M('marketing_activity')->find($info['activity_id']);

        $this->assign('info', $info);
        $this->assign('leads', $leads);
        $this->assign('activity', $activity);
        $this->display();
    }

    /**
     * [Delete 删除报名记录]
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
        $result = M('enrollment')->where(array('id' => $id))->delete();

        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [convertToStudent 转化为正式学员]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [int] $enrollment_id [报名记录ID]
     */
    private function convertToStudent($enrollment_id)
    {
        $enrollment = M('enrollment')->find($enrollment_id);
        if (empty($enrollment) || empty($enrollment['leads_id'])) {
            return false;
        }

        $leads = M('leads')->find($enrollment['leads_id']);
        if (empty($leads)) {
            return false;
        }

        // 检查学员是否已存在
        $exist = M('student')->where(array('phone' => $leads['phone']))->find();
        if ($exist) {
            return false;
        }

        // 创建学员
        $student_id = M('student')->add(array(
            'name' => $leads['student_name'],
            'phone' => $leads['phone'],
            'wechat' => $leads['wechat'],
            'source' => 'marketing',
            'add_time' => time()
        ));

        // 更新报名记录关联学生
        if ($student_id > 0) {
            M('enrollment')->where(array('id' => $enrollment_id))->save(array('student_id' => $student_id));
            // 更新线索状态
            M('leads')->where(array('id' => $enrollment['leads_id']))->save(array('status' => 3));
            return true;
        }

        return false;
    }
}
?>
