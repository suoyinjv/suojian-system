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
		<legend>
			<span class="fs-16">招生统计</span>
			<a href="<?php echo U('Admin/Stats/Index');?>" class="fr fs-14 m-t-5 am-icon-mail-reply"> <?php echo L('common_operation_back');?></a>
		</legend>

		<!-- 统计卡片 -->
		<div class="am-g m-t-15">
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-primary am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl"><?php echo ($today_new); ?></p>
						<p class="am-text-default">今日新增</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-success am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl"><?php echo ($week_new); ?></p>
						<p class="am-text-default">本周新增</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-warning am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl"><?php echo ($month_new); ?></p>
						<p class="am-text-default">本月新增</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-secondary am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl"><?php echo ($total); ?></p>
						<p class="am-text-default">总学员</p>
					</div>
				</div>
			</div>
		</div>

		<!-- 趋势图 -->
		<div class="am-panel am-panel-default am-radius m-t-15">
			<div class="am-panel-hd">招生趋势</div>
			<div class="am-panel-bd">
				<div id="trendChart" style="width: 100%; height: 300px;"></div>
			</div>
		</div>

		<!-- 来源统计 -->
		<div class="am-panel am-panel-default am-radius m-t-15">
			<div class="am-panel-hd">来源渠道分布</div>
			<div class="am-panel-bd">
				<table class="am-table am-table-striped am-table-hover">
					<thead>
						<tr>
							<th>来源渠道</th>
							<th>人数</th>
							<th>占比</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($source_list)): if(is_array($source_list)): foreach($source_list as $key=>$v): ?><tr>
									<td><?php echo ($v["source"]); ?></td>
									<td><?php echo ($v["count"]); ?></td>
									<td><?php echo ($v["percent"]); ?>%</td>
								</tr><?php endforeach; endif; ?>
						<?php else: ?>
							<tr><td colspan="3" class="am-text-center"><?php echo L('common_not_data_tips');?></td></tr><?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
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

<script src="/Public/echarts.min.js"></script>
<script>
$(function(){
	var chart = echarts.init(document.getElementById('trendChart'));
	chart.setOption({
		tooltip: {trigger: 'axis'},
		legend: {data: ['新增学员']},
		grid: {left: '3%', right: '4%', bottom: '3%', containLabel: true},
		xAxis: {type: 'category', data: <?php echo ($date_list); ?>, boundaryGap: false},
		yAxis: {type: 'value'},
		series: [{name: '新增学员', type: 'line', smooth: true, data: <?php echo ($data_list); ?>}]
	});
});
</script>