<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="<?php echo C('DEFAULT_CHARSET');?>" />
	<title><?php echo ($home_seo_site_title); ?></title>
	<meta name="keywords" content="<?php echo MyC('home_seo_site_keywords');?>" />
	<meta name="description" content="<?php echo MyC('home_seo_site_description');?>" />
	<meta name="generator" content="<?php echo __MY_URL__;?>" />
	<meta name="application-name" content="<?php echo ($home_seo_site_title); ?>" />
	<meta name="msapplication-tooltip" content="<?php echo ($home_seo_site_title); ?>" />
	<meta name="msapplication-starturl" content="<?php echo __MY_URL__;?>" />
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo __MY_URL__;?>favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1, maximum-scale=1">
	<link rel="stylesheet" type="text/css" href="/Public/Common/Lib/assets/css/amazeui.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Common/Css/Common.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Home/<?php echo ($default_theme); ?>/Css/Common.css" />
	<?php if(!empty($module_css)): ?><link rel="stylesheet" type="text/css" href="/Public/<?php echo ($module_css); ?>" /><?php endif; ?>
	<script>
		var __root__ = '';
		var __my_url__ = '<?php echo __MY_URL__;?>';
		var __public__ = '/Public';
		var __default_theme__ = '<?php echo ($default_theme); ?>';
		var __user_id__ = '<?php echo ($user["id"]); ?>';
	</script>
</head>
<body>

<!-- content start -->
<div class="am-g my-content" <?php if(!empty($max_width_style)): ?>style="<?php echo ($max_width_style); ?>"<?php endif; ?>>
	<div class="am-panel am-panel-default am-radius my-error">
		<div class="am-panel-bd"><?php echo ($msg); ?></div>
	</div>
</div>
<!-- content end -->

<!-- footer start -->
<!-- footer start -->
<?php if(!isset($is_footer) or $is_footer == 1): ?><hr data-am-widget="divider" class="am-divider am-divider-default m-0" />
<footer data-am-widget="footer" class="am-footer am-footer-default" data-am-footer="{}" <?php if(!empty($max_width_style)): ?>style="<?php echo ($max_width_style); ?>"<?php endif; ?>>
	<div class="footer-nav-list">
		<?php if(is_array($nav_footer)): foreach($nav_footer as $k=>$v): ?><a href="<?php echo ($v["url"]); ?>" <?php if($v['is_new_window_open'] == 1): ?>target="_blank"<?php endif; ?>><?php echo ($v["name"]); ?></a>
			<?php if($k < count($nav_footer)-1): ?><span class="am-footer-divider">| </span><?php endif; endforeach; endif; ?>
	</div>
	<div class="am-footer-miscs">
		<p>SUOJIAN 提供技术支持</p>
		<p>CopyRight©2014-<?php echo date('Y'); ?> SUOJIAN <?php echo L('common_application_ver'); ?> Inc.</p>
		<p><?php echo MyC('home_site_icp');?></p>
	</div>
</footer><?php endif; ?>
<!-- footer end -->

<!-- go-top start -->
<div data-am-smooth-scroll class="am-hide-sm-only">
	<a href="javascript:;" title="<?php echo L('common_go_top_text');?>" class="am-icon-btn am-icon-arrow-up" id="my-go-top" style="display:none;"></a>
</div>
<!-- go-top end -->

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
<script type="text/javascript" src="/Public/Common/Lib/ueditor/ueditor.all.js"></script>
<script type="text/javascript" src="/Public/Common/Lib/ueditor/lang/zh-cn/zh-cn.js"></script>

<!-- 颜色选择器 -->
<script type="text/javascript" src="/Public/Common/Lib/colorpicker/jquery.colorpicker.js"></script>

<!-- 项目公共 -->
<script type="text/javascript" src="/Public/Common/Js/Common.js"></script>
<script type="text/javascript" src="/Public/Home/<?php echo ($default_theme); ?>/Js/Common.js"></script>

<!-- 控制器 -->
<?php if(!empty($module_js)): ?><script type="text/javascript" src="/Public/<?php echo ($module_js); ?>"></script><?php endif; ?>
<?php echo MyC('home_footer_info');?>
<!-- footer end -->