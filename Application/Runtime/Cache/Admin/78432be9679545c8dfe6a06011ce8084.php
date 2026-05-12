<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="<?php echo C('DEFAULT_CHARSET');?>" />
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1, maximum-scale=1">
	<title><?php echo L('common_site_title');?></title>
	<link rel="stylesheet" type="text/css" href="/Public/Common/Lib/assets/css/amazeui.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Common/Lib/amazeui-switch/amazeui.switch.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Common/Lib/amazeui-chosen/amazeui.chosen.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Common/Css/Common.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Admin/<?php echo ($default_theme); ?>/Css/Common.css" />
	<?php if(!empty($module_css)): ?><link rel="stylesheet" type="text/css" href="/Public/<?php echo ($module_css); ?>" /><?php endif; ?>
</head>
<body>

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<!-- form start -->
		<form class="am-form view-list" action="<?php echo U('Admin/Live/Index');?>" method="POST">
			<div class="am-g">
				<input type="text" class="am-radius form-keyword" placeholder="课程名称" name="name" <?php if(isset($param['name'])): ?>value="<?php echo ($param["name"]); ?>"<?php endif; ?> />
				<select name="course_type" class="am-radius c-p m-l-5">
					<option value="">全部类型</option>
					<option value="1" <?php if(isset($param['course_type']) and $param['course_type'] == 1): ?>selected<?php endif; ?>>1v1</option>
					<option value="2" <?php if(isset($param['course_type']) and $param['course_type'] == 2): ?>selected<?php endif; ?>>小班</option>
					<option value="3" <?php if(isset($param['course_type']) and $param['course_type'] == 3): ?>selected<?php endif; ?>>大班</option>
				</select>
				<select name="status" class="am-radius c-p m-l-5">
					<option value="-1">全部状态</option>
					<option value="0" <?php if(isset($param['status']) and $param['status'] == 0): ?>selected<?php endif; ?>>未开始</option>
					<option value="1" <?php if(isset($param['status']) and $param['status'] == 1): ?>selected<?php endif; ?>>直播中</option>
					<option value="2" <?php if(isset($param['status']) and $param['status'] == 2): ?>selected<?php endif; ?>>已结束</option>
				</select>
				<button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit"><?php echo L('common_operation_query');?></button>
			</div>
		</form>
		<!-- form end -->

		<!-- operation start -->
		<div class="am-g m-t-15">
	\t\t<a href="<?php echo U('Admin/Live/SaveInfo');?>" class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus"> 新增直播</a>
			<a href="<?php echo U('Admin/Live/resource');?>" class="am-btn am-btn-default am-radius am-btn-xs am-icon-folder"> 备课资源</a>
			<a href="<?php echo U('Admin/Live/replay');?>" class="am-btn am-btn-default am-radius am-btn-xs am-icon-play-circle"> 回放管理</a>
		</div>
		<!-- operation end -->

		<!-- list start -->
		<table class="am-table am-table-striped am-table-hover am-text-middle m-t-10">
			<thead>
				<tr>
					<th>ID</th>
					<th>课程名称</th>
					<th>类型</th>
					<th>最大人数</th>
					<th>直播平台</th>
					<th>开播时间</th>
					<th>录制状态</th>
					<th>状态</th>
					<th><?php echo L('common_operation_name');?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($list)): if(is_array($list)): foreach($list as $key=>$v): ?><tr id="data-list-<?php echo ($v["id"]); ?>">
							<td><?php echo ($v["id"]); ?></td>
							<td><?php echo ($v["name"]); ?></td>
							<td>
								<?php if($v['course_type'] == 1): ?><span class="am-badge am-badge-primary">1v1</span>
								<?php elseif($v['course_type'] == 2): ?><span class="am-badge am-badge-success">小班</span>
								<?php else: ?><span class="am-badge am-badge-warning">大班</span><?php endif; ?>
							</td>
							<td><?php echo ($v["max_students"]); ?></td>
							<td><?php echo ($v["platform"]); ?></td>
							<td><?php echo ($v["start_time"]); ?></td>
							<td>
								<?php if($v['record_status'] == 0): ?><span class="am-badge">未录制</span>
								<?php elseif($v['record_status'] == 1): ?><span class="am-badge am-badge-success">已录制</span>
								<?php else: ?><span class="am-badge am-badge-warning">录制中</span><?php endif; ?>
							</td>
							<td>
								<?php if($v['status'] == 0): ?><span class="am-badge">未开始</span>
								<?php elseif($v['status'] == 1): ?><span class="am-badge am-badge-primary">直播中</span>
								<?php else: ?><span class="am-badge am-badge-default">已结束</span><?php endif; ?>
							</td>
							<td class="view-operation">
								<a href="<?php echo U('Admin/Live/Detail', array('id'=>$v['id']));?>">
									<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-eye" data-am-popover="{content: '查看详情', trigger: 'hover focus'}"></button>
								</a>
								<a href="<?php echo U('Admin/Live/SaveInfo', array('id'=>$v['id']));?>">
									<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-edit" data-am-popover="{content: '<?php echo L('common_operation_edit');?>', trigger: 'hover focus'}"></button>
								</a>
								<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-trash-o submit-delete" data-url="<?php echo U('Admin/Live/Delete');?>" data-id="<?php echo ($v["id"]); ?>"></button>
							</td>
						</tr><?php endforeach; endif; ?>
				<?php else: ?>
					<tr><td colspan="9" class="table-no"><?php echo L('common_not_data_tips');?></td></tr><?php endif; ?>
			</tbody>
		</table>
		<!-- list end -->

		<!-- page start -->
		<?php if(!empty($list)): echo ($page_html); endif; ?>
		<!-- page end -->
	</div>
</div>
<!-- right content end  -->

<!-- footer start -->
<!-- commom html start -->
<!-- delete html start -->
<div class="am-modal am-modal-confirm" tabindex="-1" id="common-confirm-delete">
	<div class="am-modal-dialog am-radius">
		<div class="am-modal-bd"><?php echo L('common_delete_tips');?></div>
		<div class="am-modal-footer">
			<span class="am-modal-btn" data-am-modal-cancel><?php echo L('common_operation_cancel');?></span>
			<span class="am-modal-btn" data-am-modal-confirm><?php echo L('common_operation_confirm');?></span>
		</div>
	</div>
</div>
<!-- delete html end -->
<!-- commom html end -->
</body>
</html>

<!-- 类库 -->
<script type="text/javascript" src="/Public/Common/Lib/jquery/jquery-2.1.0.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/assets/js/amazeui.min.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/echarts/echarts.min.js"></script>

<!-- ueditor 编辑器 -->
<script type="text/javascript" src="/Public/Common/Lib/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/ueditor/lang/zh-cn/zh-cn.js"></script>

<!-- 颜色选择器 -->
<script type="text/javascript" src="/Public/Common/Lib/colorpicker/jquery.colorpicker.js"></script>

<!-- 元素拖拽排序插件 -->
<script type="text/javascript" src="/Public/Common/Lib/dragsort/jquery.dragsort-0.5.2.min.js"></script>

<!-- amazeui插件 -->
<script type="text/javascript" src="/Public/Common/Lib/amazeui-switch/amazeui.switch.min.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/amazeui-chosen/amazeui.chosen.min.js"></script>

<!-- 项目公共 -->
<script type="text/javascript" src="/Public/Common/Js/Common.js"></script>

<!-- 控制器 -->
<?php if(!empty($module_js)): ?><script type="text/javascript" src="/Public/<?php echo ($module_js); ?>"></script><?php endif; ?>
<!-- footer end -->