<?php
namespace Admin\Controller;

/**
 * 线索管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class LeadController extends CommonController
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
     * [Index 线索列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function index()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');

        // 搜索条件
        $keyword = I('keyword', '', 'trim');
        $status = I('status', 0, 'intval');
        $source = I('source', '', 'trim');

        $where = array();
        if ($keyword) {
            $where[] = array(
                'student_name' => array('like', '%' . $keyword . '%'),
                'phone' => array('like', '%' . $keyword . '%'),
                '_logic' => 'or'
            );
        }
        if ($status > 0) {
            $where['status'] = $status;
        }
        if ($source) {
            $where['source'] = $source;
        }

        $count = M('leads')->where($where)->count();
        $list = M('leads')->where($where)
            ->order('add_time DESC')
            ->page($page, $rows)
            ->select();

        // 处理状态
        $status_map = array(
            1 => '新线索',
            2 => '已跟进',
            3 => '已转化',
            4 => '已流失'
        );
        $source_map = array(
            'website' => '网站表单',
            'offline' => '线下地推',
            'viral' => '裂变海报',
            'tel' => '电话咨询',
            'referral' => '转介绍'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['source_text'] = $source_map[$item['source']] ?: '未知';
            $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i', $item['add_time']) : '-';
            $item['follow_time'] = $item['follow_time'] ? date('Y-m-d H:i', $item['follow_time']) : '-';
            // 解析跟进记录
            if ($item['follow_log']) {
                $item['follow_logs'] = json_decode($item['follow_log'], true);
            }
        }

        $this->assign('status_map', $status_map);
        $this->assign('source_map', $source_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->assign('count', $count);
        $this->display();
    }

    /**
     * [Add 添加线索]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function add()
    {
        if (IS_POST) {
            $data = array(
                'student_name' => I('post.student_name', '', 'trim'),
                'phone' => I('post.phone', '', 'trim'),
                'source' => I('post.source', 'website', 'trim'),
                'status' => 1,
                'wechat' => I('post.wechat', '', 'trim'),
                'age' => I('post.age', 0, 'intval'),
                'parent_name' => I('post.parent_name', '', 'trim'),
                'interest_course' => I('post.interest_course', '', 'trim'),
                'budget' => I('post.budget', 0, 'floatval'),
                'address' => I('post.address', '', 'trim'),
                'interest_level' => I('post.interest_level', 3, 'intval'),
                'add_time' => time()
            );

            if (empty($data['student_name']) || empty($data['phone'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '姓名和电话不能为空'));
            }

            // 检查电话是否重复
            $exist = M('leads')->where(array('phone' => $data['phone']))->find();
            if ($exist) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '该电话已存在'));
            }

            $result = M('leads')->add($data);
            if ($result) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '添加失败'));
            }
        }

        $this->display();
    }

    /**
     * [Edit 编辑线索]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function edit()
    {
        $id = I('id', 0, 'intval');

        if (IS_POST) {
            $data = array(
                'student_name' => I('post.student_name', '', 'trim'),
                'phone' => I('post.phone', '', 'trim'),
                'source' => I('post.source', 'website', 'trim'),
                'status' => I('post.status', 1, 'intval'),
                'wechat' => I('post.wechat', '', 'trim'),
                'age' => I('post.age', 0, 'intval'),
                'parent_name' => I('post.parent_name', '', 'trim'),
                'interest_course' => I('post.interest_course', '', 'trim'),
                'budget' => I('post.budget', 0, 'floatval'),
                'address' => I('post.address', '', 'trim'),
                'interest_level' => I('post.interest_level', 0, 'intval'),
                'update_time' => time()
            );

            if (empty($data['student_name']) || empty($data['phone'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '姓名和电话不能为空'));
            }

            $result = M('leads')->where(array('id' => $id))->save($data);
            if ($result !== false) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '更新成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
            }
        }

        $info = M('leads')->find($id);
        // 解析跟进记录
        if ($info && $info['follow_log']) {
            $info['follow_logs'] = json_decode($info['follow_log'], true);
        }
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [Follow 跟进记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function follow()
    {
        $leads_id = I('leads_id', 0, 'intval');

        if (IS_POST) {
            $data = array(
                'leads_id' => $leads_id,
                'user_id' => session('admin.id'),
                'content' => I('post.content', '', 'trim'),
                'next_time' => I('post.next_time', 0, 'strtotime'),
                'add_time' => time()
            );

            if (empty($data['content'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '跟进内容不能为空'));
            }

            // 添加跟进记录
            M('follow_log')->add($data);

            // 更新线索状态
            $update_data = array(
                'status' => 2,
                'follow_time' => time(),
                'update_time' => time()
            );

            // 如果设置了下次跟进时间
            if ($data['next_time'] > 0) {
                $update_data['next_follow_time'] = $data['next_time'];
            }

            // 更新意向等级
            $interest_level = I('post.interest_level', 0, 'intval');
            if ($interest_level > 0) {
                $update_data['interest_level'] = $interest_level;
            }

            // 更新状态
            $status = I('post.status', 0, 'intval');
            if ($status > 0) {
                $update_data['status'] = $status;
            }

            M('leads')->where(array('id' => $leads_id))->save($update_data);

            $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
        }

        // 获取跟进记录
        $logs = M('follow_log')->where(array('leads_id' => $leads_id))
            ->order('add_time DESC')
            ->select();

        $info = M('leads')->find($leads_id);

        $this->assign('logs', $logs);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [ExtendFollow 添加跟进记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function extendFollow()
    {
        if (IS_POST) {
            $id = I('id', 0, 'intval');
            $content = I('post.content', '', 'trim');
            $next_time = I('post.next_time', 0, 'strtotime');
            $interest_level = I('post.interest_level', 0, 'intval');

            if (empty($content)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '跟进内容不能为空'));
            }

            $leads = M('leads')->find($id);
            if (empty($leads)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '线索不存在'));
            }

            // 构建跟进记录
            $follow_data = array(
                'user_id' => session('admin.id'),
                'user_name' => session('admin.name'),
                'content' => $content,
                'create_time' => time()
            );

            // 获取原有跟进记录
            $old_logs = $leads['follow_log'] ? json_decode($leads['follow_log'], true) : array();
            $old_logs[] = $follow_data;

            // 更新线索
            $update_data = array(
                'follow_log' => json_encode($old_logs),
                'follow_time' => time(),
                'update_time' => time(),
                'status' => 2
            );

            if ($next_time > 0) {
                $update_data['next_follow_time'] = $next_time;
            }

            if ($interest_level > 0) {
                $update_data['interest_level'] = $interest_level;
            }

            $result = M('leads')->where(array('id' => $id))->save($update_data);

            if ($result !== false) {
                M('follow_log')->add(array(
                    'leads_id' => $id,
                    'user_id' => session('admin.id'),
                    'content' => $content,
                    'next_time' => $next_time,
                    'create_time' => time()
                ));
                $this->ajaxReturn(array('code' => 1, 'msg' => '跟进成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '跟进失败'));
            }
        }

        $id = I('id', 0, 'intval');
        $info = M('leads')->find($id);

        if ($info && $info['follow_log']) {
            $info['follow_logs'] = json_decode($info['follow_log'], true);
        }

        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [AssignLeads 分配线索]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function assignLeads()
    {
        if (IS_POST) {
            $ids = I('ids', '');
            $assigned_to = I('assigned_to', 0, 'intval');

            if (empty($ids)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择要分配的线索'));
            }
            if ($assigned_to <= 0) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择分配给谁'));
            }

            $user = M('admin')->find($assigned_to);
            if (empty($user)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '被分配人不存在'));
            }

            $id_arr = explode(',', $ids);
            $success = 0;

            foreach ($id_arr as $id) {
                $id = intval($id);
                if ($id > 0) {
                    M('leads')->where(array('id' => $id))->save(array(
                        'assigned_to' => $assigned_to,
                        'assigned_name' => $user['name'],
                        'assign_time' => time(),
                        'update_time' => time()
                    ));
                    $success++;
                }
            }

            $this->ajaxReturn(array('code' => 1, 'msg' => "成功分配{$success}条线索"));
        }

        $admins = M('admin')->field('id, name')->where(array('status' => 1))->select();
        $this->assign('admins', $admins);
        $this->display();
    }

    /**
     * [LeadsStats 线索统计API]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function leadsStats()
    {
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));
        $assigned_to = I('assigned_to', 0, 'intval');

        $where = "create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59')";
        if ($assigned_to > 0) {
            $where .= " AND assigned_to = {$assigned_to}";
        }

        // 总线索数
        $total = M('leads')->where($where)->count();

        // 各状态统计
        $status_stats = M('leads')
            ->field('status, COUNT(*) as cnt')
            ->where($where)
            ->group('status')
            ->select();

        // 来源统计
        $source_stats = M('leads')
            ->field('source, COUNT(*) as cnt')
            ->where($where)
            ->group('source')
            ->select();

        // 每日趋势
        $daily_trend = array();
        $days = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime($start_date . " +{$i} days"));
            $daily_trend[] = array(
                'date' => $date,
                'total' => M('leads')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d') = '{$date}'")->count(),
                'converted' => M('leads')->where("status=3 AND FROM_UNIXTIME(create_time, '%Y-%m-%d') = '{$date}'")->count()
            );
        }

        // 顾问排名
        $advisor_stats = M('leads')
            ->field('assigned_to, assigned_name, COUNT(*) as total, SUM(CASE WHEN status=3 THEN 1 ELSE 0 END) as converted')
            ->where($where . " AND assigned_to > 0")
            ->group('assigned_to')
            ->order('converted DESC')
            ->select();

        // 转化率
        $converted = M('leads')->where($where . " AND status=3")->count();
        $conversion_rate = $total > 0 ? round($converted / $total * 100, 2) : 0;

        $this->ajaxReturn(array(
            'code' => 1,
            'data' => array(
                'total' => $total,
                'converted' => $converted,
                'conversion_rate' => $conversion_rate,
                'status_stats' => $status_stats,
                'source_stats' => $source_stats,
                'daily_trend' => $daily_trend,
                'advisor_stats' => $advisor_stats
            )
        ));
    }

    /**
     * [Delete 删除线索]
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
        M('leads')->where(array('id' => $id))->delete();
        M('follow_log')->where(array('leads_id' => $id))->delete();
        $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
    }

    /**
     * [Export 导出线索]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function export()
    {
        $list = M('leads')->order('add_time DESC')->select();

        $status_map = array(
            1 => '新线索',
            2 => '已跟进',
            3 => '已转化',
            4 => '已流失'
        );
        $source_map = array(
            'website' => '网站表单',
            'offline' => '线下地推',
            'viral' => '裂变海报',
            'tel' => '电话咨询',
            'referral' => '转介绍'
        );

        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']];
            $item['source_text'] = $source_map[$item['source']];
            $item['add_time'] = date('Y-m-d H:i', $item['add_time']);
        }

        exportExcel(array('姓名', '电话', '来源', '状态', '意向等级', '添加时间'), $list, '线索列表');
    }
}
?>
