<?php

namespace Admin\Model;
use Think\Model;

/**
 * 海报生成记录模型
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PosterRecordModel extends Model
{
	protected $tableName = 'sc_poster_record';

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
		// 解析variables JSON
		if(!empty($data['variables']))
		{
			$data['variables'] = json_decode($data['variables'], true);
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

		// 处理variables JSON
		if(!empty($data['variables']) && is_array($data['variables']))
		{
			$data['variables'] = json_encode($data['variables']);
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
	 * [addViews 增加浏览次数]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $id         [id]
	 * @return   array                   [结果]
	 */
	public function addViews($id)
	{
		if(empty($id))
		{
			return false;
		}
		return $this->where(array('id' => $id))->setInc('views');
	}

	/**
	 * [getStudentRecords 获取学生的海报记录]
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
		return $this->where(array('student_id' => $student_id))
			->order('create_time desc')
			->limit($limit)
			->select();
	}

	/**
	 * [generatePoster 生成海报记录]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-01T21:51:08+0800
	 * @param    int         $template_id [模板id]
	 * @param    int         $student_id [学生id]
	 * @param    array       $variables  [变量]
	 * @param    string      $image_url  [图片URL]
	 * @param    string      $qr_code    [二维码URL]
	 * @param    string      $source     [来源]
	 * @return   array                   [结果]
	 */
	public function generatePoster($template_id, $student_id, $variables = array(), $image_url = '', $qr_code = '', $source = '')
	{
		$data = array(
			'template_id' => $template_id,
			'student_id' => $student_id,
			'variables' => !empty($variables) ? json_encode($variables) : '',
			'image_url' => $image_url,
			'qr_code' => $qr_code,
			'source' => $source,
			'views' => 0,
		);

		$result = $this->data($data)->add();

		// 增加模板使用次数
		if($result && !empty($template_id))
		{
			D('PosterTemplate')->addUseCount($template_id);
		}

		return $result;
	}
}
?>
