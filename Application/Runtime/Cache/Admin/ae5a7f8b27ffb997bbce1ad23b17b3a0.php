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
        <!-- operation start -->
        <div class="am-g">
            <button class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus tree-submit-add" data-am-modal="{target: '#data-save-win'}"> <?php echo L('common_operation_add');?></button>
        </div>
        <!-- operation end -->

        <!-- save win start -->
        <div class="am-popup am-radius" id="data-save-win">
			<div class="am-popup-inner">
				<div class="am-popup-hd">
					<h4 class="am-popup-title" data-add-title="<?php echo L('interval_add_name');?>" data-edit-title="<?php echo L('interval_edit_name');?>"><?php echo L('interval_add_name');?></h4>
					<span data-am-modal-close class="am-close">&times;</span>
				</div>
				<div class="am-popup-bd">
					<!-- form start -->
					<form class="am-form form-validation admin-save" action="<?php echo U('Admin/Interval/Save');?>" method="POST" request-type="ajax-reload" request-value="">
						<div class="am-form-group">
							<label><?php echo L('common_name_text');?><span class="fs-12 fw-100 cr-999">（<?php echo L('interval_name_format_tips');?>）</span></label>
							<input type="text" placeholder="<?php echo L('common_name_text');?>" name="name" pattern="<?php echo L('common_regex_interval');?>" data-validation-message="<?php echo L('interval_name_format');?>" class="am-radius" required />
						</div>
						<div class="am-form-group">
							<label><?php echo L('common_view_sort_title');?></label>
							<input type="number" placeholder="<?php echo L('common_view_sort_title');?>" name="sort" min="0" max="255" data-validation-message="<?php echo L('common_sort_error');?>" class="am-radius" value="0" required />
						</div>
						<!-- 是否启用 开始 -->
<div class="am-form-group">
	<label><?php echo L('common_view_enable_title');?></label>
	<div>
		<?php if(is_array($common_is_enable_list)): foreach($common_is_enable_list as $key=>$v): ?><label class="am-radio-inline m-r-10">
				<input type="radio" name="is_enable" value="<?php echo ($v["id"]); ?>" <?php if(isset($data['is_enable']) and $data['is_enable'] == $v['id']): ?>checked="checked"<?php else: if(!isset($data['is_enable']) and isset($v['checked']) and $v['checked'] == true): ?>checked="checked"<?php endif; endif; ?> data-am-ucheck /> <?php echo ($v["name"]); ?>
			</label><?php endforeach; endif; ?>
	</div>
</div>
<!-- 是否启用 结束 -->
						<div class="am-form-group">
							<input type="hidden" name="id" />
							<button type="submit" class="am-btn am-btn-primary am-radius btn-loading-example am-btn-sm w100" data-am-loading="{loadingText:'<?php echo L('common_form_loading_tips');?>'}"><?php echo L('common_operation_save');?></button>
						</div>
					</form>
					<!-- form end -->
				</div>
			</div>
		</div>
		<!-- save win end -->

        <!-- list start -->
		<div id="tree" class="m-t-15">
			<div class="m-t-30 t-c">
				<img src="/Public/Common/Images/loading.gif" />
				<p><?php echo L('common_form_loading_tips');?></p>
			</div>
		</div>
		<!-- list end -->
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
<script>
	Tree(0, "<?php echo U('Admin/Interval/GetNodeSon');?>", 0);
</script>