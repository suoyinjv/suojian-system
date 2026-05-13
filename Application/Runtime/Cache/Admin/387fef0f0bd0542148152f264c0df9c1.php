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

<div class="content-right">
    <div class="content">
        <legend><span class="fs-16 fw-700">💰 财务数据</span></legend>

        <div class="am-g am-margin-top">
            <div class="am-u-sm-12 am-u-md-6">
                <div class="am-panel am-panel-default">
                    <div class="am-panel-hd">📈 每日收入（近30天）</div>
                    <div class="am-panel-bd">
                        <table class="am-table am-table-bordered am-text-center">
                            <thead><tr><th>日期</th><th>订单数</th><th>金额</th></tr></thead>
                            <tbody>
                                <?php if(is_array($daily_revenue)): $i = 0; $__LIST__ = $daily_revenue;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr><td><?php echo ($vo["date"]); ?></td><td><?php echo ($vo["cnt"]); ?></td><td class="am-text-success">¥<?php echo (number_format($vo["amount"],2)); ?></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                <?php if(count($daily_revenue) == 0): ?><tr><td colspan="3" class="am-text-center cr-999">暂无数据</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="am-u-sm-12 am-u-md-6">
                <div class="am-panel am-panel-default">
                    <div class="am-panel-hd">💳 支付方式统计</div>
                    <div class="am-panel-bd">
                        <table class="am-table am-table-bordered">
                            <thead><tr><th>支付方式</th><th>订单数</th><th>金额</th></tr></thead>
                            <tbody>
                                <?php if(is_array($pay_type_stats)): $i = 0; $__LIST__ = $pay_type_stats;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr><td>
                                    <?php if($vo['pay_type'] == 1): ?>微信
                                    <?php elseif($vo['pay_type'] == 2): ?>支付宝
                                    <?php elseif($vo['pay_type'] == 3): ?>银行卡
                                    <?php elseif($vo['pay_type'] == 4): ?>现金
                                    <?php else: ?>其他<?php endif; ?>
                                </td><td><?php echo ($vo["cnt"]); ?></td><td class="am-text-success">¥<?php echo (number_format($vo["amount"],2)); ?></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                <?php if(count($pay_type_stats) == 0): ?><tr><td colspan="3" class="am-text-center cr-999">暂无数据</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="am-g">
            <div class="am-u-sm-12">
                <div class="am-panel am-panel-default">
                    <div class="am-panel-hd">🏅 课程收入排名（Top 5）</div>
                    <div class="am-panel-bd">
                        <table class="am-table am-table-bordered">
                            <thead><tr><th>课程</th><th>收入</th></tr></thead>
                            <tbody>
                                <?php if(is_array($course_revenue)): $i = 0; $__LIST__ = $course_revenue;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr><td><?php echo ((isset($vo["course_name"]) && ($vo["course_name"] !== ""))?($vo["course_name"]):'未知'); ?></td><td class="am-text-success">¥<?php echo (number_format($vo["amount"],2)); ?></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
                                <?php if(count($course_revenue) == 0): ?><tr><td colspan="2" class="am-text-center cr-999">暂无数据</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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