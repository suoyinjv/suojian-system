<?php
namespace Admin\Controller;

/**
 * 营销活动管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class MarketingController extends CommonController
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
     * [Index 营销活动列表]
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

        // 计算活动状态（自动更新）
        $this->autoUpdateActivityStatus();

        $count = M('marketing_activity')->where($where)->count();
        $list = M('marketing_activity')->where($where)
            ->order('id DESC')
            ->page($page, $rows)
            ->select();

        $type_map = array(
            1 => '限时优惠',
            2 => '拼团',
            3 => '秒杀',
            4 => '优惠券',
            5 => '满减',
            6 => '折扣'
        );
        $status_map = array(
            0 => '待发布',
            1 => '进行中',
            2 => '已结束',
            3 => '已归档'
        );

        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '未知';
            $item['status_text'] = $status_map[$item['status']] ?: '未知';
            $item['start_time'] = $item['start_time'] ? date('Y-m-d H:i', $item['start_time']) : '-';
            $item['end_time'] = $item['end_time'] ? date('Y-m-d H:i', $item['end_time']) : '-';
            // 计算转化率
            $item['conversion_rate'] = $item['view_count'] > 0 ? round($item['signup_count'] / $item['view_count'] * 100, 2) . '%' : '0%';
        }

        $this->assign('type_map', $type_map);
        $this->assign('status_map', $status_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Save 新建/编辑活动]
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
                'start_time' => I('post.start_time', 0, 'strtotime'),
                'end_time' => I('post.end_time', 0, 'strtotime'),
                'rules' => json_encode(I('post.')),
                'status' => I('post.status', 0, 'intval'),
                'cover_image' => I('post.cover_image', '', 'trim'),
                'description' => I('post.description', '', 'trim'),
                'update_time' => time()
            );

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '活动名称不能为空'));
            }

            if ($id > 0) {
                // 编辑
                $result = M('marketing_activity')->where(array('id' => $id))->save($data);
                if ($result !== false) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '更新成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
                }
            } else {
                // 新增
                $data['create_time'] = time();
                $data['view_count'] = 0;
                $data['signup_count'] = 0;
                $id = M('marketing_activity')->add($data);
                if ($id > 0) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '添加失败'));
                }
            }
        } else {
            $id = I('id', 0, 'intval');
            if ($id > 0) {
                $info = M('marketing_activity')->find($id);
                $this->assign('info', $info);
            }
            $this->display();
        }
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

        $result = M('marketing_activity')->where(array('id' => $id))->save(array(
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
     * [Detail 活动详情+数据统计]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function detail()
    {
        $id = I('id', 0, 'intval');
        $info = M('marketing_activity')->find($id);

        if (empty($info)) {
            $this->error('活动不存在');
        }

        // 获取报名记录统计
        $signup_count = M('enrollment')->where(array('activity_id' => $id))->count();
        $paid_count = M('enrollment')->where(array('activity_id' => $id, 'status' => array('in', '1,2')))->count();

        // 趋势数据（最近7天）
        $trend_data = array();
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');
            $trend_data[] = array(
                'date' => $date,
                'views' => M('marketing_activity')->where(array('id' => $id, 'update_time' => array('between', $start . ',' . $end)))->count(),
                'signups' => M('enrollment')->where(array('activity_id' => $id, 'create_time' => array('between', $start . ',' . $end)))->count()
            );
        }

        $info['signup_count'] = $signup_count;
        $info['paid_count'] = $paid_count;
        $info['conversion_rate'] = $info['view_count'] > 0 ? round($signup_count / $info['view_count'] * 100, 2) : 0;
        $info['trend_data'] = $trend_data;

        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [Record 报名记录列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function record()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $activity_id = I('activity_id', 0, 'intval');
        $status = I('status', -1, 'intval');

        $where = array();
        if ($activity_id > 0) {
            $where['e.activity_id'] = $activity_id;
        }
        if ($status >= 0) {
            $where['e.status'] = $status;
        }

        $prefix = C('DB_PREFIX');
        $count = M('enrollment')->alias('e')->where($where)->count();
        $list = M('enrollment')->alias('e')
            ->field('e.*, l.student_name, l.phone as lead_phone')
            ->join('LEFT JOIN ' . $prefix . 'leads l ON e.leads_id=l.id')
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

        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->assign('activity_id', $activity_id);
        $this->display();
    }

    /**
     * [Report 招生数据看板API]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function report()
    {
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));

        // 营销活动统计
        $activity_stats = array(
            'total' => M('marketing_activity')->count(),
            'active' => M('marketing_activity')->where(array('status' => 1))->count(),
            'total_views' => M('marketing_activity')->sum('view_count'),
            'total_signups' => M('marketing_activity')->sum('signup_count')
        );

        // 报名转化统计
        $enrollment_stats = array(
            'total' => M('enrollment')->where("create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59')")->count(),
            'paid' => M('enrollment')->where("status IN(1,2) AND create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date} 23:59:59')")->count()
        );
        $enrollment_stats['conversion_rate'] = $activity_stats['total_views'] > 0 ? round($enrollment_stats['total'] / $activity_stats['total_views'] * 100, 2) : 0;

        // 每日趋势
        $daily_trend = array();
        $days = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime($start_date . " +{$i} days"));
            $daily_trend[] = array(
                'date' => $date,
                'activities' => M('marketing_activity')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d') = '{$date}'")->count(),
                'views' => M('marketing_activity')->where("status=1 AND start_time <= UNIX_TIMESTAMP('{$date} 23:59:59') AND end_time >= UNIX_TIMESTAMP('{$date} 00:00:00')")->sum('view_count'),
                'signups' => M('enrollment')->where("FROM_UNIXTIME(create_time, '%Y-%m-%d') = '{$date}'")->count()
            );
        }

        // 活动类型分布
        $type_distribution = M('marketing_activity')
            ->field('type, COUNT(*) as cnt, SUM(view_count) as views, SUM(signup_count) as signups')
            ->group('type')
            ->select();

        $this->ajaxReturn(array(
            'code' => 1,
            'data' => array(
                'activity_stats' => $activity_stats,
                'enrollment_stats' => $enrollment_stats,
                'daily_trend' => $daily_trend,
                'type_distribution' => $type_distribution
            )
        ));
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
        $result = M('marketing_activity')->where(array('id' => $id))->delete();

        if ($result) {
            // 同时删除相关报名记录
            M('enrollment')->where(array('activity_id' => $id))->delete();
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [autoUpdateActivityStatus 自动更新活动状态]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    private function autoUpdateActivityStatus()
    {
        $time = time();
        // 将已过期的活动状态更新为已结束
        M('marketing_activity')->where(array(
            'status' => 1,
            'end_time' => array('lt', $time)
        ))->save(array('status' => 2, 'update_time' => $time));
    }
}
?>
