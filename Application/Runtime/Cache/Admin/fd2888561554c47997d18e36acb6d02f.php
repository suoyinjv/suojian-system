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

<div class="content-right">
    <div class="content">
        <div class="am-g" style="margin-bottom:15px;">
            <div class="am-u-sm-6">
                <span class="fs-16 fw-700">📝 作业管理</span>
            </div>
            <div class="am-u-sm-6 tr">
                <a href="<?php echo U('Admin/Homework/add');?>" class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus"> 布置作业</a>
            </div>
        </div>
        <div class="am-panel am-panel-default">
            <div class="am-panel-bd">
                <table class="am-table am-table-striped am-table-hover am-text-middle">
                    <thead>
                        <tr>
                            <th>作业标题</th>
                            <th>课程</th>
                            <th>班级</th>
                            <th>截止时间</th>
                            <th>提交数</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($vo["title"]); ?></td>
                            <td><?php echo ($vo["course_name"]); ?></td>
                            <td><?php echo ($vo["class_name"]); ?></td>
                            <td><?php echo ($vo["submit_deadline"]); ?></td>
                            <td><?php echo ((isset($vo["submit_count"]) && ($vo["submit_count"] !== ""))?($vo["submit_count"]):0); ?></td>
                            <td><?php if($vo['status'] == 1): ?><span class="am-badge am-badge-success">已发布</span><?php else: ?><span class="am-badge">草稿</span><?php endif; ?></td>
                            <td>
                                <a href="<?php echo U('Admin/Homework/edit', array('id'=>$vo['id']));?>">编辑</a>
                                <a href="<?php echo U('Admin/Homework/submitList', array('homework_id'=>$vo['id']));?>">提交记录</a>
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        <?php if(count($list) == 0): ?><tr><td colspan="7" class="am-text-center cr-999">暂无作业数据</td></tr><?php endif; ?>
                    </tbody>
                </table>
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