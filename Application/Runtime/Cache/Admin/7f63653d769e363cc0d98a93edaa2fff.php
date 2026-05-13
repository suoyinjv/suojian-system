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
		<form class="am-form form-validation view-save" action="<?php echo U('Admin/Site/Save');?>" method="POST" request-type="ajax-url" request-value="<?php echo U('Admin/Site/Index');?>" enctype="multipart/form-data">
			<input type="hidden" name="max_file_size" value="<?php echo MyC('home_max_limit_image', 2048000);?>" />
			<div class="am-form-group">
				<label><?php echo ($data["home_site_name"]["name"]); ?></label>
				<input type="text" name="<?php echo ($data["home_site_name"]["only_tag"]); ?>" minlength="1" placeholder="<?php echo ($data["home_site_name"]["name"]); ?>" data-validation-message="<?php echo ($data["home_site_name"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_site_name"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group am-form-file">
				<label class="block"><?php echo ($data["home_site_logo"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_site_logo"]["describe"]); ?>）</span></label>
				<button type="button" class="am-btn am-btn-default am-btn-sm am-radius">
				<i class="am-icon-cloud-upload"></i> <?php echo L('site_site_logo_text');?></button>
				<input type="file" name="<?php echo ($data["home_site_logo"]["only_tag"]); ?>_img" multiple data-validation-message="<?php echo ($data["home_site_logo"]["error_tips"]); ?>" accept="image/gif,image/jpeg,image/jpg,image/png" <?php if(isset($data)): ?>value="<?php echo ($data["home_site_logo"]["value"]); ?>"<?php endif; ?> />
				<input type="hidden" name="<?php echo ($data["home_site_logo"]["only_tag"]); ?>" <?php if(isset($data)): ?>value="<?php echo ($data["home_site_logo"]["value"]); ?>"<?php endif; ?> />
				<img src="<?php echo ($image_host); echo ($data["home_site_logo"]["value"]); ?>" id="form-img-logo" />
				<div id="form-file-logo-tips"></div>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_timezone"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_timezone"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["common_timezone"]["only_tag"]); ?>" class="am-radius chosen-select c-p" data-placeholder="<?php echo ($data["common_timezone"]["name"]); ?>" data-validation-message="<?php echo ($data["common_timezone"]["error_tips"]); ?>" required>
					<?php if(is_array($site_timezone_list)): foreach($site_timezone_list as $k=>$v): ?><option value="<?php echo ($k); ?>" <?php if(isset($data['common_timezone']['value']) and $data['common_timezone']['value'] == $k): ?>selected<?php endif; ?>><?php echo ($v); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_max_limit_image"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_max_limit_image"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["home_max_limit_image"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_max_limit_image"]["name"]); ?>" data-validation-message="<?php echo ($data["home_max_limit_image"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_max_limit_image"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_max_limit_file"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_max_limit_file"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["home_max_limit_file"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_max_limit_file"]["name"]); ?>" data-validation-message="<?php echo ($data["home_max_limit_file"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_max_limit_file"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_max_limit_video"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_max_limit_video"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["home_max_limit_video"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_max_limit_video"]["name"]); ?>" data-validation-message="<?php echo ($data["home_max_limit_video"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_max_limit_video"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_content_max_width"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_content_max_width"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["home_content_max_width"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_content_max_width"]["name"]); ?>" data-validation-message="<?php echo ($data["home_content_max_width"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_content_max_width"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_site_state"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_site_state"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_site_state"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_site_state"]["error_tips"]); ?>" required>
					<?php if(is_array($site_site_state_list)): foreach($site_site_state_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_site_state']['value']) and $data['home_site_state']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_site_close_reason"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_site_close_reason"]["describe"]); ?>）</span></label>
				<textarea rows="3" name="<?php echo ($data["home_site_close_reason"]["only_tag"]); ?>" class="am-radius" placeholder="<?php echo ($data["home_site_close_reason"]["name"]); ?>" data-validation-message="<?php echo ($data["home_site_close_reason"]["error_tips"]); ?>"><?php if(isset($data)): echo ($data["home_site_close_reason"]["value"]); endif; ?></textarea>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_user_reg_state"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_user_reg_state"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_user_reg_state"]["only_tag"]); ?>" multiple="multiple" class="am-radius chosen-select" data-placeholder="<?php echo ($data["home_user_reg_state"]["name"]); ?>" >
					<?php if(is_array($site_user_reg_state_list)): foreach($site_user_reg_state_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_user_reg_state']['value']) and in_array($v['value'], explode(',', $data['home_user_reg_state']['value']))): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>

			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_user_login_state"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_user_login_state"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_user_login_state"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_user_login_state"]["error_tips"]); ?>" required>
					<?php if(is_array($site_user_login_state_list)): foreach($site_user_login_state_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_user_login_state']['value']) and $data['home_user_login_state']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_verify_expire_time"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_verify_expire_time"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["common_verify_expire_time"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_verify_expire_time"]["name"]); ?>" data-validation-message="<?php echo ($data["common_verify_expire_time"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_verify_expire_time"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_verify_time_interval"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_verify_time_interval"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["common_verify_time_interval"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_verify_time_interval"]["name"]); ?>" data-validation-message="<?php echo ($data["common_verify_time_interval"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_verify_time_interval"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_img_verify_state"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_img_verify_state"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_img_verify_state"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_img_verify_state"]["error_tips"]); ?>" required>
					<?php if(is_array($site_img_verify_state_list)): foreach($site_img_verify_state_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_img_verify_state']['value']) and $data['home_img_verify_state']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_site_icp"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_site_icp"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["home_site_icp"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_site_icp"]["describe"]); ?>" data-validation-message="<?php echo ($data["home_site_icp"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_site_icp"]["value"]); ?>"<?php endif; ?> />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_footer_info"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_footer_info"]["describe"]); ?>）</span></label>
				<textarea rows="6" name="<?php echo ($data["home_footer_info"]["only_tag"]); ?>" class="am-radius" placeholder="<?php echo ($data["home_footer_info"]["name"]); ?>" data-validation-message="<?php echo ($data["home_footer_info"]["error_tips"]); ?>"><?php if(isset($data)): echo ($data["home_footer_info"]["value"]); endif; ?></textarea>
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
<!-- footer end