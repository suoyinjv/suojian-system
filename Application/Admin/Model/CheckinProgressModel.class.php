<?php

namespace Admin\Model;
use Think\Model;

/**
 * 学生打卡进度模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CheckinProgressModel extends Model
{
	protected $tableName = 'sc_checkin_progress';

	/**
	 * [$_auto 自动完成]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 */
	protected $_auto = array();

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
	 * [getStudentProgress 获取学生的活动进度]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $activity_id [活动id]
	 * @param    int         $student_id [学生id]
	 * @return   array                   [结果]
	 */
	public function getStudentProgress($activity_id, $student_id)
	{
		if(empty($activity_id) || empty($student_id))
		{
			return array();
		}
		return $this->where(array(
			'activity_id' => $activity_id,
			'student_id' => $student_id
		))->find();
	}

	/**
	 * [initProgress 初始化学生进度]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $activity_id [活动id]
	 * @param    int         $student_id [学生id]
	 * @param    int         $total_days [总天数]
	 * @return   array                   [结果]
	 */
	public function initProgress($activity_id, $student_id, $total_days = 0)
	{
		if(empty($activity_id) || empty($student_id))
		{
			return false;
		}

		$exist = $this->getStudentProgress($activity_id, $student_id);
		if(!empty($exist))
		{
			return $exist['id'];
		}

		$data = array(
			'activity_id' => $activity_id,
			'student_id' => $student_id,
			'current_day' => 0,
			'total_days' => $total_days,
			'total_points' => 0,
			'continuous_days' => 0,
			'status' => 0,
		);

		return $this->data($data)->add();
	}

	/**
	 * [updateProgress 更新学生进度]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $activity_id [活动id]
	 * @param    int         $student_id [学生id]
	 * @param    int         $points      [积分]
	 * @return   array                   [结果]
	 */
	public function updateProgress($activity_id, $student_id, $points = 0)
	{
		if(empty($activity_id) || empty($student_id))
		{
			return false;
		}

		$progress = $this->getStudentProgress($activity_id, $student_id);
		if(empty($progress))
		{
			return false;
		}

		$data = array(
			'current_day' => $progress['current_day'] + 1,
			'total_points' => $progress['total_points'] + $points,
			'continuous_days' => $progress['continuous_days'] + 1,
			'last_checkin_time' => time(),
		);

		// 检查是否完成
		if($data['current_day'] >= $progress['total_days'])
		{
			$data['status'] = 2; // 已完成
		}

		return $this->where(array('id' => $progress['id']))->save($data);
	}
}
?>
