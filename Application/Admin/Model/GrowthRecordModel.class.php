<?php

namespace Admin\Model;
use Think\Model;

/**
 * 成长记录模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class GrowthRecordModel extends Model
{
	protected $tableName = 'sc_growth_record';

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
		// 解析images JSON
		if(!empty($data['images']))
		{
			$data['images'] = json_decode($data['images'], true);
		}
		// 解析tags
		if(!empty($data['tags']))
		{
			$data['tags'] = explode(',', $data['tags']);
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

		// 处理images JSON
		if(!empty($data['images']) && is_array($data['images']))
		{
			$data['images'] = json_encode($data['images']);
		}

		// 处理tags
		if(!empty($data['tags']) && is_array($data['tags']))
		{
			$data['tags'] = implode(',', $data['tags']);
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
	 * [getStudentRecords 获取学生的成长记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id [学生id]
	 * @param    int         $limit      [条数]
	 * @return   array                   [结果]
	 */
	public function getStudentRecords($student_id, $limit = 20)
	{
		if(empty($student_id))
		{
			return array();
		}
		return $this->where(array(
			'student_id' => $student_id,
			'is_public' => 1,
		))->order('create_time desc')->limit($limit)->select();
	}

	/**
	 * [getPublicList 获取公开的成长记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    array       $map        [条件]
	 * @param    int         $page       [页码]
	 * @param    int         $rows       [每页数量]
	 * @return   array                   [结果]
	 */
	public function getPublicList($map = array(), $page = 1, $rows = 20)
	{
		$map['is_public'] = 1;
		return $this->getList($map, $page, $rows, 'create_time desc');
	}
}
?>
