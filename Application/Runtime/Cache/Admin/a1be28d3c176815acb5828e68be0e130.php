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
		<!-- table nav start -->
		
		<!-- table nav end -->

		<div class="am-panel am-panel-primary">
			<div class="am-panel-hd">
				<h3 class="am-panel-title"><i class="am-icon-university"></i> 校区管理</h3>
			</div>
			<div class="am-panel-bd">
				<div class="am-btn-toolbar">
					<a href="<?php echo U('Admin/Campus/add'); ?>" class="am-btn am-btn-primary am-radius"><i class="am-icon-plus"></i> 添加校区</a>
					<a href="<?php echo U('Admin/Campus/crossCampusStudent'); ?>" class="am-btn am-btn-success am-radius"><i class="am-icon-users"></i> 跨校学员</a>
					<a href="<?php echo U('Admin/Campus/crossCampusTeacher'); ?>" class="am-btn am-btn-warning am-radius"><i class="am-icon-user"></i> 跨校老师</a>
				</div>
				<table class="am-table am-table-bordered am-table-striped am-table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th>校区名称</th>
							<th>站点名称</th>
							<th>子域名</th>
							<th>主题色</th>
							<th>备案号</th>
							<th>到期时间</th>
							<th>编码</th>
							<th>地址</th>
							<th>电话</th>
							<th>负责人</th>
							<th>学员数</th>
							<th>老师数</th>
							<th>班级数</th>
							<th>状态</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($list)): foreach($list as $vo): ?>
						<tr>
							<td><?php echo $vo['id']; ?></td>
							<td><?php echo $vo['name']; ?></td>
							<td><?php echo $vo['site_name'] ?: '-'; ?></td>
							<td><?php echo $vo['domain'] ?: '-'; ?></td>
							<td><?php if($vo['theme_color']): ?><span style="display:inline-block;width:20px;height:20px;background:<?php echo $vo['theme_color']; ?>;border:1px solid #ccc;border-radius:3px;vertical-align:middle;"></span> <?php echo $vo['theme_color']; else: ?>-<?php endif; ?></td>
							<td><?php echo $vo['icp'] ?: '-'; ?></td>
							<td><?php echo $vo['expire_date']; ?></td>
							<td><?php echo $vo['code']; ?></td>
							<td><?php echo $vo['address']; ?></td>
							<td><?php echo $vo['phone']; ?></td>
							<td><?php echo $vo['principal']; ?></td>
							<td><span class="am-badge am-badge-primary"><?php echo $vo['student_count']; ?></span></td>
							<td><span class="am-badge am-badge-success"><?php echo $vo['teacher_count']; ?></span></td>
							<td><span class="am-badge am-badge-warning"><?php echo $vo['class_count']; ?></span></td>
							<td><?php echo $vo['status_text']; ?></td>
							<td>
								<a href="<?php echo U('Admin/Campus/overview', array('id'=>$vo['id'])); ?>" class="am-btn am-btn-xs am-btn-secondary am-radius">数据</a>
								<a href="<?php echo U('Admin/Campus/edit', array('id'=>$vo['id'])); ?>" class="am-btn am-btn-xs am-btn-primary am-radius">编辑</a>
								<a href="javascript:;" onclick="deleteConfirm('<?php echo U('Admin/Campus/delete', array('id'=>$vo['id'])); ?>')" class="am-btn am-btn-xs am-btn-danger am-radius">删除</a>
							</td>
						</tr>
						<?php endforeach; else: ?>
						<tr><td colspan="16" class="am-text-center">暂无校区数据</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
				<div class="am-cf">
					<div class="am-fr"><?php echo $page; ?></div>
				</div>
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

<script>
function deleteConfirm(url) {
	if(confirm('确定要删除吗？')) {
		location.href = url;
	}
}
</script>