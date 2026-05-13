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
	<!-- 引入现代化主题 -->
	<link rel="stylesheet" type="text/css" href="/Public/Admin/<?php echo ($default_theme); ?>/Css/Theme.css" />
	<?php if(!empty($module_css)): ?><link rel="stylesheet" type="text/css" href="/Public/<?php echo ($module_css); ?>" /><?php endif; ?>
</head>
<body>

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<!-- form start -->
		<form class="am-form view-list" action="<?php echo U('Admin/Checkin/Index');?>" method="POST">
			<div class="am-g">
				<input type="text" class="am-radius form-keyword" placeholder="活动名称" name="name" <?php if(isset($param['name'])): ?>value="<?php echo ($param["name"]); ?>"<?php endif; ?> />
				<select name="type" class="am-radius c-p m-l-5">
					<option value="">全部类型</option>
					<option value="1" <?php if(isset($param['type']) and $param['type'] == 1): ?>selected<?php endif; ?>>日常打卡</option>
					<option value="2" <?php if(isset($param['type']) and $param['type'] == 2): ?>selected<?php endif; ?>>闯关模式</option>
				</select>
				<select name="status" class="am-radius c-p m-l-5">
					<option value="-1">全部状态</option>
					<option value="0" <?php if(isset($param['status']) and $param['status'] == 0): ?>selected<?php endif; ?>>未开始</option>
					<option value="1" <?php if(isset($param['status']) and $param['status'] == 1): ?>selected<?php endif; ?>>进行中</option>
					<option value="2" <?php if(isset($param['status']) and $param['status'] == 2): ?>selected<?php endif; ?>>已结束</option>
				</select>
				<button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit">搜索</button>
			</div>
		</form>
		<!-- form end -->

		<!-- operation start -->
		<div class="am-g m-t-15">
			<a href="<?php echo U('Admin/Checkin/SaveInfo');?>" class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus"> 新增打卡活动</a>
		</div>
		<!-- operation end -->

		<!-- list start -->
		<div class="am-g m-t-15">
			<?php if(!empty($list)): if(is_array($list)): foreach($list as $key=>$v): ?><div class="am-u-sm-6 am-u-lg-4 m-b-10">
						<div class="am-panel am-panel-primary am-radius">
							<div class="am-panel-hd am-text-truncate"><?php echo ($v["name"]); ?></div>
							<div class="am-panel-bd">
								<img src="<?php echo ($v["cover_image"]); ?>" class="am-img-responsive am-center m-b-10" style="height: 120px; object-fit: cover;" />
								<p class="am-text-sm">
									<span class="am-badge am-badge-secondary">
										<i class="am-icon-<?php echo ($v["type_icon"]); ?>"></i> <?php echo ($v["type_name"]); ?>
									</span>
								</p>
								<p class="am-text-sm">时间：<?php echo ($v["start_date"]); ?> ~ <?php echo ($v["end_date"]); ?></p>
								<p class="am-text-sm">参与：<?php echo ($v["join_count"]); ?>人 | 完成率：<?php echo ($v["completion_rate"]); ?>%</p>
								<p class="am-text-sm">
									<?php if($v['status'] == 0): ?><span class="am-badge">未开始</span>
									<?php elseif($v['status'] == 1): ?><span class="am-badge am-badge-primary">进行中</span>
									<?php else: ?><span class="am-badge am-badge-default">已结束</span><?php endif; ?>
								</p>
								<div class="am-cf m-t-10">
									<div class="am-fl">
										<a href="<?php echo U('Admin/Checkin/Stages', array('id'=>$v['id']));?>" class="am-btn am-btn-xs am-btn-primary am-radius am-icon-sitemap"> 闯关</a>
										<a href="<?php echo U('Admin/Checkin/Tasks', array('id'=>$v['id']));?>" class="am-btn am-btn-xs am-btn-secondary am-radius am-icon-tasks"> 任务</a>
									</div>
									<div class="am-fr">
										<a href="<?php echo U('Admin/Checkin/SaveInfo', array('id'=>$v['id']));?>" class="am-btn am-btn-xs am-btn-default am-radius am-icon-edit"></a>
										<a href="<?php echo U('Admin/Checkin/Detail', array('id'=>$v['id']));?>" class="am-btn am-btn-xs am-btn-default am-radius am-icon-eye"></a>
										<button class="am-btn am-btn-xs am-btn-default am-radius am-icon-trash-o submit-delete" data-url="<?php echo U('Admin/Checkin/Delete');?>" data-id="<?php echo ($v["id"]); ?>"></button>
									</div>
								</div>
							</div>
						</div>
					</div><?php endforeach; endif; ?>
			<?php else: ?>
				<div class="am-u-sm-12 am-text-center"><?php echo L('common_not_data_tips');?></div><?php endif; ?>
		</div>
		<!-- list end -->

		<!-- page start -->
		<?php if(!empty($list)): ?><div class="am-cf"><?php echo ($page_html); ?></div><?php endif; ?>
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