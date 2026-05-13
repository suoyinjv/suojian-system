<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>评价管理</title>
    <link rel="stylesheet" href="/Public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Public/css/admin.css">
</head>
<body>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">评价管理</h3>
    </div>
    <div class="panel-body">
        <form method="get" class="form-inline search-form">
            <div class="form-group">
                <input type="text" name="keyword" class="form-control" placeholder="评价内容" value="<?php echo htmlspecialchars(I('keyword')); ?>">
            </div>
            <div class="form-group">
                <select name="teacher_id" class="form-control">
                    <option value="">全部老师</option>
                    <?php if (!empty($teachers)): ?>
                    <?php $sel_teacher = I('teacher_id', 0, 'intval'); ?>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo $sel_teacher == $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="score" class="form-control">
                    <option value="">全部评分</option>
                    <option value="5" <?php echo I('score')==5?'selected':''; ?>>5星</option>
                    <option value="4" <?php echo I('score')==4?'selected':''; ?>>4星</option>
                    <option value="3" <?php echo I('score')==3?'selected':''; ?>>3星</option>
                    <option value="2" <?php echo I('score')==2?'selected':''; ?>>2星</option>
                    <option value="1" <?php echo I('score')==1?'selected':''; ?>>1星</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">搜索</button>
            <a href="<?php echo U('save'); ?>" class="btn btn-success">添加评价</a>
        </form>
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>学生</th>
                    <th>老师</th>
                    <th>课程</th>
                    <th>评分</th>
                    <th>内容</th>
                    <th>时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                <?php foreach ($list as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['student_name'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($item['teacher_name'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($item['course_name'] ?: '-'); ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="glyphicon glyphicon-star<?php echo $i <= $item['score'] ? '' : '-empty'; ?>"></span>
                        <?php endfor; ?>
                    </td>
                    <td title="<?php echo htmlspecialchars($item['content'] ?? ''); ?>"><?php echo htmlspecialchars(mb_substr($item['content'] ?? '', 0, 30)); ?></td>
                    <td><?php echo $item['create_time'] ? date('Y-m-d H:i', $item['create_time']) : '-'; ?></td>
                    <td>
                        <a href="<?php echo U('save', ['id' => $item['id']]); ?>" class="btn btn-xs btn-primary">编辑</a>
                        <a href="javascript:;" onclick="if(confirm('确定删除？'))$.get('<?php echo U('delete', ['id' => $item['id']]); ?>', function(){location.reload()})" class="btn btn-xs btn-danger">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="8" class="text-center">暂无评价记录</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="text-center">
            共 <?php echo $count; ?> 条记录
        </div>
    </div>
</div>
<script src="/Public/static/js/jquery-2.0.0.min.js"></script>
<script src="/Public/bootstrap-3.3.5/js/bootstrap.min.js"></script>
</body>
</html>