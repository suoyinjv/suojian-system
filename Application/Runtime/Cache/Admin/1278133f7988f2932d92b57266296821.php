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
		<div class="am-btn-toolbar am-margin-bottom">
			<a href="<?php echo U('Admin/Campus/index'); ?>" class="am-btn am-btn-default am-radius">
				<i class="am-icon-arrow-left"></i> 返回校区管理
			</a>
			<a href="<?php echo U('Admin/Campus/crossCampusTeacher'); ?>" class="am-btn am-btn-warning am-radius">
				<i class="am-icon-user"></i> 跨校老师查询
			</a>
		</div>

		<div class="am-panel am-panel-success">
			<div class="am-panel-hd">
				<h3 class="am-panel-title"><i class="fa fa-search"></i> 跨校区学员查询</h3>
			</div>
			<div class="am-panel-bd">
				<form class="am-form am-form-inline" action="" method="GET">
					<div class="am-form-group">
						<input type="text" name="keyword" class="am-form-field" placeholder="姓名/手机号" value="<?php echo I('keyword'); ?>">
					</div>
					<button type="submit" class="am-btn am-btn-primary"><i class="am-icon-search"></i> 搜索</button>
					<a href="<?php echo U('Admin/Campus/crossCampusStudent'); ?>" class="am-btn am-btn-default">清空</a>
				</form>
			</div>
		</div>

		<table class="am-table am-table-bordered am-table-striped am-table-hover">
			<thead>
				<tr>
					<th>ID</th>
					<th>姓名</th>
					<th>手机号</th>
					<th>性别</th>
					<th>所属校区</th>
					<th>剩余课时</th>
					<th>添加时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($list)): foreach($list as $vo): ?>
				<tr>
					<td><?php echo $vo['id']; ?></td>
					<td><?php echo $vo['username']; ?></td>
					<td><?php echo $vo['my_mobile']; ?></td>
					<td><?php if($vo['sex'] == 1): ?>男<?php else: ?>女<?php endif; ?></td>
					<td><span class="am-badge am-badge-primary"><?php echo $vo['campus_name']; ?></span></td>
					<td><span class="am-badge am-badge-success"><?php echo $vo['balance_hours']; ?></span></td>
					<td><?php echo $vo['add_time'] ? date('Y-m-d', $vo['add_time']) : '-'; ?></td>
					<td>
						<a href="<?php echo U('Student/view', array('id'=>$vo['id'])); ?>">详情</a>
						<a href="<?php echo U('Student/edit', array('id'=>$vo['id'])); ?>">编辑</a>
					</td>
				</tr>
				<?php endforeach; else: ?>
				<tr>
					<td colspan="8" class="am-text-center">暂无数据</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
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