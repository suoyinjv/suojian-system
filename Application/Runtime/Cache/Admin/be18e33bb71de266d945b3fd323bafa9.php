<?php if (!defined('THINK_PATH')) exit();?>

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<!-- table nav start -->
		
		<!-- table nav end -->

		<div class="am-panel am-panel-primary">
			<div class="am-panel-hd">
				<h3 class="am-panel-title">
					<i class="am-icon-history"></i> <?php echo L('message_log_title'); ?>
					<a href="<?php echo U('Admin/Message/Send'); ?>" class="am-btn am-btn-xs am-btn-primary am-radius am-fr">
						<i class="am-icon-plus"></i> <?php echo L('message_send_now'); ?>
					</a>
				</h3>
			</div>
			<div class="am-panel-bd">
				<!-- 筛选表单 -->
				<form class="am-form am-form-inline am-margin-bottom" action="<?php echo U('Admin/Message/Log'); ?>" method="get">
					<div class="am-form-group">
						<input type="text" name="keyword" class="am-form-field" placeholder="<?php echo L('message_log_receiver_tips'); ?>" value="<?php echo I('keyword'); ?>">
					</div>
					<div class="am-form-group">
						<select name="channel" class="am-form-field">
							<option value=""><?php echo L('message_all_channel'); ?></option>
							<option value="sms" <?php if(I('channel') == 'sms'): ?>selected<?php endif; ?>><?php echo L('message_channel_sms'); ?></option>
							<option value="weixin" <?php if(I('channel') == 'weixin'): ?>selected<?php endif; ?>><?php echo L('message_channel_weixin'); ?></option>
						</select>
					</div>
					<div class="am-form-group">
						<select name="status" class="am-form-field">
							<option value=""><?php echo L('message_all_status'); ?></option>
							<option value="1" <?php if(I('status') == 1): ?>selected<?php endif; ?>><?php echo L('message_status_success'); ?></option>
							<option value="0" <?php if(I('status') == 0): ?>selected<?php endif; ?>><?php echo L('message_status_failed'); ?></option>
							<option value="2" <?php if(I('status') == 2): ?>selected<?php endif; ?>><?php echo L('message_status_pending'); ?></option>
						</select>
					</div>
					<div class="am-form-group">
						<input type="text" name="start_date" class="am-form-field" placeholder="<?php echo L('message_start_date'); ?>" value="<?php echo I('start_date'); ?>" data-am-datepicker readonly />
					</div>
					<div class="am-form-group">-</div>
					<div class="am-form-group">
						<input type="text" name="end_date" class="am-form-field" placeholder="<?php echo L('message_end_date'); ?>" value="<?php echo I('end_date'); ?>" data-am-datepicker readonly />
					</div>
					<button type="submit" class="am-btn am-btn-primary"><i class="am-icon-search"></i> <?php echo L('common_operation_filter'); ?></button>
					<a href="<?php echo U('Admin/Message/Log'); ?>" class="am-btn am-btn-default"><i class="am-icon-refresh"></i> <?php echo L('common_operation_reset'); ?></a>
				</form>

				<table class="am-table am-table-bordered am-table-striped am-table-hover">
					<thead>
						<tr>
							<th>ID</th>
							<th><?php echo L('message_log_sendtime'); ?></th>
							<th><?php echo L('message_log_receiver'); ?></th>
							<th><?php echo L('message_log_content'); ?></th>
							<th><?php echo L('message_log_channel'); ?></th>
							<th><?php echo L('message_log_status'); ?></th>
							<th><?php echo L('message_log_action'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($$list as $$vo): ?>
						<tr>
							<td><?php echo $vo['id']; ?></td>
							<td>{$vo.create_time|date='Y-m-d H:i',###}</td>
							<td>
								<p class="am-margin-0"><?php echo $vo['receiver_name']; ?></p>
								<p class="am-text-xs am-text-muted am-margin-0"><?php echo $vo['receiver_phone']; ?></p>
							</td>
							<td class="am-text-truncate" style="max-width:220px;" title="<?php echo $vo['content']; ?>"><?php echo $vo['content']; ?></td>
							<td>
								<?php if($vo['channel'] == 'sms'): ?>
									<span class="am-badge am-badge-primary"><?php echo L('message_channel_sms'); ?></span>
								<?php elseif($vo['channel'] == 'weixin'): ?>
									<span class="am-badge am-badge-success"><?php echo L('message_channel_weixin'); ?></span>
								<?php else: ?>
									<span class="am-badge am-badge-secondary"><?php echo L('message_channel_both'); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if($vo['status'] == 1): ?>
									<span class="am-badge am-badge-success"><?php echo L('message_status_success'); ?></span>
								<?php elseif($vo['status'] == 0): ?>
									<span class="am-badge am-badge-danger"><?php echo L('message_status_failed'); ?></span>
								<?php else: ?>
									<span class="am-badge am-badge-warning"><?php echo L('message_status_pending'); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<a href="javascript:;" onclick="viewDetail(<?php echo $vo['id']; ?>)" class="am-btn am-btn-xs am-btn-secondary am-radius"><?php echo L('message_log_view'); ?></a>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php if(!isset($$list)): ?>
						<tr>
							<td colspan="7" class="am-text-center"><?php echo L('message_log_empty'); ?></td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<div class="am-cf">
					<div class="am-fr">
						<?php echo $page; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- right content end  -->

<!-- footer start -->

<!-- footer end -->

<script>
function viewDetail(id) {
	$.get('/getLog?id=' + id, function(res) {
		if (res.data) {
			var d = res.data;
			var statusText = d.status == 1 ? '<?php echo L("message_status_success"); ?>' : (d.status == 0 ? '<?php echo L("message_status_failed"); ?>' : '<?php echo L("message_status_pending"); ?>');
			var channelText = d.channel == 'sms' ? '<?php echo L("message_channel_sms"); ?>' : (d.channel == 'weixin' ? '<?php echo L("message_channel_weixin"); ?>' : '<?php echo L("message_channel_both"); ?>');
			var html = '<div class="am-modal-bd">';
			html += '<p><strong><?php echo L("message_log_receiver"); ?>:</strong> ' + d.receiver_name + ' (' + d.receiver_phone + ')</p>';
			html += '<p><strong><?php echo L("message_send_channel"); ?>:</strong> ' + channelText + '</p>';
			html += '<p><strong><?php echo L("message_send_content"); ?>:</strong></p>';
			html += '<p class="am-text-muted">' + d.content + '</p>';
			html += '<p><strong><?php echo L("message_log_status"); ?>:</strong> ' + statusText + '</p>';
			html += '<p><strong><?php echo L("message_log_sendtime"); ?>:</strong> ' + d.create_time + '</p>';
			if (d.error_msg) {
				html += '<p class="am-text-danger"><strong><?php echo L("message_error_reason"); ?>:</strong> ' + d.error_msg + '</p>';
			}
			html += '</div>';
			$('body').append('<div class="am-modal am-modal-alert" id="log-detail-' + id + '"><div class="am-modal-dialog am-radius"><div class="am-modal-hd"><?php echo L("message_log_detail_title"); ?><a href="javascript:;" class="am-close am-close-spin" data-am-modal-close>&times;</a></div>' + html + '<div class="am-modal-footer"><span class="am-modal-btn" data-am-modal-close><?php echo L("common_operation_confirm"); ?></span></div></div></div>');
			$('#log-detail-' + id).modal();
		} else {
			alert(res.msg || '<?php echo L("message_log_view_failed"); ?>');
		}
	});
}
</script>