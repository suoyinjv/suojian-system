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
<header class="am-topbar am-topbar-inverse admin-header">
	<div class="am-topbar-brand">
		<a href="<?php echo U('Admin/Index/Index');?>">
			<h2><?php echo L('common_site_name');?><span class="admin-site-vice-name m-l-5"><?php echo L('common_site_vice_name');?></span></h2>
		</a>
	</div>
	<button class="am-topbar-btn am-topbar-toggle am-btn am-btn-sm am-btn-success am-show-sm-only am-radius header-nav-submit" data-am-collapse="{target: '#topbar-collapse'}">
		<span class="am-sr-only"><?php echo L('nav_switch_text');?></span>
		<i class="am-icon-bars"></i>
	</button>
	<div class="am-collapse am-topbar-collapse" id="topbar-collapse">
		<ul class="am-nav am-nav-pills am-topbar-nav am-topbar-right admin-header-list tpl-header-list">
			<li class="am-dropdown">
				<a href="<?php echo __MY_URL__;?>" target="_blank" class="tpl-header-list-link">
					<i class="am-icon-home"></i>
					<span><?php echo L('common_toview_home_text');?></span>
				</a>
			</li>
			
			<?php if(!IsMobile()): ?><li class="am-dropdown am-hide-sm-only">
					<a href="javascript:;" id="admin-fullscreen" class="tpl-header-list-link">
						<i class="am-icon-arrows-alt"></i>
						<span class="admin-fulltext" fulltext-open="<?php echo L('nav_fulltext_open');?>" fulltext-exit="<?php echo L('nav_fulltext_exit');?>"><?php echo L('nav_fulltext_open');?></span>
					</a>
				</li><?php endif; ?>
			<li class="am-dropdown common-nav-top" data-am-dropdown data-am-dropdown-toggle>
				<a class="am-dropdown-toggle tpl-header-list-link" href="javascript:;">
					<i class="am-icon-user"></i>
					<span class="tpl-header-list-user-nick"><?php echo ($admin["username"]); ?></span>
					<span class="tpl-header-list-user-ico">
					</span>
				</a>
				<ul class="am-dropdown-content">
					<li>
						<a href="javascript:;" data-type="nav" data-url="<?php echo U('Admin/Admin/SaveInfo', array('id'=>$admin['id']));?>">
							<i class="am-icon-cog"></i>
							<?php echo L('common_set_up_the_text');?>
						</a>
					</li>
					<li>
						<a href="<?php echo U('Admin/Admin/Logout');?>">
							<i class="am-icon-power-off"></i>
							<?php echo L('common_logout_text');?>
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</header>

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<div class="am-panel am-panel-primary">
			<div class="am-panel-hd">
				<h3 class="am-panel-title">
					<i class="am-icon-plus"></i> <?php if(isset($data['id'])): echo L('message_edit_template'); else: echo L('message_add_template'); endif; ?>
				</h3>
			</div>
			<div class="am-panel-bd">
				<form class="am-form am-form-horizontal form-validation" action="<?php echo U('Admin/Message/saveTemplate'); ?>" method="POST">

					<?php if(isset($data['id'])): ?>
					<input type="hidden" name="id" value="<?php echo $data['id']; ?>" />
					<?php endif; ?>

					<div class="am-form-group">
						<label class="am-u-sm-2 am-text-right"><?php echo L('message_template_name'); ?> <span class="am-text-danger">*</span></label>
						<div class="am-u-sm-8">
							<input type="text" name="name" class="am-form-field am-radius" placeholder="<?php echo L('message_template_name_tips'); ?>" value="<?php if(isset($data)): echo $data['name']; endif; ?>" required />
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-2 am-text-right"><?php echo L('message_template_type'); ?></label>
						<div class="am-u-sm-8">
							<select name="type" class="am-form-field am-radius">
								<option value="1" <?php if(isset($data) and $data['type'] == 1): ?>selected<?php endif; ?>><?php echo L('message_type_notice'); ?></option>
								<option value="2" <?php if(isset($data) and $data['type'] == 2): ?>selected<?php endif; ?>><?php echo L('message_type_marketing'); ?></option>
								<option value="3" <?php if(isset($data) and $data['type'] == 3): ?>selected<?php endif; ?>><?php echo L('message_type_reminder'); ?></option>
								<option value="4" <?php if(isset($data) and $data['type'] == 4): ?>selected<?php endif; ?>><?php echo L('message_type_other'); ?></option>
							</select>
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-2 am-text-right"><?php echo L('message_send_channel'); ?></label>
						<div class="am-u-sm-8">
							<select name="channel" class="am-form-field am-radius">
								<option value="sms" <?php if(isset($data) and $data['channel'] == 'sms'): ?>selected<?php endif; ?>><?php echo L('message_channel_sms'); ?></option>
								<option value="weixin" <?php if(isset($data) and $data['channel'] == 'weixin'): ?>selected<?php endif; ?>><?php echo L('message_channel_weixin'); ?></option>
								<option value="both" <?php if(isset($data) and $data['channel'] == 'both'): ?>selected<?php endif; ?>><?php echo L('message_channel_both'); ?></option>
							</select>
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-2 am-text-right"><?php echo L('message_template_content'); ?> <span class="am-text-danger">*</span></label>
						<div class="am-u-sm-8">
							<textarea name="content" class="am-form-field am-radius" rows="6" placeholder="<?php echo L('message_template_content_tips'); ?>" required><?php if(isset($data)): echo $data['content']; endif; ?></textarea>
							<p class="am-text-sm am-text-muted"><?php echo L('message_template_var_tips'); ?>: {name}, {course}, {date}, {time}, {class}</p>
						</div>
					</div>

					<div class="am-form-group">
						<label class="am-u-sm-2 am-text-right"><?php echo L('message_template_status'); ?></label>
						<div class="am-u-sm-8">
							<select name="status" class="am-form-field am-radius">
								<option value="1" <?php if(!isset($data) or $data['status'] == 1): ?>selected<?php endif; ?>><?php echo L('common_status_on'); ?></option>
								<option value="0" <?php if(isset($data) and $data['status'] == 0): ?>selected<?php endif; ?>><?php echo L('common_status_off'); ?></option>
							</select>
						</div>
					</div>

					<div class="am-form-group">
						<div class="am-u-sm-8 am-u-sm-offset-2">
							<button type="submit" class="am-btn am-btn-primary am-radius"><i class="am-icon-check"></i> <?php echo L('common_operation_save'); ?></button>
							<a href="<?php echo U('Admin/Message/template'); ?>" class="am-btn am-btn-default am-radius"><i class="am-icon-arrow-left"></i> <?php echo L('common_operation_cancel'); ?></a>
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