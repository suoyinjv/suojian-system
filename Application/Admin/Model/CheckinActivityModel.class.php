<?php

namespace Admin\Model;
use Think\Model;

/**
 * 打卡活动模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CheckinActivityModel extends Model
{
	protected $tableName = 'sc_checkin_activity';

	/**
	 * [$_auto 自动完成]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 */
	protected $_auto = array(
		array('create_time', 'time', 1, 'function'),
	);

	/**
	 * [getList 获取列表]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    array       $map        [条件]
	 * @param    int         $page       [页码]
	 * @param    int         $rows       [每页数量]
	 * @param    string      $order      [排序]
	 * @return   array                   [结果]
	 */
	public function getList($map = array(), $page = 1, $rows = 20, $order = 'id desc')
	{
		$count = $this->where($map)->count();
		$page = max($page, 1);
		$rows = max($rows, 1);
		$offset = ($page - 1) * $rows;
		$list = $this->where($map)->order($order)->limit($offset, $rows)->select();
		return array('list' => $list, 'total' => $count, 'page' => $page, 'rows' => $rows);
	}

	/**
	 * [getDetail 获取详情]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $id         [id]
	 * @return   array                   [结果]
	 */
	public function getDetail($id)
	{
		if(empty($id))
		{
			return array();
		}
		$data = $this->where(array('id' => $id))->find();
		// 解析rules JSON
		if(!empty($data['rules']))
		{
			$data['rules'] = json_decode($data['rules'], true);
		}
		return $data;
	}

	/**
	 * [saveData 保存数据]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    array       $data       [数据]
	 * @return   array                   [结果]
	 */
	public function saveData($data = array())
	{
		if(empty($data))
		{
			$data = I('post.');
		}

		// 处理日期字段
		if(!empty($data['start_date']))
		{
			$data['start_date'] = strtotime($data['start_date']);
		}
		if(!empty($data['end_date']))
		{
			$data['end_date'] = strtotime($data['end_date']);
		}

		// 处理rules JSON
		if(!empty($data['rules']) && is_array($data['rules']))
		{
			$data['rules'] = json_encode($data['rules']);
		}

		// 添加数据
		if(empty($data['id']))
		{
			$result = $this->data($data)->add();
		}
		else
		{
			$result = $this->where(array('id' => $data['id']))->save($data);
		}
		return $result;
	}

	/**
	 * [deleteData 删除数据]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $id         [id]
	 * @return   array                   [结果]
	 */
	public function deleteData($id)
	{
		if(empty($id))
		{
			return false;
		}
		return $this->where(array('id' => $id))->delete();
	}

	/**
	 * [changeStatus 修改状态]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $id         [id]
	 * @param    int         $status     [状态]
	 * @return   array                   [结果]
	 */
	public function changeStatus($id, $status)
	{
		if(empty($id))
		{
			return false;
		}
		return $this->where(array('id' => $id))->save(array('status' => $status));
	}
}
?>
