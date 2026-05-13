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
		<form class="am-form form-validation view-save" action="<?php echo U('Admin/Seo/Save');?>" method="POST" request-type="ajax-url" request-value="<?php echo U('Admin/Seo/Index');?>">
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_url_model"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_url_model"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_seo_url_model"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_seo_url_model"]["error_tips"]); ?>" required>
					<?php if(is_array($seo_url_model_list)): foreach($seo_url_model_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_seo_url_model']['value']) and $data['home_seo_url_model']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_url_html_suffix"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_url_html_suffix"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["home_seo_url_html_suffix"]["only_tag"]); ?>" pattern="<?php echo L('common_regex_url_html_suffix');?>" maxlength="8" placeholder="<?php echo ($data["home_seo_url_html_suffix"]["name"]); ?>" data-validation-message="<?php echo ($data["home_seo_url_html_suffix"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_seo_url_html_suffix"]["value"]); ?>"<?php endif; ?> />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_site_title"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_site_title"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["home_seo_site_title"]["only_tag"]); ?>" minlength="1" placeholder="<?php echo ($data["home_seo_site_title"]["name"]); ?>" data-validation-message="<?php echo ($data["home_seo_site_title"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_seo_site_title"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_site_keywords"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_site_keywords"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["home_seo_site_keywords"]["only_tag"]); ?>" placeholder="<?php echo ($data["home_seo_site_keywords"]["name"]); ?>" data-validation-message="<?php echo ($data["home_seo_site_keywords"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["home_seo_site_keywords"]["value"]); ?>"<?php endif; ?> />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_site_description"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_site_description"]["describe"]); ?>）</span></label>
				<textarea rows="6" name="<?php echo ($data["home_seo_site_description"]["only_tag"]); ?>" class="am-radius" placeholder="<?php echo ($data["home_seo_site_description"]["name"]); ?>" data-validation-message="<?php echo ($data["home_seo_site_description"]["error_tips"]); ?>"><?php if(isset($data)): echo ($data["home_seo_site_description"]["value"]); endif; ?></textarea>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_channel_browser"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_channel_browser"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_seo_channel_browser"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_seo_channel_browser"]["error_tips"]); ?>" required>
					<?php if(is_array($seo_channel_browser_list)): foreach($seo_channel_browser_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_seo_channel_browser']['value']) and $data['home_seo_channel_browser']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["home_seo_article_browser"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["home_seo_article_browser"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["home_seo_article_browser"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["home_seo_article_browser"]["error_tips"]); ?>" required>
					<?php if(is_array($seo_article_browser_list)): foreach($seo_article_browser_list as $key=>$v): ?><option value="<?php echo ($v["value"]); ?>" <?php if(isset($data['home_seo_article_browser']['value']) and $data['home_seo_article_browser']['value'] == $v['value']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
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