<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>线索管理 - SchoolCMS</title>
    <link rel="stylesheet" href="/Public/AmazeUI/css/amazeui.min.css">
    <style>
        .toolbar { margin-bottom: 15px; }
        .status-tag { padding: 2px 8px; border-radius: 3px; font-size: 12px; }
        .status-1 { background: #e7f7ff; color: #1890ff; }
        .status-2 { background: #fff7e6; color: #fa8c16; }
        .status-3 { background: #f6ffed; color: #52c41a; }
        .status-4 { background: #f9f0ff; color: #722ed1; }
        .status-5 { background: #fff1f0; color: #f5222d; }
    </style>
</head>
<body>
<div class="am-cf admin-main">
    <div class="admin-content">
        <div class="am-tabs" data-am-tabs>
            <ul class="am-tabs-nav am-nav am-nav-tabs">
                <li class="am-active"><a href="#tab1">线索列表</a></li>
            </ul>
            <div class="am-tabs-bd">
                <div class="am-tab-panel am-active" id="tab1">
                    <div class="toolbar">
                        <a href="/admin.php/Admin/Lead/add" class="am-btn am-btn-primary am-radius">
                            <i class="am-icon-plus"></i> 添加线索
                        </a>
                        <a href="/admin.php/Admin/Lead/export" class="am-btn am-btn-default am-radius">
                            <i class="am-icon-download"></i> 导出
                        </a>
                    </div>
                    <form class="am-form am-form-horizontal" method="get">
                        <div class="am-form-group">
                            <div class="am-u-sm-2">
                                <input type="text" name="keyword" placeholder="搜索姓名/电话" value="<?php echo I('keyword'); ?>">
                            </div>
                            <div class="am-u-sm-2">
                                <select name="status">
                                    <option value="0">全部状态</option>
                                    <option value="1" <?php echo I('status')==1?'selected':''; ?>>新线索</option>
                                    <option value="2" <?php echo I('status')==2?'selected':''; ?>>已联系</option>
                                    <option value="3" <?php echo I('status')==3?'selected':''; ?>>有意向</option>
                                    <option value="4" <?php echo I('status')==4?'selected':''; ?>>已成交</option>
                                    <option value="5" <?php echo I('status')==5?'selected':''; ?>>无效</option>
                                </select>
                            </div>
                            <div class="am-u-sm-2">
                                <button type="submit" class="am-btn am-btn-default">搜索</button>
                            </div>
                        </div>
                    </form>
                    <table class="am-table am-table-bordered am-table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>学员姓名</th>
                                <th>电话</th>
                                <th>来源</th>
                                <th>状态</th>
                                <th>意向课程</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($$list as $$vo): ?>
                            <tr>
                                <td><?php echo $vo['id']; ?></td>
                                <td><?php echo $vo['student_name']; ?></td>
                                <td><?php echo $vo['phone']; ?></td>
                                <td><?php echo $vo['source_text']; ?></td>
                                <td><span class="status-tag status-<?php echo $vo['status']; ?>"><?php echo $vo['status_text']; ?></span></td>
                                <td><?php echo $vo['interest_course']; ?></td>
                                <td><?php echo date('Y-m-d', $vo['add_time']); ?></td>
                                <td>
                                    <a href="/admin.php/Admin/Lead/edit/id/<?php echo $vo['id']; ?>">编辑</a>
                                    <a href="/admin.php/Admin/Lead/follow/lead_id/<?php echo $vo['id']; ?>">跟进</a>
                                    <a href="javascript:;" onclick="deleteConfirm('/admin.php/Admin/Lead/delete/id/<?php echo $vo['id']; ?>')">删除</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="am-pagination">
                        <?php echo $page; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/AmazeUI/js/amazeui.min.js"></script>
<script>
function deleteConfirm(url) {
    if(confirm('确定要删除吗？')) {
        location.href = url;
    }
}
</script>
</body>
</html>