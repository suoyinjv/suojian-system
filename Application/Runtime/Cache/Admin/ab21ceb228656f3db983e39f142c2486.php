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
<!-- nav placeholder -->

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<div class="am-panel am-panel-warning">
			<div class="am-panel-hd">
				<h3 class="am-panel-title"><i class="fa fa-edit"></i> 编辑校区</h3>
			</div>
			<div class="am-panel-bd">
				<form class="am-form am-form-horizontal form-validation" action="<?php echo U('Admin/Campus/save'); ?>" method="POST">
					<input type="hidden" name="id" value="<?php echo $info['id']; ?>">

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">校区名称 <span class="am-text-danger">*</span></label>
						<div class="am-u-sm-7">
							<input type="text" name="name" class="am-form-field" value="<?php echo $info['name']; ?>" placeholder="请输入校区名称" required>
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">校区编码</label>
						<div class="am-u-sm-7">
							<input type="text" name="code" class="am-form-field" value="<?php echo $info['code']; ?>" placeholder="如: HQ, CD, SH">
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">校区地址</label>
						<div class="am-u-sm-7">
							<input type="text" name="address" class="am-form-field" value="<?php echo $info['address']; ?>" placeholder="详细地址">
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">联系电话</label>
						<div class="am-u-sm-7">
							<input type="text" name="phone" class="am-form-field" value="<?php echo $info['phone']; ?>" placeholder="校区联系电话">
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">负责人</label>
						<div class="am-u-sm-7">
							<input type="text" name="principal" class="am-form-field" value="<?php echo $info['principal']; ?>" placeholder="校区负责人姓名">
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-3 am-text-right">状态</label>
						<div class="am-u-sm-7">
							<select name="status" class="am-form-field">
								<option value="1" <?php if($info['status'] == 1): ?>selected<?php endif; ?>>启用</option>
								<option value="0" <?php if($info['status'] == 0): ?>selected<?php endif; ?>>禁用</option>
							</select>
						</div>
					</div>

					<div class="am-form-group">
						<div class="am-u-sm-7 am-u-sm-offset-3">
							<button type="submit" class="am-btn am-btn-primary am-radius"><i class="am-icon-check"></i> 保存修改</button>
							<a href="<?php echo U('Admin/Campus/index'); ?>" class="am-btn am-btn-default am-radius"><i class="am-icon-arrow-left"></i> 返回列表</a>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- right content end  -->

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