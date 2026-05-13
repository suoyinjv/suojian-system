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

<!-- right content start  -->
<div class="content-right">
	<div class="content">
		<!-- 页面标题 -->
		<div class="am-g" style="margin-bottom:15px;">
			<div class="am-u-sm-6">
				<span class="fs-16 fw-700">📁 备课资源管理</span>
			</div>
			<div class="am-u-sm-6 tr">
				<button type="button" class="am-btn am-btn-primary am-radius am-btn-sm" onclick="$('#uploadModal').modal('open');">
					<i class="am-icon-upload"></i> 上传资源
				</button>
			</div>
		</div>

		<!-- 筛选 -->
		<form class="am-form am-form-inline" method="get" style="margin-bottom:15px;">
			<div class="am-form-group">
				<select name="live_id" class="am-radius" onchange="this.form.submit()">
					<option value="0">全部课程</option>
					<?php if(is_array($live_list)): foreach($live_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>" <?php if($live_id == $v['id']): ?>selected<?php endif; ?>><?php echo ($v["title"]); ?></option><?php endforeach; endif; ?>
				</select>
			</div>
		</form>

		<!-- 资源列表 -->
		<div class="am-panel am-panel-default">
			<div class="am-panel-bd">
				<table class="am-table am-table-bordered am-table-striped">
					<thead>
						<tr>
							<th>资源名称</th>
							<th>类型</th>
							<th>关联课程</th>
							<th>上传教师</th>
							<th>文件大小</th>
							<th>上传时间</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
							<td>
								<?php if($vo['type'] == 'img'): ?><a href="{$vo.file_url}" target="_blank"><img src="{$vo.file_url}" style="width:40px;height:40px;vertical-align:middle;margin-right:5px;object-fit:cover;"></a><?php endif; ?>
								{$vo.title}
							</td>
							<td><span class="am-badge am-badge-secondary am-radius">{$vo.type_text}</span></td>
							<td>{$vo.course_title}</td>
							<td>{$vo.teacher_name}</td>
							<td>{$vo.file_size_text}</td>
							<td>{$vo.create_time}</td>
							<td>
								<?php if($vo['file_url']): ?><a href="{$vo.file_url}" target="_blank" class="am-btn am-btn-default am-btn-xs am-radius">下载</a><?php endif; ?>
								<a href="javascript:;" onclick="delResource({$vo.id})" class="am-btn am-btn-danger am-btn-xs am-radius">删除</a>
							</td>
						</tr><?php endforeach; endif; else: echo "" ;endif; ?>
						<?php if(count($list) == 0): ?><tr><td colspan="7" class="am-text-center cr-999">暂无资源数据</td></tr><?php endif; ?>
					</tbody>
				</table>
				<div class="am-pagination"><?php echo ($page); ?></div>
			</div>
		</div>
	</div>
</div>

<!-- 上传弹窗 -->
<div class="am-modal am-modal-no-btn" tabindex="-1" id="uploadModal">
	<div class="am-modal-dialog">
		<div class="am-modal-hd">
			上传备课资源
			<a href="javascript:;" class="am-close am-close-spin" data-am-modal-close>&times;</a>
		</div>
		<div class="am-modal-bd">
			<form class="am-form" id="uploadForm" enctype="multipart/form-data">
				<div class="am-form-group">
					<label>资源名称 <span class="cr-red">*</span></label>
					<input type="text" name="title" placeholder="请输入资源名称" required>
				</div>
				<div class="am-form-group">
					<label>关联课程 <span class="cr-red">*</span></label>
					<select name="course_id" class="am-radius" required>
						<option value="">请选择课程</option>
						<?php if(is_array($live_list)): foreach($live_list as $key=>$v): ?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["title"]); ?></option><?php endforeach; endif; ?>
					</select>
				</div>
				<div class="am-form-group">
					<label>选择文件 <span class="cr-red">*</span></label>
					<input type="file" name="file" id="resourceFile" required>
					<span class="fs-12 cr-999">支持文档、PPT、图片、视频、音频，最大50MB</span>
				</div>
				<div class="am-form-group">
					<button type="button" class="am-btn am-btn-primary am-radius w100" onclick="doUpload()">开始上传</button>
				</div>
			</form>
			<div id="uploadProgress" class="am-progress am-progress-striped am-active" style="display:none;">
				<div class="am-progress-bar" style="width: 0%"></div>
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
function delResource(id) {
    if (!confirm('确定删除此资源？')) return;
    $.ajax({
        url: '{:U("Admin/Live/resourceDelete")}',
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

function doUpload() {
    var formData = new FormData($('#uploadForm')[0]);
    var file = $('#resourceFile')[0].files[0];
    if (!file) { alert('请选择文件'); return; }

    $('#uploadProgress').show();
    $.ajax({
        url: '{:U("Admin/Live/resourceUpload")}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var pct = Math.round(e.loaded / e.total * 100);
                    $('.am-progress-bar').css('width', pct + '%');
                }
            });
            return xhr;
        },
        success: function(res) {
            if (res.code == 1) {
                $('#uploadModal').modal('close');
                location.reload();
            } else {
                alert(res.msg);
            }
        },
        error: function() {
            alert('上传失败，请重试');
        }
    });
}
</script>