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
		<!-- table nav start -->
		<ul class="am-nav am-nav-pills table-nav m-b-10">
	<li <?php if($nav_type == 'email'): ?>class="am-active"<?php endif; ?> data-type="email">
		<a href="<?php echo U('Admin/Email/Index', ['type'=>'email']);?>"><?php echo L('email_email_nav_name');?></a>
	</li>
	<li <?php if($nav_type == 'message'): ?>class="am-active"<?php endif; ?> data-type="message">
		<a href="<?php echo U('Admin/Email/Index', ['type'=>'message']);?>"><?php echo L('email_message_nav_name');?></a>
	</li>
</ul>
		<!-- table nav end -->
		
		<!-- form start -->
		<form class="am-form form-validation view-save" action="<?php echo U('Admin/Email/Save');?>" method="POST" request-type="ajax-url" request-value="<?php echo U('Admin/Email/Index', ['type'=>'email']);?>">
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_host"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_host"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["common_email_smtp_host"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_host"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_host"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_host"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_port"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_port"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["common_email_smtp_port"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_port"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_port"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_port"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_account"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_account"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["common_email_smtp_account"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_account"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_account"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_account"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_name"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_name"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["common_email_smtp_name"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_name"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_name"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_name"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_pwd"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_pwd"]["describe"]); ?>）</span></label>
				<input type="password" name="<?php echo ($data["common_email_smtp_pwd"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_pwd"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_pwd"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_pwd"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_email_smtp_send_name"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_email_smtp_send_name"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["common_email_smtp_send_name"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_email_smtp_send_name"]["name"]); ?>" data-validation-message="<?php echo ($data["common_email_smtp_send_name"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_email_smtp_send_name"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo L('email_test_email_text');?><span class="fs-12 fw-100 cr-999">（<?php echo L('email_test_email_tips');?>）</span></label>
				<div class="am-input-group am-input-group-sm">
					<span class='am-form-group'>
						<input type="text" placeholder="<?php echo L('email_test_email_text');?>" class="am-radius test-email-value" />
					</span>
					<span class="am-input-group-btn">
						<button class="am-btn am-btn-default am-radius test-email-submit" type="button" data-url="<?php echo U('Admin/Email/EmailTest');?>"><?php echo L('common_operation_test');?></button>
					</span>
				</div>
			</div>
			<div class="am-form-group">
				<button type="submit" class="am-btn am-btn-primary am-radius btn-loading-example am-btn-sm w100" data-am-loading="{loadingText:'<?php echo L('common_form_loading_tips');?>'}"><?php echo L('common_operation_save');?></button>
			</div>
		</form>
        <!-- form end -->
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