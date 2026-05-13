<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>优惠券管理</title>
    <link rel="stylesheet" href="/Public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Public/css/admin.css">
</head>
<body>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">优惠券管理</h3>
    </div>
    <div class="panel-body">
        <form method="get" class="form-inline search-form">
            <div class="form-group">
                <input type="text" name="keyword" class="form-control" placeholder="优惠券名称" value="<?php echo I('keyword'); ?>">
            </div>
            <div class="form-group">
                <select name="type" class="form-control">
                    <option value="">全部类型</option>
                    <option value="1" <?php echo I('type')==1?'selected':''; ?>>折扣券</option>
                    <option value="2" <?php echo I('type')==2?'selected':''; ?>>抵扣券</option>
                </select>
            </div>
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="-1">全部状态</option>
                    <option value="0" <?php echo I('status')=='0'?'selected':''; ?>>禁用</option>
                    <option value="1" <?php echo I('status')=='1'?'selected':''; ?>>启用</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">搜索</button>
            <a href="<?php echo U('add'); ?>" class="btn btn-success">添加优惠券</a>
        </form>
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>优惠券名称</th>
                    <th>类型</th>
                    <th>面值</th>
                    <th>使用门槛</th>
                    <th>发放总量</th>
                    <th>剩余数量</th>
                    <th>有效期(天)</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($list as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td>
                        <?php if($item['type'] == 1): ?>
                            <span class="label label-primary">折扣券</span>
                        <?php else: ?>
                            <span class="label label-success">抵扣券</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['value_text']; ?></td>
                    <td>¥<?php echo $item['min_amount']; ?></td>
                    <td><?php echo $item['total_count']; ?></td>
                    <td><?php echo $item['remain_count']; ?></td>
                    <td><?php echo $item['valid_days']; ?>天</td>
                    <td>
                        <?php if($item['status'] == 1): ?>
                            <span class="label label-success">启用</span>
                        <?php else: ?>
                            <span class="label label-default">禁用</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo U('edit', ['id' => $item['id']]); ?>" class="btn btn-xs btn-primary">编辑</a>
                        <a href="<?php echo U('sendCoupon', ['coupon_id' => $item['id']]); ?>" class="btn btn-xs btn-info">发放</a>
                        <a href="<?php echo U('sendLog', ['coupon_id' => $item['id']]); ?>" class="btn btn-xs btn-default">记录</a>
                        <a href="javascript:;" onclick="if(confirm('确定删除？'))$.get('<?php echo U('delete', ['id' => $item['id']]); ?>', function(){location.reload()})" class="btn btn-xs btn-danger">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($list)): ?>
                <tr><td colspan="10" class="text-center">暂无优惠券</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="text-center">
            <?php echo $page; ?>
        </div>
    </div>
</div>
<script src="/Public/static/js/jquery-2.0.0.min.js"></script>
<script src="/Public/bootstrap-3.3.5/js/bootstrap.min.js"></script>
</body>
</html>