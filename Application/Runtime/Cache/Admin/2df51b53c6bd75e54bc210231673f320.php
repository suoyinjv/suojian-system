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

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<!-- 页面标题 -->
		<div class="am-g" style="margin-bottom:15px;">
			<div class="am-u-sm-6">
				<span class="fs-16 fw-700">🎬 回放管理</span>
			</div>
			<div class="am-u-sm-6 tr">
				<a href="{:U('Admin/Live/replayAdd')}" class="am-btn am-btn-primary am-radius am-btn-sm">
					<i class="am-icon-plus"></i> 新增回放
				</a>
			</div>
		</div>

		<!-- 筛选 -->
		<form class="am-form am-form-inline" method="get" style="margin-bottom:15px;">
			<div class="am-form-group">
				<select name="course_id" class="am-radius" onchange="this.form.submit()">
					<option value="0">全部课程</option>
					<?php if(is_array($course_list)): foreach($course_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if($course_id == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["title"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
		</form>

		<!-- 回放列表 -->
		<div class="am-panel am-panel-default">
			<div class="am-panel-bd">
				<table class="am-table am-table-bordered am-table-striped">
					<thead>
						<tr>
							<th>回放标题</th>
							<th>关联课程</th>
							<th>时长</th>
							<th>播放次数</th>
							<th>Session</th>
							<th>创建时间</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
							<td>{$vo.title}</td>
							<td>{$vo.course_title}</td>
							<td>{$vo.duration_text}</td>
							<td>{$vo.views|default=0}</td>
							<td><code>{$vo.session_id|default='-'}</code></td>
							<td>{$vo.create_time}</td>
							<td>
								<?php if($vo['url']): ?><a href="{$vo.url}" target="_blank" class="am-btn am-btn-success am-btn-xs am-radius"><i class="am-icon-play"></i> 播放</a><?php endif; ?>
								<a href="javascript:;" onclick="delReplay({$vo.id})" class="am-btn am-btn-danger am-btn-xs am-radius">删除</a>
							</td>
						</tr><?php endforeach; endif; else: echo "" ;endif; ?>
						<?php if(count($list) == 0): ?><tr><td colspan="7" class="am-text-center cr-999">暂无回放记录</td></tr><?php endif; ?>
					</tbody>
				</table>
				<div class="am-pagination"><?php echo ($page); ?></div>
			</div>
		</div>
	</div>
</div>
<!-- right content end -->

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
function delReplay(id) {
    if (!confirm('确定删除此回放记录？')) return;
    $.ajax({
        url: '{:U("Admin/Live/replayDelete")}',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(res) {
            if (res.code == 1) {
                location.reload();
            } else {
                alert(res.msg);
            }
        }
    });
}
</script>