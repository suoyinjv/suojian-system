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
		<!-- form start -->
		<form class="am-form view-list" action="<?php echo U('Admin/Leave/Index');?>" method="POST">
			<div class="am-g">
				<input type="text" class="am-radius form-keyword" placeholder="请输入学员姓名搜索" name="keyword" <?php if(isset($param['keyword'])): ?>value="<?php echo ($param["keyword"]); ?>"<?php endif; ?> />
				<button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit">查询</button>
				<label class="fs-12 m-l-5 c-p fw-100 more-submit">
					更多筛选
					<input type="checkbox" name="is_more" value="1" id="is_more" <?php if(isset($param['is_more']) and $param['is_more'] == 1): ?>checked<?php endif; ?> />
					<i class="am-icon-angle-down"></i>
				</label>

				<div class="more-where <?php if(!isset($param['is_more']) or $param['is_more'] != 1): ?>none<?php endif; ?>">
					<select class="am-radius c-p m-t-10 m-l-5 param-where" name="type">
						<option value="">请假类型</option>
						<option value="1" <?php if(isset($param['type']) and $param['type'] == 1): ?>selected<?php endif; ?>>事假</option>
						<option value="2" <?php if(isset($param['type']) and $param['type'] == 2): ?>selected<?php endif; ?>>病假</option>
						<option value="3" <?php if(isset($param['type']) and $param['type'] == 3): ?>selected<?php endif; ?>>其他</option>
					</select>
					<select name="status" class="am-radius c-p m-t-10 m-l-5 param-where">
						<option value="-1">审批状态</option>
						<option value="0" <?php if(isset($param['status']) and $param['status'] == 0): ?>selected<?php endif; ?>>待审批</option>
						<option value="1" <?php if(isset($param['status']) and $param['status'] == 1): ?>selected<?php endif; ?>>已批准</option>
						<option value="2" <?php if(isset($param['status']) and $param['status'] == 2): ?>selected<?php endif; ?>>已拒绝</option>
						<option value="3" <?php if(isset($param['status']) and $param['status'] == 3): ?>selected<?php endif; ?>>已取消</option>
					</select>
					<div class="param-date param-where m-l-5">
						<input type="text" name="time_start" readonly="readonly" class="am-radius m-t-10" placeholder="开始日期" id="time_start" <?php if(isset($param['time_start'])): ?>value="<?php echo ($param["time_start"]); ?>"<?php endif; ?>/>
						<span>~</span>
						<input type="text" readonly="readonly" class="am-radius m-t-10" placeholder="结束日期" name="time_end" id="time_end" <?php if(isset($param['time_end'])): ?>value="<?php echo ($param["time_end"]); ?>"<?php endif; ?>/>
					</div>
				</div>
			</div>
        </form>
        <!-- form end -->

        <!-- operation start -->
        <div class="am-g m-t-15">
            <a href="<?php echo U('Admin/Leave/SaveInfo');?>" class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus"> 新增请假</a>
        </div>
        <!-- operation end -->

		<!-- list start -->
		<table class="am-table am-table-striped am-table-hover am-text-middle m-t-10 m-l-5">
			<thead>
				<tr>
					<th>学员姓名</th>
					<th class="am-hide-sm-only">请假类型</th>
					<th class="am-hide-sm-only">开始日期</th>
					<th class="am-hide-sm-only">结束日期</th>
					<th class="am-hide-sm-only">审批状态</th>
					<th>更多</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($list)): if(is_array($list)): foreach($list as $key=>$v): ?><tr id="data-list-<?php echo ($v["id"]); ?>">
							<td><?php echo ($v["student_name"]); ?></td>
							<td class="am-hide-sm-only">
								<?php if($v['type'] == 1): ?>事假
								<?php elseif($v['type'] == 2): ?>病假
								<?php else: ?>其他<?php endif; ?>
							</td>
							<td class="am-hide-sm-only"><?php echo ($v["start_date"]); ?></td>
							<td class="am-hide-sm-only"><?php echo ($v["end_date"]); ?></td>
							<td class="am-hide-sm-only">
								<?php if($v['status'] == 0): ?><span class="am-badge">待审批</span>
								<?php elseif($v['status'] == 1): ?><span class="am-badge am-badge-success">已批准</span>
								<?php elseif($v['status'] == 2): ?><span class="am-badge am-badge-danger">已拒绝</span>
								<?php else: ?><span class="am-badge am-badge-default">已取消</span><?php endif; ?>
							</td>
							<td>
								<span class="am-icon-caret-down c-p" data-am-modal="{target: '#my-popup<?php echo ($v["id"]); ?>'}"> 查看详情</span>
								<div class="am-popup am-radius" id="my-popup<?php echo ($v["id"]); ?>">
									<div class="am-popup-inner">
										<div class="am-popup-hd">
											<h4 class="am-popup-title">请假详情</h4>
											<span data-am-modal-close class="am-close">&times;</span>
										</div>
										<div class="am-popup-bd">
											<dl class="dl-content">
												<dt>学员姓名</dt>
												<dd><?php echo ($v["student_name"]); ?></dd>

												<dt>请假类型</dt>
												<dd>
													<?php if($v['type'] == 1): ?>事假
													<?php elseif($v['type'] == 2): ?>病假
													<?php else: ?>其他<?php endif; ?>
												</dd>

												<dt>开始日期</dt>
												<dd><?php echo ($v["start_date"]); ?></dd>

												<dt>结束日期</dt>
												<dd><?php echo ($v["end_date"]); ?></dd>

												<dt>请假原因</dt>
												<dd><?php echo ($v["reason"]); ?></dd>

												<dt>审批状态</dt>
												<dd>
													<?php if($v['status'] == 0): ?><span class="am-badge">待审批</span>
													<?php elseif($v['status'] == 1): ?><span class="am-badge am-badge-success">已批准</span>
													<?php elseif($v['status'] == 2): ?><span class="am-badge am-badge-danger">已拒绝</span>
													<?php else: ?><span class="am-badge am-badge-default">已取消</span><?php endif; ?>
												</dd>

												<dt>审批时间</dt>
												<dd><?php echo ($v["approve_time"]); ?></dd>

												<dt>审批备注</dt>
												<dd><?php echo ($v["approve_remark"]); ?></dd>

												<dt>创建时间</dt>
												<dd><?php echo ($v["add_time"]); ?></dd>

												<dt>更新时间</dt>
												<dd><?php echo ($v["upd_time"]); ?></dd>
											</dl>
										</div>
									</div>
								</div>
							</td>
							<td class="view-operation">
								<a href="<?php echo U('Admin/Leave/SaveInfo', array('id'=>$v['id']));?>">
									<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-edit" data-am-popover="{content: '编辑/审批', trigger: 'hover focus'}"></button>
								</a>
								<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-trash-o submit-delete" data-url="<?php echo U('Admin/Leave/Delete');?>" data-am-popover="{content: '删除', trigger: 'hover focus'}" data-id="<?php echo ($v["id"]); ?>"></button>
							</td>
						</tr><?php endforeach; endif; ?>
				<?php else: ?>
					<tr><td colspan="7" class="table-no">暂无数据</td></tr><?php endif; ?>
			</tbody>
		</table>
		<!-- list end -->

		<!-- page start -->
		<?php if(!empty($list)): echo ($page_html); endif; ?>
		<!-- page end -->
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