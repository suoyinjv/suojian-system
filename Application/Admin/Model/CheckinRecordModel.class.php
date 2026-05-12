<?php

namespace Admin\Model;
use Think\Model;

/**
 * 打卡记录模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CheckinRecordModel extends Model
{
	protected $tableName = 'sc_checkin_record';

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

		// 处理images JSON
		if(!empty($data['images']) && is_array($data['images']))
		{
			$data['images'] = json_encode($data['images']);
		}

		// 添加数据
		if(empty($data['id']))
		{
			$data['checkin_time'] = time();
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
	 * [getStudentRecords 获取学生的打卡记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $student_id [学生id]
	 * @param    int         $limit      [条数]
	 * @return   array                   [结果]
	 */
	public function getStudentRecords($student_id, $limit = 50)
	{
		if(empty($student_id))
		{
			return array();
		}
		return $this->where(array('student_id' => $student_id))
			->order('checkin_time desc')
			->limit($limit)
			->select();
	}

	/**
	 * [checkIn 打卡]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $task_id    [任务id]
	 * @param    int         $student_id [学生id]
	 * @param    array       $data       [数据]
	 * @return   array                   [结果]
	 */
	public function checkIn($task_id, $student_id, $data = array())
	{
		if(empty($task_id) || empty($student_id))
		{
			return false;
		}

		$save_data = array(
			'task_id' => $task_id,
			'student_id' => $student_id,
			'content' => isset($data['content']) ? $data['content'] : '',
			'images' => isset($data['images']) && is_array($data['images']) ? json_encode($data['images']) : '',
			'audio_url' => isset($data['audio_url']) ? $data['audio_url'] : '',
			'video_url' => isset($data['video_url']) ? $data['video_url'] : '',
			'status' => 0,
			'checkin_time' => time(),
		);

		return $this->data($save_data)->add();
	}
}
?>
