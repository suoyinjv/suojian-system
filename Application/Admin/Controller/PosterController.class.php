<?php
namespace Admin\Controller;

/**
 * 海报设计管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class PosterController extends CommonController
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
     * [Index 海报模板列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function index()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $category = I('category', 0, 'intval');
        $keyword = I('keyword', '', 'trim');

        $where = array();
        if ($category > 0) {
            $where['category'] = $category;
        }
        if ($keyword) {
            $where['title'] = array('like', '%' . $keyword . '%');
        }

        $count = M('poster_template')->where($where)->count();
        $list = M('poster_template')->where($where)
            ->order('id DESC')
            ->page($page, $rows)
            ->select();

        $category_map = array(
            1 => '招生宣传',
            2 => '活动邀请',
            3 => '节日祝福',
            4 => '课程介绍',
            5 => '喜报展示'
        );

        foreach ($list as &$item) {
            $item['category_text'] = $category_map[$item['category']] ?: '其他';
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
            // 解析变量
            if ($item['variables']) {
                $item['variables'] = json_decode($item['variables'], true);
            }
        }

        $this->assign('category_map', $category_map);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Save 保存/编辑模板]
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
                'category' => I('post.category', 1, 'intval'),
                'thumbnail' => I('post.thumbnail', '', 'trim'),
                'background' => I('post.background', '', 'trim'),
                'content' => I('post.content', '', 'trim'),
                'variables' => json_encode(I('post.variables', array())),
                'width' => I('post.width', 750, 'intval'),
                'height' => I('post.height', 1334, 'intval'),
                'update_time' => time()
            );

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '模板名称不能为空'));
            }

            if ($id > 0) {
                $result = M('poster_template')->where(array('id' => $id))->save($data);
                if ($result !== false) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '更新成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
                }
            } else {
                $data['create_time'] = time();
                $id = M('poster_template')->add($data);
                if ($id > 0) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '添加失败'));
                }
            }
        } else {
            $id = I('id', 0, 'intval');
            if ($id > 0) {
                $info = M('poster_template')->find($id);
                // 解析JSON字段
                if ($info && $info['variables']) {
                    $info['variables'] = json_decode($info['variables'], true);
                }
                $this->assign('info', $info);
            }
            $this->display();
        }
    }

    /**
     * [UploadThumbnail 上传缩略图]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function uploadThumbnail()
    {
        if (!IS_POST || empty($_FILES['thumbnail'])) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择图片'));
        }

        $config = array(
            'maxSize' => 1024 * 1024 * 5, // 5MB
            'rootPath' => './Uploads/Poster/',
            'savePath' => date('Ymd') . '/',
            'saveName' => array('uniqid', ''),
            'exts' => array('jpg', 'jpeg', 'png'),
            'autoSub' => true,
            'subName' => array('date', 'Ym')
        );

        $upload = new \Think\Upload($config);
        $info = $upload->uploadOne($_FILES['thumbnail']);

        if (!$info) {
            $this->ajaxReturn(array('code' => 0, 'msg' => $upload->getError()));
        }

        $url = '/Uploads/Poster/' . $info['savepath'] . $info['savename'];
        $this->ajaxReturn(array('code' => 1, 'msg' => '上传成功', 'data' => array('url' => $url)));
    }

    /**
     * [Generate 生成海报]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function generate()
    {
        if (IS_POST) {
            $template_id = I('template_id', 0, 'intval');
            $student_id = I('student_id', 0, 'intval');
            $variables = I('post.');

            if ($template_id <= 0) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择模板'));
            }

            $template = M('poster_template')->find($template_id);
            if (empty($template)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '模板不存在'));
            }

            // 获取学生信息用于变量替换
            $student = array();
            if ($student_id > 0) {
                $student = M('student')->find($student_id);
            }

            // 变量替换
            $content = $template['content'];
            foreach ($variables as $key => $value) {
                $content = str_replace('{' . $key . '}', $value, $content);
            }
            // 替换学生信息变量
            if ($student) {
                $content = str_replace('{student_name}', $student['name'], $content);
                $content = str_replace('{student_phone}', $student['phone'], $content);
            }
            // 替换时间变量
            $content = str_replace('{date}', date('Y-m-d'), $content);
            $content = str_replace('{time}', date('Y-m-d H:i'), $content);

            // 生成记录
            $record_id = M('poster_record')->add(array(
                'template_id' => $template_id,
                'student_id' => $student_id,
                'content' => $content,
                'variables' => json_encode($variables),
                'qrcode_url' => '',
                'create_time' => time()
            ));

            $this->ajaxReturn(array(
                'code' => 1,
                'msg' => '生成成功',
                'data' => array(
                    'record_id' => $record_id,
                    'content' => $content
                )
            ));
        }

        // 获取模板列表
        $templates = M('poster_template')->order('id DESC')->select();
        // 获取学生列表
        $students = M('student')->field('id, name, phone')->select();

        $this->assign('templates', $templates);
        $this->assign('students', $students);
        $this->display();
    }

    /**
     * [BatchGenerate 批量生成]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function batchGenerate()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $template_id = I('template_id', 0, 'intval');
        $student_ids = I('student_ids', '');
        $variables = I('post.');

        if ($template_id <= 0) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择模板'));
        }
        if (empty($student_ids)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请选择学员'));
        }

        $template = M('poster_template')->find($template_id);
        if (empty($template)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '模板不存在'));
        }

        $id_arr = explode(',', $student_ids);
        $success = 0;
        $errors = array();

        foreach ($id_arr as $student_id) {
            $student_id = intval($student_id);
            if ($student_id <= 0) continue;

            $student = M('student')->find($student_id);
            if (empty($student)) continue;

            // 变量替换
            $content = $template['content'];
            foreach ($variables as $key => $value) {
                $content = str_replace('{' . $key . '}', $value, $content);
            }
            $content = str_replace('{student_name}', $student['name'], $content);
            $content = str_replace('{student_phone}', $student['phone'], $content);
            $content = str_replace('{date}', date('Y-m-d'), $content);

            $record_id = M('poster_record')->add(array(
                'template_id' => $template_id,
                'student_id' => $student_id,
                'content' => $content,
                'variables' => json_encode($variables),
                'qrcode_url' => '',
                'create_time' => time()
            ));

            if ($record_id > 0) {
                $success++;
            } else {
                $errors[] = $student['name'];
            }
        }

        $msg = "成功生成{$success}张海报";
        if (!empty($errors)) {
            $msg .= "，" . implode('、', $errors) . "生成失败";
        }

        $this->ajaxReturn(array('code' => 1, 'msg' => $msg, 'data' => array('success' => $success)));
    }

    /**
     * [RecordList 生成记录列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function recordList()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $template_id = I('template_id', 0, 'intval');

        $where = array();
        if ($template_id > 0) {
            $where['r.template_id'] = $template_id;
        }

        $prefix = C('DB_PREFIX');
        $count = M('poster_record')->alias('r')->where($where)->count();
        $list = M('poster_record')->alias('r')
            ->field('r.*, p.title as template_title, s.username as student_name')
            ->join('LEFT JOIN ' . $prefix . 'poster_template p ON r.template_id=p.id')
            ->join('LEFT JOIN ' . $prefix . 'student s ON r.student_id=s.id')
            ->where($where)
            ->order('r.id DESC')
            ->page($page, $rows)
            ->select();

        foreach ($list as &$item) {
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
        }

        // 获取模板列表
        $templates = M('poster_template')->field('id, title')->select();
        $this->assign('templates', $templates);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Download 下载海报]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function download()
    {
        $id = I('id', 0, 'intval');

        $record = M('poster_record')->find($id);
        if (empty($record)) {
            $this->error('记录不存在');
        }

        // 这里应该调用图片合成服务生成实际图片
        // 简化处理，直接返回内容供前端渲染
        $this->assign('record', $record);
        $this->display();
    }

    /**
     * [GetQrCode 获取带参数二维码]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function getQrCode()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $record_id = I('record_id', 0, 'intval');
        $page = I('page', 'pages/index/index');
        $scene = I('scene', '');

        if ($record_id > 0) {
            $scene = 'poster_' . $record_id;
        }

        // 生成小程序码或二维码
        // 这里需要调用微信接口，简化处理
        $qrcode_url = '/Uploads/Qrcode/' . date('Ymd') . '/poster_' . $record_id . '.png';

        // 更新记录
        if ($record_id > 0) {
            M('poster_record')->where(array('id' => $record_id))->save(array(
                'qrcode_url' => $qrcode_url,
                'update_time' => time()
            ));
        }

        $this->ajaxReturn(array(
            'code' => 1,
            'data' => array(
                'qrcode_url' => $qrcode_url,
                'page' => $page,
                'scene' => $scene
            )
        ));
    }

    /**
     * [Delete 删除模板]
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

        // 删除模板
        M('poster_template')->where(array('id' => $id))->delete();
        // 删除生成记录
        M('poster_record')->where(array('template_id' => $id))->delete();

        $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
    }

    /**
     * [DeleteRecord 删除生成记录]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function deleteRecord()
    {
        if (!IS_AJAX) {
            $this->error(L('common_unauthorized_access'));
        }

        $id = I('id', 0, 'intval');
        $result = M('poster_record')->where(array('id' => $id))->delete();

        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }
}
?>
