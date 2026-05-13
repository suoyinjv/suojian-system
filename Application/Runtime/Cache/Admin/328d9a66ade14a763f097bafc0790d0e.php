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
		<form class="am-form form-validation view-save" action="<?php echo U('Admin/Config/Save');?>" method="POST" request-type="ajax-url" request-value="<?php echo U('Admin/Config/Index');?>">
			<div class="am-form-group">
				<label><?php echo ($data["admin_semester_id"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["admin_semester_id"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["admin_semester_id"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["admin_semester_id"]["error_tips"]); ?>" required>
					<option value=""><?php echo L('common_select_can_choose');?></option>
					<?php if(is_array($semester_list)): foreach($semester_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($data['admin_semester_id']['value']) and $data['admin_semester_id']['value'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["admin_excel_charset"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["admin_excel_charset"]["describe"]); ?>）</span></label>
				<select name="<?php echo ($data["admin_excel_charset"]["only_tag"]); ?>" class="am-radius c-p" data-validation-message="<?php echo ($data["admin_excel_charset"]["error_tips"]); ?>" required>
					<?php if(is_array($common_excel_charset_list)): foreach($common_excel_charset_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($data['admin_excel_charset']['value']) and $data['admin_excel_charset']['value'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["admin_page_number"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["admin_page_number"]["describe"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["admin_page_number"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_page_number"]["describe"]); ?>" pattern="<?php echo L('common_regex_page_number');?>" data-validation-message="<?php echo ($data["admin_page_number"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_page_number"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group form-fraction-list">
				<label class="block"><?php echo ($data["admin_fraction_weak"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["admin_fraction_weak"]["error_tips"]); ?>）</span></label>
				<input type="number" name="<?php echo ($data["admin_fraction_weak"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_fraction_weak"]["describe"]); ?>" pattern="<?php echo L('common_regex_score');?>" data-validation-message="<?php echo ($data["admin_fraction_weak"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_fraction_weak"]["value"]); ?>"<?php endif; ?> required />
				<input type="number" name="<?php echo ($data["admin_fraction_poor"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_fraction_poor"]["describe"]); ?>" pattern="<?php echo L('common_regex_score');?>" data-validation-message="<?php echo ($data["admin_fraction_poor"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_fraction_poor"]["value"]); ?>"<?php endif; ?> required />
				<input type="number" name="<?php echo ($data["admin_fraction_commonly"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_fraction_commonly"]["describe"]); ?>" pattern="<?php echo L('common_regex_score');?>" data-validation-message="<?php echo ($data["admin_fraction_commonly"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_fraction_commonly"]["value"]); ?>"<?php endif; ?> required />
				<input type="number" name="<?php echo ($data["admin_fraction_good"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_fraction_good"]["describe"]); ?>" pattern="<?php echo L('common_regex_score');?>" data-validation-message="<?php echo ($data["admin_fraction_good"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_fraction_good"]["value"]); ?>"<?php endif; ?> required />
				<input type="number" name="<?php echo ($data["admin_fraction_excellent"]["only_tag"]); ?>" placeholder="<?php echo ($data["admin_fraction_excellent"]["describe"]); ?>" pattern="<?php echo L('common_regex_score');?>" data-validation-message="<?php echo ($data["admin_fraction_excellent"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["admin_fraction_excellent"]["value"]); ?>"<?php endif; ?> required />
			</div>
			<div class="am-form-group">
				<label><?php echo ($data["common_image_proportion"]["name"]); ?><span class="fs-12 fw-100 cr-999">（<?php echo ($data["common_image_proportion"]["describe"]); ?>）</span></label>
				<input type="text" name="<?php echo ($data["common_image_proportion"]["only_tag"]); ?>" placeholder="<?php echo ($data["common_image_proportion"]["name"]); ?>" pattern="<?php echo L('common_regex_image_proportion');?>" data-validation-message="<?php echo ($data["common_image_proportion"]["error_tips"]); ?>" class="am-radius" <?php if(isset($data)): ?>value="<?php echo ($data["common_image_proportion"]["value"]); ?>"<?php endif; ?> required />
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