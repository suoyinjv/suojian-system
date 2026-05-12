<?php

namespace Admin\Model;
use Think\Model;

/**
 * 请假模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class LeaveModel extends Model
{
	protected $tableName = 'sc_leave';

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

		// 处理images JSON
		if(!empty($data['images']) && is_array($data['images']))
		{
			$data['images'] = json_encode($data['images']);
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
	 * [approve 审批请假]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $id         [id]
	 * @param    int         $status     [状态 1通过 2拒绝]
	 * @param    string      $remark     [备注]
	 * @param    int         $approver_id [审批人id]
	 * @return   array                   [结果]
	 */
	public function approve($id, $status, $remark = '', $approver_id = 0)
	{
		if(empty($id))
		{
			return false;
		}
		return $this->where(array('id' => $id))->save(array(
			'status' => $status,
			'approver_id' => $approver_id,
			'approve_time' => time(),
			'approve_remark' => $remark,
		));
	}

	/**
	 * [getStudentLeaves 获取学生的请假记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id [学生id]
	 * @param    int         $limit      [条数]
	 * @return   array                   [结果]
	 */
	public function getStudentLeaves($student_id, $limit = 20)
	{
		if(empty($student_id))
		{
			return array();
		}
		return $this->where(array('student_id' => $student_id))
			->order('create_time desc')
			->limit($limit)
			->select();
	}

	/**
	 * [checkLeaveStatus 检查请假状态]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id [学生id]
	 * @param    string      $date       [日期 Y-m-d]
	 * @return   array                   [结果]
	 */
	public function checkLeaveStatus($student_id, $date)
	{
		if(empty($student_id) || empty($date))
		{
			return array();
		}

		$start_date = strtotime($date);
		$end_date = strtotime($date . ' 23:59:59');

		return $this->where(array(
			'student_id' => $student_id,
			'status' => 1,
			'start_date' => array('elt', $end_date),
			'end_date' => array('egt', $start_date),
		))->find();
	}
}
?>
