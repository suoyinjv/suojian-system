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

<!-- header nav start -->
<header class="am-topbar am-topbar-inverse am-topbar-fixed-top">
	<div class="nav-header" <?php if(!empty($max_width_style)): ?>style="<?php echo ($max_width_style); ?>"<?php endif; ?>>
		<!-- logo start -->
		<h1 class="am-topbar-brand">
			<a href="<?php echo __MY_URL__;?>">
				<img src="<?php echo ($image_host); echo MyC('home_site_logo');?>" alt="<?php echo MyC('home_seo_site_title');?>" id="home_site_logo" />
			</a>
		</h1>
		<!-- logo end -->

		<!-- mobile nav button start -->
		<button class="am-topbar-btn am-topbar-toggle am-btn am-btn-sm am-btn-success am-show-sm-only" data-am-collapse="{target: '#doc-topbar-collapse'}"><span class="am-sr-only"><?php echo L('nav_switch_text');?></span> <span class="am-icon-bars"></span></button>
		<!-- mobile nav button end -->

		<div class="am-collapse am-topbar-collapse" id="doc-topbar-collapse">
			<!-- nav start -->
			<ul class="am-nav am-nav-pills am-topbar-nav">
				<li <?php if($nav_pid == 0 and $nav_id == 0): ?>class="am-active"<?php endif; ?>>
					<a href="<?php echo __MY_URL__;?>"><?php echo L('common_home_text');?></a>
				</li>
				<?php if(is_array($nav_header)): foreach($nav_header as $k=>$v): if(empty($v['item'])): ?><li class="<?php if($nav_pid == $v['id'] or $nav_id == $v['id']): ?>am-active<?php endif; ?>">
							<a href="<?php echo ($v["url"]); ?>" <?php if($v['is_new_window_open'] == 1): ?>target="_blank"<?php endif; ?>><?php echo ($v["name"]); ?></a>
						</li>
					<?php else: ?>
						<li class="am-dropdown <?php if($nav_pid == $v['id'] or $nav_id == $v['id']): ?>nav-active<?php endif; ?>" data-am-dropdown>
							<a class="am-dropdown-toggle" data-am-dropdown-toggle href="javascript:;">
								<?php echo ($v["name"]); ?>
								<span class="am-icon-caret-down"></span>
							</a>
							<ul class="am-dropdown-content">
								<?php if(is_array($v["item"])): foreach($v["item"] as $key=>$vs): ?><li <?php if($nav_id == $vs['id']): ?>class="am-active"<?php endif; ?>><a href="<?php echo ($vs["url"]); ?>" <?php if($vs['is_new_window_open'] == 1): ?>target="_blank"<?php endif; ?>><?php echo ($vs["name"]); ?></a></li><?php endforeach; endif; ?>
							</ul>
						</li><?php endif; endforeach; endif; ?>
			</ul>
			<!-- nav end -->

			<!-- search start -->
			<!-- <form class="am-topbar-form am-topbar-left am-form-inline am-hide-md-only" role="search" method="get" action="">
	<div class="am-input-group am-input-group-sm ml-search">
		<input type="text" name="q" value="" class="am-form-field am-radius" placeholder="<?php echo L('common_so_tips');?>" id="map_suggestId">
		<span class="am-input-group-btn">
			<button class="am-btn am-btn-default am-radius" type="submit"><span class="am-icon-search"></span> <?php echo L('common_so_text');?></button>
		</span>
	</div>
</form> -->
			<!-- search end -->

			<!-- user start -->
			<div class="am-topbar-right">
	<?php if(empty($user)): if(MyC('home_user_login_state') == 1): ?><a href="<?php echo U('Home/User/LoginInfo');?>" class="am-btn am-btn-secondary am-topbar-btn am-btn-sm am-radius"><span class="am-icon-user"></span> <?php echo L('common_login_text');?></a><?php endif; ?>
		<?php if(in_array('sms', MyC('home_user_reg_state'))): ?><a href="<?php echo U('Home/User/RegInfo');?>" class="am-btn am-btn-success am-topbar-btn am-btn-sm am-radius user-nav-reg-submit"><span class="am-icon-pencil"></span> <?php echo L('common_reg_text');?></a>
		<?php elseif(in_array('email', MyC('home_user_reg_state'))): ?>
			<a href="<?php echo U('Home/User/EmailRegInfo');?>" class="am-btn am-btn-success am-topbar-btn am-btn-sm am-radius user-nav-reg-submit"><span class="am-icon-pencil"></span> <?php echo L('common_reg_text');?></a><?php endif; ?>
	<?php else: ?>
		<div class="am-dropdown user-header-nav" data-am-dropdown="{boundary: '.am-topbar'}">
			<button class="am-btn am-btn-secondary am-topbar-btn am-btn-sm am-dropdown-toggle am-radius" data-am-dropdown-toggle><?php if(empty($user['nickname'])): echo L('common_user_home_text'); else: echo ($user["nickname"]); endif; ?> <span class="am-icon-caret-down"></span></button>
			<ul class="am-dropdown-content am-radius">
				<?php if(is_array($user_nav_menu)): foreach($user_nav_menu as $key=>$v): if($v['is_show'] == 1): ?><li <?php if((strtolower(CONTROLLER_NAME.ACTION_NAME) == strtolower($v['control'].$v['action'])) or (isset(L('user_left_menu_hidden_active')[strtolower(CONTROLLER_NAME.ACTION_NAME)]) and strtolower(L('user_left_menu_hidden_active')[strtolower(CONTROLLER_NAME.ACTION_NAME)]) == strtolower($v['control'].$v['action']))): ?>class="am-active"<?php endif; ?>>
							<a href="<?php echo U('Home/'.$v['control'].'/'.$v['action']);?>"><?php if(!empty($v['icon'])): ?><i class="<?php echo ($v["icon"]); ?>"></i><?php endif; ?> <?php echo ($v["name"]); ?></a>
						</li><?php endif; endforeach; endif; ?>
			</ul>
		</div><?php endif; ?>
</div>
			<!-- user end -->
		</div>
	</div>
</header>
<!-- header nav end -->

<!-- conntent start  -->
<div class="am-g my-content" <?php if(!empty($max_width_style)): ?>style="<?php echo ($max_width_style); ?>"<?php endif; ?>>
	<!-- layout module start -->
	<?php if(!empty($data)): if(is_array($data)): foreach($data as $key=>$v): switch($v["value"]): case "100": if(!empty($v['item'][0])): ?><div class="layout-<?php echo ($v["value"]); ?>"><?php echo ($v['item'][0]); ?></div><?php endif; break;?>
				<?php case "122": case "123": case "124": case "236": list($t1, $t2, $t3) = str_split($v['value']); ?>
					<ul data-am-widget="gallery" class="am-gallery am-avg-sm-<?php echo ($t1); ?> am-avg-md-<?php echo ($t2); ?> am-avg-lg-<?php echo ($t3); ?> am-gallery-overlay small-banner am-no-layout m-b-5 layout-100-<?php echo ($v["value"]); ?>" data-am-gallery="{}">
						<?php if(!empty($v['item'])): if(is_array($v["item"])): foreach($v["item"] as $key=>$vs): ?><li class="layout-item">
									<div class="layout-100-<?php echo ($v["value"]); ?>-item"><?php echo ($vs); ?></div>
								</li><?php endforeach; endif; endif; ?>
					</ul><?php break;?>
				<?php case "84": case "48": case "633": case "363": case "336": $t_all = str_split($v['value']); ?>
					<div class="layout-100-<?php echo ($v["value"]); ?> o-h">
						<?php if(!empty($v['item'])): if(is_array($v["item"])): foreach($v["item"] as $ks=>$vs): ?><div class="am-u-md-<?php echo ($t_all[$ks]); ?>"><?php echo ($vs); ?></div><?php endforeach; endif; endif; ?>
					</div><?php break; endswitch; endforeach; endif; endif; ?>
	<!-- layout module end -->
</div>
<!-- conntent end  -->

<!-- layuot common module start -->
<div class="site-oneself" <?php if(!empty($max_width_style)): ?>style="<?php echo ($max_width_style); ?>"<?php endif; ?>>
	<!-- link start -->
	<?php if(!empty($link['layout'])): ?><div class="layout-100">
			<div class="my-link o-h">
				<legend><?php echo L('common_view_link_name');?></legend>
				<?php if(!empty($link['data'])): ?><ul>
						<?php if(is_array($link["data"])): foreach($link["data"] as $key=>$v): ?><li><a href="<?php echo ($v["url"]); ?>" <?php if($v['is_new_window_open'] == 1): ?>target="_blank"<?php endif; ?> title="<?php echo ($v["describe"]); ?>"><?php echo ($v["name"]); ?></a></li><?php endforeach; endif; ?>
					</ul><?php endif; ?>
			</div>
		</div><?php endif; ?>
	<!-- link end -->
</div>
<!-- layuot common module end -->

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