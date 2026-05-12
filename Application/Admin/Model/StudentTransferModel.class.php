<?php

namespace Admin\Model;
use Think\Model;

/**
 * 学员转校记录模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class StudentTransferModel extends Model
{
	protected $tableName = 'sc_student_transfer';

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
		return $this->where(array('id' => $id))->find();
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

		// 处理时间字段
		if(!empty($data['transfer_time']))
		{
			$data['transfer_time'] = is_numeric($data['transfer_time']) ? $data['transfer_time'] : strtotime($data['transfer_time']);
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
	 * [getStudentTransfers 获取学生的转校记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id [学生id]
	 * @return   array                   [结果]
	 */
	public function getStudentTransfers($student_id)
	{
		if(empty($student_id))
		{
			return array();
		}
		return $this->where(array('student_id' => $student_id))
			->order('transfer_time desc')
			->select();
	}

	/**
	 * [transfer 执行转校]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id     [学生id]
	 * @param    int         $from_campus_id [原校区id]
	 * @param    int         $to_campus_id   [目标校区id]
	 * @param    string      $reason         [原因]
	 * @param    int         $operator_id    [操作人id]
	 * @return   array                       [结果]
	 */
	public function transfer($student_id, $from_campus_id, $to_campus_id, $reason = '', $operator_id = 0)
	{
		if(empty($student_id) || empty($to_campus_id))
		{
			return false;
		}

		// 添加转校记录
		$data = array(
			'student_id' => $student_id,
			'from_campus_id' => $from_campus_id,
			'to_campus_id' => $to_campus_id,
			'reason' => $reason,
			'operator_id' => $operator_id,
			'transfer_time' => time(),
		);

		return $this->data($data)->add();
	}

	/**
	 * [getTransferStats 获取转校统计]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    array       $map        [条件]
	 * @return   array                   [结果]
	 */
	public function getTransferStats($map = array())
	{
		$result = $this->where($map)->field(array(
			'COUNT(*)' => 'total',
			'from_campus_id',
			'to_campus_id',
		))->group('to_campus_id')->select();

		return $result;
	}
}
?>
