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

<div class="admin-content am-padding">
    <div class="am-cf am-margin-bottom">
        <strong class="am-text-primary am-text-lg">经营概览</strong>
        <span class="am-fr">更新时间：<?php echo date('Y-m-d H:i');?></span>
    </div>

    <!-- 今日数据 -->
    <div class="am-g">
        <div class="am-u-sm-12 am-u-md-6 am-u-lg-2">
            <div class="admin-stat am-panel am-panel-default am-radius">
                <div class="am-panel-bd am-text-center">
                    <h3 class="am-text-xl am-text-warning"><?php echo ($today_attendance); ?></h3>
                    <p class="am-text-muted">今日考勤</p>
                </div>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-6 am-u-lg-2">
            <div class="admin-stat am-panel am-panel-default am-radius">
                <div class="am-panel-bd am-text-center">
                    <h3 class="am-text-xl am-text-danger"><?php echo ($today_consumption); ?></h3>
                    <p class="am-text-muted">今日课消(时)</p>
                </div>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-6 am-u-lg-2">
            <div class="admin-stat am-panel am-panel-default am-radius">
                <div class="am-panel-bd am-text-center">
                    <h3 class="am-text-xl am-text-success"><?php echo ($today_new_students); ?></h3>
                    <p class="am-text-muted">今日新增学员</p>
                </div>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-6 am-u-lg-2">
            <div class="admin-stat am-panel am-panel-default am-radius">
                <div class="am-panel-bd am-text-center">
                    <h3 class="am-text-xl am-text-primary"><?php echo ($today_orders); ?></h3>
                    <p class="am-text-muted">今日订单</p>
                </div>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-6 am-u-lg-2">
            <div class="admin-stat am-panel am-panel-default am-radius">
                <div class="am-panel-bd am-text-center">
                    <h3 class="am-text-xl am-text-secondary">¥<?php echo (number_format($today_revenue,2)); ?></h3>
                    <p class="am-text-muted">今日收入</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 本月数据 -->
    <div class="am-g am-margin-top">
        <div class="am-u-sm-12">
            <div class="am-panel am-panel-default">
                <div class="am-panel-hd"><strong>本月数据</strong></div>
                <div class="am-panel-bd">
                    <table class="am-table am-table-bordered am-text-center">
                        <thead>
                            <tr>
                                <th>本月收入</th>
                                <th>本月订单</th>
                                <th>本月新增学员</th>
                                <th>本月考勤</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="am-text-success am-text-lg">¥<?php echo (number_format($month_revenue,2)); ?></td>
                                <td class="am-text-primary am-text-lg"><?php echo ($month_orders); ?></td>
                                <td class="am-text-warning am-text-lg"><?php echo ($month_new); ?></td>
                                <td class="am-text-danger am-text-lg"><?php echo ($month_attendance); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 累计数据 -->
    <div class="am-g am-margin-top">
        <div class="am-u-sm-12">
            <div class="am-panel am-panel-default">
                <div class="am-panel-hd"><strong>累计数据</strong></div>
                <div class="am-panel-bd">
                    <table class="am-table am-table-bordered am-text-center">
                        <thead>
                            <tr>
                                <th>总学员</th>
                                <th>在读学员</th>
                                <th>教师总数</th>
                                <th>剩余课时</th>
                                <th>总订单</th>
                                <th>总收入</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="am-text-lg"><?php echo ($total_students); ?></td>
                                <td class="am-text-lg"><?php echo ($active_students); ?></td>
                                <td class="am-text-lg"><?php echo ($total_teachers); ?></td>
                                <td class="am-text-lg"><?php echo ($total_remaining); ?></td>
                                <td class="am-text-lg"><?php echo ($total_orders); ?></td>
                                <td class="am-text-success am-text-lg">¥<?php echo (number_format($total_revenue,2)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 待处理事项 -->
    <div class="am-g am-margin-top">
        <div class="am-u-sm-12 am-u-md-6">
            <div class="am-panel am-panel-warning">
                <div class="am-panel-hd"><strong>待处理事项</strong></div>
                <div class="am-panel-bd">
                    <ul class="am-list am-list-static">
                        <li>即将过期课程：<span class="am-badge am-badge-danger"><?php echo ($expire_courses); ?></span> 个</li>
                        <li>待跟进线索：<span class="am-badge am-badge-warning"><?php echo ($pending_leads); ?></span> 条</li>
                    </ul>
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