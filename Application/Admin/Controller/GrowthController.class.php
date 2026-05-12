<?php
namespace Admin\Controller;

/**
 * 成长记录管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class GrowthController extends CommonController
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
     * [Index 成长记录列表]
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
        $student_id = I('student_id', 0, 'intval');
        $keyword = I('keyword', '', 'trim');

        $where = array();
        if ($type > 0) {
            $where['g.type'] = $type;
        }
        if ($student_id > 0) {
            $where['g.student_id'] = $student_id;
        }
        if ($keyword) {
            $where['g.title'] = array('like', '%' . $keyword . '%');
        }

        $prefix = C('DB_PREFIX');
        $count = M('growth_record')->alias('g')->where($where)->count();
        $list = M('growth_record')->alias('g')
            ->field('g.*, s.username as student_name, t.teacher_name')
            ->join('LEFT JOIN ' . $prefix . 'student s ON g.student_id=s.id')
            ->join('LEFT JOIN ' . $prefix . 'teacher t ON g.teacher_id=t.id')
            ->where($where)
            ->order('g.id DESC')
            ->page($page, $rows)
            ->select();

        $type_map = array(
            1 => '课堂表现',
            2 => '比赛获奖',
            3 => '活动参与',
            4 => '日常记录'
        );

        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '未知';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
            // 解析图片
            if ($item['images']) {
                $item['images'] = json_decode($item['images'], true);
            }
            // 解析标签
            if ($item['tags']) {
                $item['tags'] = json_decode($item['tags'], true);
            }
        }

        // 获取学生列表
        $students = M('student')->field('id, name')->select();
        $this->assign('type_map', $type_map);
        $this->assign('students', $students);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Save 添加/编辑成长记录]
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
                'type' => I('post.type', 1, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'images' => json_encode(I('post.images', array())),
                'video_url' => I('post.video_url', '', 'trim'),
                'teacher_id' => I('post.teacher_id', 0, 'intval'),
                'tags' => json_encode(I('post.tags', array())),
                'is_public' => I('post.is_public', 1, 'intval')
            );

            if (empty($data['student_id'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择学生'));
            }
            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请填写标题'));
            }

            if ($id > 0) {
                $data['update_time'] = time();
                $result = M('growth_record')->where(array('id' => $id))->save($data);
                if ($result !== false) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '更新成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
                }
            } else {
                $data['create_time'] = time();
                $id = M('growth_record')->add($data);
                if ($id > 0) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '添加失败'));
                }
            }
        } else {
            $id = I('id', 0, 'intval');
            if ($id > 0) {
                $info = M('growth_record')->find($id);
                // 解析JSON字段
                if ($info['images']) {
                    $info['images'] = json_decode($info['images'], true);
                }
                if ($info['tags']) {
                    $info['tags'] = json_decode($info['tags'], true);
                }
                $this->assign('info', $info);
            }

            // 获取学生列表
            $students = M('student')->field('id, name')->select();
            // 获取教师列表
            $teachers = M('teacher')->field('id, teacher_name as name')->select();
            $this->assign('students', $students);
            $this->assign('teachers', $teachers);
            $this->display();
        }
    }

    /**
     * [Detail 成长档案详情]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function detail()
    {
        $id = I('id', 0, 'intval');

        $info = M('growth_record')->find($id);
        if (empty($info)) {
            $this->error('记录不存在');
        }

        // 获取学生信息
        $student = M('student')->find($info['student_id']);
        // 获取教师信息
        $teacher = M('teacher')->find($info['teacher_id']);

        // 解析JSON字段
        if ($info['images']) {
            $info['images'] = json_decode($info['images'], true);
        }
        if ($info['tags']) {
            $info['tags'] = json_decode($info['tags'], true);
        }

        // 获取该学生的其他记录
        $other_records = M('growth_record')->where(array(
            'student_id' => $info['student_id'],
            'id' => array('neq', $id)
        ))->order('id DESC')->limit(5)->select();

        $type_map = array(
            1 => '课堂表现',
            2 => '比赛获奖',
            3 => '活动参与',
            4 => '日常记录'
        );

        foreach ($other_records as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '未知';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d', $item['create_time']) : '-';
        }

        $this->assign('info', $info);
        $this->assign('student', $student);
        $this->assign('teacher', $teacher);
        $this->assign('type_map', $type_map);
        $this->assign('other_records', $other_records);
        $this->display();
    }

    /**
     * [GetByStudent 获取某学生的全部成长记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function getByStudent()
    {
        $student_id = I('student_id', 0, 'intval');
        $type = I('type', 0, 'intval');

        $where = array(
            'student_id' => $student_id,
            'is_public' => 1
        );
        if ($type > 0) {
            $where['type'] = $type;
        }

        $list = M('growth_record')->where($where)
            ->order('create_time DESC')
            ->select();

        $type_map = array(
            1 => '课堂表现',
            2 => '比赛获奖',
            3 => '活动参与',
            4 => '日常记录'
        );

        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '未知';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d', $item['create_time']) : '-';
            // 解析图片
            if ($item['images']) {
                $item['images'] = json_decode($item['images'], true);
            }
        }

        // 统计
        $stats = array(
            'total' => count($list),
            'type_stats' => M('growth_record')
                ->field('type, COUNT(*) as cnt')
                ->where(array('student_id' => $student_id, 'is_public' => 1))
                ->group('type')
                ->select()
        );

        $this->ajaxReturn(array('code' => 1, 'data' => $list, 'stats' => $stats));
    }

    /**
     * [Delete 删除记录]
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

        // 获取记录信息，删除图片
        $info = M('growth_record')->find($id);
        if ($info && $info['images']) {
            $images = json_decode($info['images'], true);
            foreach ($images as $img) {
                $file_path = ROOT_PATH . $img;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
        }

        $result = M('growth_record')->where(array('id' => $id))->delete();

        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [Share 分享成长记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function share()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $is_public = I('is_public', 1, 'intval');

        $result = M('growth_record')->where(array('id' => $id))->save(array(
            'is_public' => $is_public,
            'update_time' => time()
        ));

        if ($result !== false) {
            $this->ajaxReturn(array('code' => 1, 'msg' => $is_public ? '已设置为公开' : '已设置为私有'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '操作失败'));
        }
    }

    /**
     * [Album 相册视图]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function album()
    {
        $student_id = I('student_id', 0, 'intval');

        // 获取所有有图片的记录
        $list = M('growth_record')->where(array(
            'student_id' => $student_id,
            'is_public' => 1
        ))->order('create_time DESC')->select();

        // 整理图片
        $photos = array();
        foreach ($list as $item) {
            if ($item['images']) {
                $images = json_decode($item['images'], true);
                foreach ($images as $img) {
                    $photos[] = array(
                        'url' => $img,
                        'title' => $item['title'],
                        'create_time' => $item['create_time']
                    );
                }
            }
        }

        // 获取学生信息
        $student = M('student')->find($student_id);

        $this->assign('photos', $photos);
        $this->assign('student', $student);
        $this->display();
    }
}
?>
