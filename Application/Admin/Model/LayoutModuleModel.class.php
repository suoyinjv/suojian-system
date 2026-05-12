<?php

namespace Admin\Model;

/**
 * 布局模块模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class LayoutModuleModel extends CommonModel
{
    // 数据自动校验
    protected $_validate = array(
        // 名称校验
        array('name', 'CheckName', '{%layoutmodule_name_format}', 1, 'callback', 3),
        // 唯一标识校验
        array('tag', 'CheckTag', '{%layoutmodule_tag_format}', 1, 'callback', 3),
        // 排序校验
        array('sort', 'CheckSort', '{%layoutmodule_sort_format}', 1, 'callback', 3),
        // 状态校验
        array('is_enable', array(0, 1), '{%common_status_tips}', 1, 'in', 3),
    );

    /**
     * [CheckName 模块名称校验]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-13T19:29:30+0800
     */
    public function CheckName()
    {
        $len = Utf8Strlen(I('name'));
        return ($len >= 2 && $len <= 30);
    }

    /**
     * [CheckTag 唯一标识校验]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-13T15:12:32+0800
     */
    public function CheckTag()
    {
        $tag = I('tag');
        if (empty($tag)) {
            return false;
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $tag)) {
            return false;
        }

        // 编辑时排除自身
        $where = array('tag' => $tag);
        $id = I('id', 0, 'intval');
        if ($id > 0) {
            $where['id'] = array('neq', $id);
        }

        return ($this->db(0)->where($where)->count() == 0);
    }

    /**
     * [CheckSort 排序值校验]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-13T15:12:32+0800
     */
    public function CheckSort()
    {
        $sort = I('sort', 0, 'intval');
        return ($sort >= 0 && $sort <= 9999);
    }
}
?>
