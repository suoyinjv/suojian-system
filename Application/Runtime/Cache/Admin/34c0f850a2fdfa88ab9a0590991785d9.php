<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>课程套餐管理 - SchoolCMS</title>
    <link rel="stylesheet" href="/Public/AmazeUI/css/amazeui.min.css">
</head>
<body>
<div class="am-cf admin-main">
    <div class="admin-content">
        <div class="am-u-sm-12">
            <div class="am-btn-toolbar">
                <a href="/admin.php/Admin/Package/add" class="am-btn am-btn-primary am-radius">
                    <i class="am-icon-plus"></i> 添加套餐
                </a>
                <a href="/admin.php/Admin/Package/studentPackage" class="am-btn am-btn-success am-radius">
                    <i class="am-icon-users"></i> 学员课时
                </a>
                <a href="/admin.php/Admin/Package/buyPackage" class="am-btn am-btn-warning am-radius">
                    <i class="am-icon-shopping-cart"></i> 购买课时
                </a>
            </div>
            <table class="am-table am-table-bordered am-table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>套餐名称</th>
                        <th>类型</th>
                        <th>课时数</th>
                        <th>价格(元)</th>
                        <th>赠送课时</th>
                        <th>有效期(天)</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($$list as $$vo): ?>
                    <tr>
                        <td><?php echo $vo['id']; ?></td>
                        <td><?php echo $vo['name']; ?></td>
                        <td><?php echo $vo['type_text']; ?></td>
                        <td><?php echo $vo['total_hours']; ?></td>
                        <td><?php echo $vo['price']; ?></td>
                        <td><?php echo $vo['gift_hours']; ?></td>
                        <td><?php echo $vo['valid_days']; ?></td>
                        <td><?php echo $vo['status_text']; ?></td>
                        <td>
                            <a href="/admin.php/Admin/Package/edit/id/<?php echo $vo['id']; ?>">编辑</a>
                            <a href="javascript:;" onclick="deleteConfirm('/admin.php/Admin/Package/delete/id/<?php echo $vo['id']; ?>')">删除</a>
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