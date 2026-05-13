<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>转校申请管理 - SchoolCMS</title>
    <link rel="stylesheet" href="/Public/AmazeUI/css/amazeui.min.css">
    <link rel="stylesheet" href="/Public/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<div class="am-cf admin-main">
    <div class="admin-content">
        <div class="am-g am-margin-top am-padding-top-sm">
            <div class="am-u-sm-12">
                <div class="am-btn-toolbar am-fr">
                    <a href="<?php echo U('Admin/Transfer/transfer'); ?>" class="am-btn am-btn-primary am-radius">
                        <i class="am-icon-plus"></i> 发起转校
                    </a>
                    <a href="<?php echo U('Admin/Transfer/getHistory'); ?>" class="am-btn am-btn-success am-radius">
                        <i class="am-icon-history"></i> 转校历史
                    </a>
                </div>
                <h2 class="am-text-xl"><i class="am-icon-exchange"></i> 转校申请列表</h2>
                <hr>
            </div>
            <div class="am-u-sm-12">
                <form method="get" class="am-form-inline am-margin-bottom">
                    <div class="am-form-group">
                        <input type="text" name="keyword" class="am-form-field" placeholder="学员姓名/学号" value="<?php echo I('keyword'); ?>">
                    </div>
                    <div class="am-form-group">
                        <select name="status" class="am-form-field">
                            <option value="">全部状态</option>
                            <option value="0" <?php if(I('status') === '0'): ?>selected<?php endif; ?>>待审核</option>
                            <option value="1" <?php if(I('status') === '1'): ?>selected<?php endif; ?>>已通过</option>
                            <option value="2" <?php if(I('status') === '2'): ?>selected<?php endif; ?>>已拒绝</option>
                            <option value="3" <?php if(I('status') === '3'): ?>selected<?php endif; ?>>已取消</option>
                        </select>
                    </div>
                    <button type="submit" class="am-btn am-btn-default"><i class="am-icon-search"></i> 搜索</button>
                </form>
            </div>
            <div class="am-u-sm-12">
                <table class="am-table am-table-bordered am-table-striped am-table-hover">
                    <thead>
                        <tr>
                            <th class="am-text-center">ID</th>
                            <th>学员姓名</th>
                            <th>原校区</th>
                            <th>目标校区</th>
                            <th>申请时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($list)): foreach($list as $vo): ?>
                        <tr>
                            <td class="am-text-center"><?php echo $vo['id']; ?></td>
                            <td><?php echo $vo['student_name']; ?></td>
                            <td><?php echo $vo['from_campus_name']; ?></td>
                            <td><?php echo $vo['to_campus_name']; ?></td>
                            <td><?php echo $vo['apply_time'] ? date('Y-m-d H:i', $vo['apply_time']) : '-'; ?></td>
                            <td>
                                <?php if($vo['status'] == 0): ?>
                                    <span class="am-badge am-badge-warning">待审核</span>
                                <?php elseif($vo['status'] == 1): ?>
                                    <span class="am-badge am-badge-success">已通过</span>
                                <?php elseif($vo['status'] == 2): ?>
                                    <span class="am-badge am-badge-danger">已拒绝</span>
                                <?php elseif($vo['status'] == 3): ?>
                                    <span class="am-badge am-badge-default">已取消</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo U('Admin/Transfer/transfer', array('id'=>$vo['id'])); ?>" class="am-btn am-btn-xs am-btn-primary am-radius"><i class="am-icon-eye"></i> 查看</a>
                                <?php if($vo['status'] == 0): ?>
                                    <a href="javascript:;" onclick="cancelConfirm('<?php echo U('Admin/Transfer/cancel', array('id'=>$vo['id'])); ?>')" class="am-btn am-btn-xs am-btn-warning am-radius"><i class="am-icon-close"></i> 取消</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="am-text-center am-text-danger">暂无转校申请</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php echo $page; ?>
            </div>
        </div>
    </div>
</div>
<script src="/Public/AmazeUI/js/amazeui.min.js"></script>
<script>
function cancelConfirm(url) {
    if(confirm('确定要取消此转校申请吗？')) {
        location.href = url;
    }
}
</script>
</body>
</html>