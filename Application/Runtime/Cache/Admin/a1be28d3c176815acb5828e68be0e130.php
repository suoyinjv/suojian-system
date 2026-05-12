<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>校区管理 - SchoolCMS</title>
    <link rel="stylesheet" href="/Public/AmazeUI/css/amazeui.min.css">
</head>
<body>
<div class="am-cf admin-main">
    <div class="admin-content">
        <div class="am-u-sm-12">
            <div class="am-btn-toolbar">
                <a href="/add" class="am-btn am-btn-primary am-radius">
                    <i class="am-icon-plus"></i> 添加校区
                </a>
                <a href="/crossCampusStudent" class="am-btn am-btn-success am-radius">
                    <i class="am-icon-users"></i> 跨校学员
                </a>
                <a href="/crossCampusTeacher" class="am-btn am-btn-warning am-radius">
                    <i class="am-icon-user"></i> 跨校老师
                </a>
            </div>
            <table class="am-table am-table-bordered am-table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>校区名称</th>
                        <th>编码</th>
                        <th>地址</th>
                        <th>电话</th>
                        <th>负责人</th>
                        <th>学员数</th>
                        <th>老师数</th>
                        <th>班级数</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($$list as $$vo): ?>
                    <tr>
                        <td><?php echo $vo['id']; ?></td>
                        <td><?php echo $vo['name']; ?></td>
                        <td><?php echo $vo['code']; ?></td>
                        <td><?php echo $vo['address']; ?></td>
                        <td><?php echo $vo['phone']; ?></td>
                        <td><?php echo $vo['principal']; ?></td>
                        <td><span class="am-badge am-badge-primary"><?php echo $vo['student_count']; ?></span></td>
                        <td><span class="am-badge am-badge-success"><?php echo $vo['teacher_count']; ?></span></td>
                        <td><span class="am-badge am-badge-warning"><?php echo $vo['class_count']; ?></span></td>
                        <td><?php echo $vo['status_text']; ?></td>
                        <td>
                            <a href="/overview/id/<?php echo $vo['id']; ?>">数据</a>
                            <a href="/edit/id/<?php echo $vo['id']; ?>">编辑</a>
                            <a href="javascript:;" onclick="deleteConfirm('/delete/id/<?php echo $vo['id']; ?>')">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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