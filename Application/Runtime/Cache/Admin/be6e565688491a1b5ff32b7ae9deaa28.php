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
		<form class="am-form view-list" action="<?php echo U('Admin/Finance/Income');?>" method="POST">
			<div class="am-g">
				<input type="text" class="am-radius form-keyword" placeholder="订单号/学员姓名" name="keyword" <?php if(isset($param['keyword'])): ?>value="<?php echo ($param["keyword"]); ?>"<?php endif; ?> />
				<select name="type" class="am-radius c-p m-l-5">
					<option value="">全部类型</option>
					<option value="1" <?php if(isset($param['type']) and $param['type'] == 1): ?>selected<?php endif; ?>>课程报名</option>
					<option value="2" <?php if(isset($param['type']) and $param['type'] == 2): ?>selected<?php endif; ?>>资料购买</option>
					<option value="3" <?php if(isset($param['type']) and $param['type'] == 3): ?>selected<?php endif; ?>>续费</option>
					<option value="4" <?php if(isset($param['type']) and $param['type'] == 4): ?>selected<?php endif; ?>>其他</option>
				</select>
				<button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit"><?php echo L('common_operation_query');?></button>
			</div>
		</form>
		<!-- form end -->

		<!-- list start -->
		<table class="am-table am-table-striped am-table-hover am-text-middle m-t-10">
			<thead>
				<tr>
					<th>ID</th>
					<th>订单号</th>
					<th>学员姓名</th>
					<th>金额</th>
					<th>类型</th>
					<th>支付方式</th>
					<th>时间</th>
					<th>状态</th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($list)): if(is_array($list)): foreach($list as $key=>$v): ?><tr id="data-list-<?php echo ($v["id"]); ?>">
							<td><?php echo ($v["id"]); ?></td>
							<td><?php echo ($v["order_no"]); ?></td>
							<td><?php echo ($v["student_name"]); ?></td>
							<td class="am-text-primary">¥<?php echo ($v["amount"]); ?></td>
							<td>
								<?php if($v['type'] == 1): ?><span class="am-badge am-badge-primary">课程报名</span>
								<?php elseif($v['type'] == 2): ?><span class="am-badge am-badge-success">资料购买</span>
								<?php elseif($v['type'] == 3): ?><span class="am-badge am-badge-warning">续费</span>
								<?php else: ?><span class="am-badge am-badge-secondary">其他</span><?php endif; ?>
							</td>
							<td><?php echo ($v["pay_method"]); ?></td>
							<td><?php echo ($v["add_time"]); ?></td>
							<td>
								<?php if($v['status'] == 0): ?><span class="am-badge">待支付</span>
								<?php elseif($v['status'] == 1): ?><span class="am-badge am-badge-success">已支付</span>
								<?php else: ?><span class="am-badge am-badge-danger">已退款</span><?php endif; ?>
							</td>
						</tr><?php endforeach; endif; ?>
				<?php else: ?>
					<tr><td colspan="8" class="table-no"><?php echo L('common_not_data_tips');?></td></tr><?php endif; ?>
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