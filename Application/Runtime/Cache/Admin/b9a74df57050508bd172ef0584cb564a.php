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
		<form class="am-form view-list" action="<?php echo U('Admin/Student/Index');?>" method="POST">
			<div class="am-g">
				<input type="text" class="am-radius form-keyword" placeholder="<?php echo L('student_so_keyword_tips');?>" name="keyword" <?php if(isset($param['keyword'])): ?>value="<?php echo ($param["keyword"]); ?>"<?php endif; ?> />
				<button type="submit" class="am-btn am-btn-secondary am-btn-sm am-radius form-submit"><?php echo L('common_operation_query');?></button>
				<label class="fs-12 m-l-5 c-p fw-100 more-submit">
					<?php echo L('common_more_screening');?>
					<input type="checkbox" name="is_more" value="1" id="is_more" <?php if(isset($param['is_more']) and $param['is_more'] == 1): ?>checked<?php endif; ?> />
					<i class="am-icon-angle-down"></i>
				</label>

				<div class="more-where <?php if(!isset($param['is_more']) or $param['is_more'] != 1): ?>none<?php endif; ?>">
					<select  class="am-radius c-p m-t-10 m-l-5 param-where" name="region_id">
						<option value="0"><?php echo L('student_region_text');?></option>
						<?php if(is_array($region_list)): foreach($region_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($param['region_id']) and $param['region_id'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
					</select>
					<select name="class_id" class="am-radius c-p m-t-10 m-l-5 param-where">
						<option value="0"><?php echo L('student_class_text');?></option>
						<?php if(is_array($class_list)): foreach($class_list as $key=>$v): if(empty($v['item'])): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($param['class_id']) and $param['class_id'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option>
							<?php else: ?>
								<optgroup label="<?php echo ($v["name"]); ?>">
									<?php if(is_array($v["item"])): foreach($v["item"] as $key=>$vs): ?><option value="<?php echo ($vs["id"]); ?>" <?php if(isset($param['class_id']) and $param['class_id'] == $vs['id']): ?>selected<?php endif; ?>><?php echo ($vs["name"]); ?></option><?php endforeach; endif; ?>
								</optgroup><?php endif; endforeach; endif; ?>
					</select>
					<select name="state" class="am-radius c-p m-t-10 m-l-5 param-where">
						<option value="-1"><?php echo L('common_view_student_state_name');?></option>
						<?php if(is_array($common_student_state_list)): foreach($common_student_state_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($param['state']) and $param['state'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
					</select>
					<select name="tuition_state" class="am-radius c-p m-t-10 m-l-5 param-where">
						<option value="-1"><?php echo L('student_tuition_state_text');?></option>
						<?php if(is_array($common_tuition_state_list)): foreach($common_tuition_state_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($param['tuition_state']) and $param['tuition_state'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
					</select>
					<select name="gender" class="am-radius c-p m-t-10 m-l-5 param-where">
						<option value="-1"><?php echo L('common_view_gender_name');?></option>
						<?php if(is_array($common_gender_list)): foreach($common_gender_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if(isset($param['gender']) and $param['gender'] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["name"]); ?></option><?php endforeach; endif; ?>
					</select>
					<div class="param-date param-where m-l-5">
						<input type="text" name="time_start" readonly="readonly" class="am-radius m-t-10" placeholder="<?php echo L('student_time_start_text');?>" id="time_start" <?php if(isset($param['time_start'])): ?>value="<?php echo ($param["time_start"]); ?>"<?php endif; ?>/>
						<span>~</span>
						<input type="text" readonly="readonly" class="am-radius m-t-10" placeholder="<?php echo L('student_time_end_text');?>" name="time_end" id="time_end" <?php if(isset($param['time_end'])): ?>value="<?php echo ($param["time_end"]); ?>"<?php endif; ?>/>
					</div>
				</div>
			</div>
        </form>
        <!-- form end -->

        <!-- operation start -->
        <div class="am-g m-t-15">
            <a href="<?php echo U('Admin/Student/SaveInfo');?>" class="am-btn am-btn-secondary am-radius am-btn-xs am-icon-plus"> <?php echo L('common_operation_add');?></a>
            <?php if(!IsMobile()): ?><a href="<?php echo ($excel_url); ?>" class="am-btn am-btn-success am-btn-xs m-l-10 am-icon-file-excel-o am-radius"> <?php echo L('common_operation_excel_export_name');?></a>
            	<a href="javascript:;" class="am-btn am-btn-primary am-btn-xs m-l-10 am-icon-cloud-upload am-radius" data-am-modal="{target: '#excel-import-win'}"> <?php echo L('common_operation_excel_import_name');?></a>
            	<!-- excel win html start -->
				<div class="am-popup am-radius" id="excel-import-win">
	<div class="am-popup-inner">
		<div class="am-popup-hd">
			<h4 class="am-popup-title"><?php echo L('common_operation_excel_import_name');?></h4>
			<span data-am-modal-close class="am-close">&times;</span>
		</div>
		<div class="am-popup-bd">
			<!-- win form start -->
			<form class="am-form form-validation excel-form" action="<?php echo ($excel_import_form_url); ?>" method="POST" request-type="ajax-fun" request-value="ExcelImportCallback" enctype="multipart/form-data">
				<input type="hidden" name="max_file_size" value="<?php echo MyC('home_max_limit_file', 51200000);?>" />
				<div class="am-alert am-radius am-alert-tips m-t-0" data-am-alert>
					<?php if(!empty($excel_import_format_url)): ?><p class="m-b-0"><a href="<?php echo ($excel_import_format_url); ?>" class="cr-blue"><?php echo L('common_excel_format_download_name');?></a><span class="m-r-5"></p><?php endif; ?>
					<?php if(!empty($excel_import_tips)): ?><p class="m-t-10"><?php echo ($excel_import_tips); ?></p><?php endif; ?>
					<p class="cr-red"><?php echo L('common_excel_import_tips');?></p>
				</div>
				<div class="am-form-group am-form-file">
					<button type="button" class="am-btn am-btn-default am-btn-sm am-radius"><i class="am-icon-cloud-upload"></i> <?php echo L('common_select_file_text');?></button>
					<input type="file" name="excel" multiple data-validation-message="<?php echo L('common_select_file_tips');?>" accept="application/vnd.ms-excel" required />
				</div>
				<div class="am-form-group">
					<button type="submit" class="am-btn am-btn-primary am-radius btn-loading-example am-btn-sm w100" data-am-loading="{loadingText:'<?php echo L('common_form_loading_tips');?>'}"><?php echo L('common_operation_confirm');?></button>
				</div>
			</form>
			<!-- win form end -->

			<!-- import tips start -->
			<div class="am-alert am-alert-success am-radius excel-import-success none"><?php echo L('common_import_success_name');?> <strong>0</strong> <?php echo L('common_unit_tiao_name');?></div>
			<div class="am-panel am-panel-danger am-radius excel-import-error none">
				<div class="am-panel-hd p-l-10"><?php echo L('common_import_error_name');?> <strong>0</strong>  <?php echo L('common_unit_tiao_name');?></div>
				<table class="am-table"><tbody></tbody></table>
			</div>
			<!-- import tips end -->
		</div>
	</div>
</div>
				<!-- excel win html end --><?php endif; ?>
        </div>
        <!-- operation end -->

		<!-- list start -->
		<table class="am-table am-table-striped am-table-hover am-text-middle m-t-10 m-l-5">
			<thead>
				<tr>
					<th><?php echo L('student_username_name');?></th>
					<th class="am-hide-sm-only"><?php echo L('common_view_student_number_text');?></th>
					<th><?php echo L('common_view_gender_name');?></th>
					<th class="am-hide-sm-only"><?php echo L('student_class_text');?></th>
					<th class="am-hide-sm-only"><?php echo L('common_view_student_state_name');?></th>
					<th><?php echo L('common_more_name');?></th>
					<th><?php echo L('common_operation_name');?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($list)): if(is_array($list)): foreach($list as $key=>$v): ?><tr id="data-list-<?php echo ($v["id"]); ?>-<?php echo ($v["id_card"]); ?>">
							<td><?php echo ($v["username"]); ?></td>
							<td class="am-hide-sm-only"><?php echo ($v["number"]); ?></td>
							<td><?php echo ($v["gender"]); ?></td>
							<td class="am-hide-sm-only"><?php echo ($v["class_name"]); ?></td>
							<td class="am-hide-sm-only"><?php echo ($v["state"]); ?></td>
							<td>
								<span class="am-icon-caret-down c-p" data-am-modal="{target: '#my-popup<?php echo ($v["id"]); ?>'}"> <?php echo L('common_see_more_name');?></span>
								<div class="am-popup am-radius" id="my-popup<?php echo ($v["id"]); ?>">
									<div class="am-popup-inner">
										<div class="am-popup-hd">
											<h4 class="am-popup-title"><?php echo L('common_detail_content');?></h4>
											<span data-am-modal-close class="am-close">&times;</span>
										</div>
										<div class="am-popup-bd">
											<dl class="dl-content">
												<dt><?php echo L('student_username_name');?></dt>
												<dd><?php echo ($v["username"]); ?></dd>

												<dt><?php echo L('common_view_student_number_text');?></dt>
												<dd><?php echo ($v["number"]); ?></dd>

												<dt><?php echo L('common_view_id_card_text');?></dt>
												<dd><?php echo ($v["id_card"]); ?></dd>

												<dt><?php echo L('common_address_text');?></dt>
												<dd><?php echo ($v["region_name"]); ?> <?php echo ($v["address"]); ?></dd>

												<dt><?php echo L('common_view_gender_name');?></dt>
												<dd><?php echo ($v["gender"]); ?></dd>

												<dt><?php echo L('student_birthday_text');?></dt>
												<dd><?php echo ($v["birthday"]); ?></dd>

												<dt><?php echo L('common_view_tel_name');?></dt>
												<dd><?php echo ($v["tel"]); ?></dd>

												<dt><?php echo L('student_my_mobile_name');?></dt>
												<dd><?php echo ($v["my_mobile"]); ?></dd>

												<dt><?php echo L('common_view_parent_mobile_name');?></dt>
												<dd><?php echo ($v["parent_mobile"]); ?></dd>

												<dt><?php echo L('common_email_name');?></dt>
												<dd><?php echo ($v["email"]); ?></dd>

												<dt><?php echo L('student_class_text');?></dt>
												<dd><?php echo ($v["class_name"]); ?></dd>

												<dt><?php echo L('student_tuition_state_text');?></dt>
												<dd><?php echo ($v["tuition_state"]); ?></dd>

												<dt><?php echo L('common_view_student_state_name');?></dt>
												<dd><?php echo ($v["state"]); ?></dd>

												<dt><?php echo L('student_sign_up_name');?></dt>
												<dd><?php echo ($v["add_time"]); ?></dd>

												<dt><?php echo L('common_upd_time_name');?></dt>
												<dd><?php echo ($v["upd_time"]); ?></dd>
											</dl>
										</div>
									</div>
								</div>
							</td>
							<td class="view-operation">
								<a href="<?php echo U('Admin/Fraction/SaveInfo', array('id'=>$v['id']));?>">
									<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-line-chart" data-am-popover="{content: '<?php echo L('common_operation_fraction');?>', trigger: 'hover focus'}"></button>
								</a>
								<a href="<?php echo U('Admin/Student/SaveInfo', array('id'=>$v['id']));?>">
									<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-edit" data-am-popover="{content: '<?php echo L('common_operation_edit');?>', trigger: 'hover focus'}"></button>
								</a>
								<button class="am-btn am-btn-default am-btn-xs am-radius am-icon-trash-o submit-delete" data-url="<?php echo U('Admin/Student/Delete');?>" data-am-popover="{content: '<?php echo L('common_operation_delete');?>', trigger: 'hover focus'}" data-id="<?php echo ($v["id"]); ?>-<?php echo ($v["id_card"]); ?>"></button>
							</td>
						</tr><?php endforeach; endif; ?>
				<?php else: ?>
					<tr><td colspan="10" class="table-no"><?php echo L('common_not_data_tips');?></td></tr><?php endif; ?>
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

<?php if(!IsMobile()): ?><!-- excel win js start -->
	<script>
/**
 * [ExcelImportCallback excel导入回调（公共表单方法校验需要放在这里，不能校验其它文件的方法）]
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2017-02-11T21:46:50+0800
 * @param    {[object]}    data [回调数据]
 */
function ExcelImportCallback(data)
{
	if(data.code == 0)
	{
		// 成功
		if(data.data.success > 0)
		{
			$('.excel-import-success').removeClass('none');
			$('.excel-import-success').find('strong').text(data.data.success);
		} else {
			$('.excel-import-success').addClass('none');
		}

		// 失败
		if(data.data.error.length == 0)
		{
			$('.excel-import-error').addClass('none');
		} else {
			$('.excel-import-error').removeClass('none');
			$('.excel-import-error').find('strong').text(data.data.error.length);
			var html = '';
			for(var i in data.data.error)
			{
				html += '<tr><td>'+data.data.error[i]+'</td></tr>';
			}
			$('.excel-import-error').find('table tbody').html(html);
		}
	} else {
		Prompt(data.msg);
	}
	$.AMUI.progress.done();
	$('.form-validation').find('button[type="submit"]').button('reset');
}
</script>
	<!-- excel win js end --><?php endif; ?>