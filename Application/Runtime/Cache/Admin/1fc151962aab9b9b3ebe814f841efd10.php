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
			<span class="fs-16">消费统计</span>
			<a href="<?php echo U('Admin/Stats/Index');?>" class="fr fs-14 m-t-5 am-icon-mail-reply"> <?php echo L('common_operation_back');?></a>
		</legend>

		<!-- 统计卡片 -->
		<div class="am-g m-t-15">
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-primary am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl"><?php echo ($today_count); ?></p>
						<p class="am-text-default">今日订单</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-success am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl">¥<?php echo ($today_amount); ?></p>
						<p class="am-text-default">今日消费</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-warning am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl">¥<?php echo ($month_amount); ?></p>
						<p class="am-text-default">本月消费</p>
					</div>
				</div>
			</div>
			<div class="am-u-sm-3">
				<div class="am-panel am-panel-secondary am-radius">
					<div class="am-panel-bd am-text-center">
						<p class="am-text-xxl">¥<?php echo ($total_amount); ?></p>
						<p class="am-text-default">累计消费</p>
					</div>
				</div>
			</div>
		</div>

		<!-- 趋势图 -->
		<div class="am-panel am-panel-default am-radius m-t-15">
			<div class="am-panel-hd">消费趋势</div>
			<div class="am-panel-bd">
				<div id="consumptionChart" style="width: 100%; height: 300px;"></div>
			</div>
		</div>

		<!-- 消费排行 -->
		<div class="am-panel am-panel-default am-radius m-t-15">
			<div class="am-panel-hd">消费排行 TOP10</div>
			<div class="am-panel-bd">
				<table class="am-table am-table-striped am-table-hover">
					<thead>
						<tr>
							<th>排名</th>
							<th>学员姓名</th>
							<th>手机号</th>
							<th>消费次数</th>
							<th>消费金额</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($top_list)): if(is_array($top_list)): foreach($top_list as $k=>$v): ?><tr>
									<td>
										<?php if($k == 0): ?><span class="am-badge am-badge-warning">1</span>
										<?php elseif($k == 1): ?><span class="am-badge am-badge-secondary">2</span>
										<?php elseif($k == 2): ?><span class="am-badge am-badge-primary">3</span>
										<?php else: echo ($k+1); endif; ?>
									</td>
									<td><?php echo ($v["student_name"]); ?></td>
									<td><?php echo ($v["mobile"]); ?></td>
									<td><?php echo ($v["order_count"]); ?></td>
									<td class="am-text-primary">¥<?php echo ($v["total_amount"]); ?></td>
								</tr><?php endforeach; endif; ?>
						<?php else: ?>
							<tr><td colspan="5" class="am-text-center"><?php echo L('common_not_data_tips');?></td></tr><?php endif; ?>
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
	var chart = echarts.init(document.getElementById('consumptionChart'));
	chart.setOption({
		tooltip: {trigger: 'axis'},
		legend: {data: ['消费金额', '订单数']},
		grid: {left: '3%', right: '4%', bottom: '3%', containLabel: true},
		xAxis: {type: 'category', data: <?php echo ($date_list); ?>, boundaryGap: false},
		yAxis: [{type: 'value'}, {type: 'value'}],
		series: [
			{name: '消费金额', type: 'line', smooth: true, data: <?php echo ($amount_list); ?>},
			{name: '订单数', type: 'line', smooth: true, yAxisIndex: 1, data: <?php echo ($count_list); ?>}
		]
	});
});
</script>