<?php
namespace Admin\Controller;

/**
 * 备课资源管理控制器
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2026-05-12T00:00:00+0800
 */
class ResourceController extends CommonController
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
     * [Index 资源列表（云盘功能）]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function index()
    {
        $page = I('p', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $type = I('type', '', 'trim');
        $course_id = I('course_id', 0, 'intval');
        $teacher_id = I('teacher_id', 0, 'intval');
        $keyword = I('keyword', '', 'trim');

        $where = array();
        
        // 多租户 - 校区过滤
        $campus_id = $this->tenant_campus_id;
        if ($campus_id > 0) {
            $where['r.campus_id'] = $campus_id;
        }
        
        if ($type) {
            $where['r.type'] = $type;
        }
        if ($course_id > 0) {
            $where['r.course_id'] = $course_id;
        }
        if ($teacher_id > 0) {
            $where['r.teacher_id'] = $teacher_id;
        }
        if ($keyword) {
            $where['r.title'] = array('like', '%' . $keyword . '%');
        }

        $prefix = C('DB_PREFIX');
        $count = M('live_resource')->alias('r')->where($where)->count();
        $list = M('live_resource')->alias('r')
            ->field('r.*, t.teacher_name, c.course_name')
            ->join('LEFT JOIN ' . $prefix . 'teacher t ON r.teacher_id=t.id')
            ->join('LEFT JOIN ' . $prefix . 'course c ON r.course_id=c.id')
            ->where($where)
            ->order('r.id DESC')
            ->page($page, $rows)
            ->select();

        $type_map = array(
            'doc' => '文档',
            'ppt' => 'PPT',
            'img' => '图片',
            'video' => '视频',
            'audio' => '音频'
        );

        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']] ?: '其他';
            $item['file_size_text'] = $this->formatFileSize($item['file_size']);
            $item['create_time'] = $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-';
        }

        // 多租户 - 校区过滤
        $campus_filter = $campus_id > 0 ? ['campus_id'=>$campus_id] : [];
        // 获取课程列表
        $courses = M('course')->where($campus_filter)->field('id, course_name')->select();
        // 获取教师列表
        $teachers = M('teacher')->where($campus_filter)->field('id, teacher_name')->select();

        $this->assign('type_map', $type_map);
        $this->assign('courses', $courses);
        $this->assign('teachers', $teachers);
        $this->assign('list', $list);
        $this->assign('page', getPage($count, $rows));
        $this->display();
    }

    /**
     * [Upload 上传文件]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function upload()
    {
        if (!IS_POST) {
            $this->display();
            return;
        }

        // 文件上传处理
        $config = array(
            'maxSize' => 1024 * 1024 * 500, // 500MB
            'rootPath' => './Uploads/Resource/',
            'savePath' => date('Ymd') . '/',
            'saveName' => array('uniqid', ''),
            'exts' => array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'mp3', 'wav', 'zip', 'rar'),
            'autoSub' => true,
            'subName' => array('date', 'Ym')
        );

        $upload = new \Think\Upload($config);
        $info = $upload->upload();

        if (!$info) {
            $this->ajaxReturn(array('code' => 0, 'msg' => $upload->getError()));
        }

        // 保存文件信息
        $file = $info['file'];
        $data = array(
            'course_id' => I('course_id', 0, 'intval'),
            'teacher_id' => I('teacher_id', 0, 'intval'),
            'title' => I('title', $file['name'], 'trim'),
            'type' => $this->getFileType($file['ext']),
            'file_url' => '/Uploads/Resource/' . $file['savepath'] . $file['savename'],
            'file_size' => $file['size'],
            'create_time' => time(),
            'campus_id' => $this->tenant_campus_id
        );

        $id = M('live_resource')->add($data);
        if ($id > 0) {
            $this->ajaxReturn(array('code' => 1, 'msg' => '上传成功', 'data' => array('id' => $id)));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
        }
    }

    /**
     * [Save 保存资源信息]
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
                'course_id' => I('post.course_id', 0, 'intval'),
                'title' => I('post.title', '', 'trim'),
                'description' => I('post.description', '', 'trim')
            );

            if (empty($data['title'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '资源名称不能为空'));
            }

            if ($id > 0) {
                $data['update_time'] = time();
                $result = M('live_resource')->where(array('id' => $id))->save($data);
            } else {
                $result = false;
            }

            if ($result !== false) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '保存成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
            }
        }

        $id = I('id', 0, 'intval');
        if ($id > 0) {
            // 多租户 - 校区过滤
            $where = array('id' => $id);
            if ($this->tenant_campus_id > 0) {
                $where['campus_id'] = $this->tenant_campus_id;
            }
            $info = M('live_resource')->where($where)->find();
            $this->assign('info', $info);
        }

        // 多租户 - 校区过滤
        $campus_filter = $this->tenant_campus_id > 0 ? ['campus_id'=>$this->tenant_campus_id] : [];
        // 获取课程列表
        $courses = M('course')->where($campus_filter)->field('id, course_name')->select();
        $this->assign('courses', $courses);
        $this->display();
    }

    /**
     * [Delete 删除文件]
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
        $info = M('live_resource')->find($id);

        if (empty($info)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '资源不存在'));
        }
        
        // 多租户 - 校区校验
        if ($this->tenant_campus_id > 0 && $info['campus_id'] != $this->tenant_campus_id) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '无权限删除该资源'));
        }

        // 删除物理文件
        $file_path = ROOT_PATH . $info['file_url'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        $result = M('live_resource')->where(array('id' => $id))->delete();

        if ($result) {
            // 删除分享记录
            M('resource_share')->where(array('resource_id' => $id))->delete();
            $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
    }

    /**
     * [Share 分享资源给课程]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     */
    public function share()
    {
        if (IS_POST) {
            $resource_id = I('resource_id', 0, 'intval');
            $course_id = I('course_id', 0, 'intval');

            if ($resource_id <= 0 || $course_id <= 0) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
            }

            // 检查是否已分享
            $exist = M('resource_share')->where(array(
                'resource_id' => $resource_id,
                'course_id' => $course_id
            ))->find();

            if ($exist) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '已分享过该课程'));
            }

            $result = M('resource_share')->add(array(
                'resource_id' => $resource_id,
                'course_id' => $course_id,
                'create_time' => time()
            ));

            if ($result) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '分享成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '分享失败'));
            }
        }

        // 获取可分享的课程（多租户过滤）
        $campus_filter = $this->tenant_campus_id > 0 ? ['campus_id'=>$this->tenant_campus_id] : [];
        $courses = M('course')->where($campus_filter)->field('id, course_name')->select();
        $this->assign('courses', $courses);
        $this->display();
    }
    
    /**
     * [getFileType 获取文件类型]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [string] $ext [扩展名]
     */
    private function getFileType($ext)
    {
        $ext = strtolower($ext);
        $type_map = array(
            'doc' => array('doc', 'docx', 'txt', 'pdf'),
            'ppt' => array('ppt', 'pptx'),
            'img' => array('jpg', 'jpeg', 'png', 'gif', 'bmp'),
            'video' => array('mp4', 'avi', 'mov', 'wmv'),
            'audio' => array('mp3', 'wav', 'ogg', 'aac')
        );

        foreach ($type_map as $type => $exts) {
            if (in_array($ext, $exts)) {
                return $type;
            }
        }
        return 'other';
    }

    /**
     * [formatFileSize 格式化文件大小]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2026-05-12T00:00:00+0800
     * @param    [int] $size [字节大小]
     */
    private function formatFileSize($size)
    {
        if ($size >= 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024 * 1024), 2) . ' GB';
        } elseif ($size >= 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return $size . ' B';
        }
    }
}
?>
