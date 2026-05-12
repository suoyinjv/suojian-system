<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>积分管理</title>
    <link rel="stylesheet" href="/Public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Public/css/admin.css">
</head>
<body>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">积分管理</h3>
    </div>
    <div class="panel-body">
        <form method="get" class="form-inline search-form">
            <div class="form-group">
                <select name="student_id" class="form-control">
                    <option value="">全部学生</option>
                </select>
            </div>
            <div class="form-group">
                <select name="type" class="form-control">
                    <option value="">全部类型</option>
                    <option value="earn" {:I('type')=='earn'?'selected':''}>获取</option>
                    <option value="consume" {:I('type')=='consume'?'selected':''}>消费</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="start_date" class="form-control datepicker" placeholder="开始日期" value="{:I('start_date')}">
            </div>
            <div class="form-group">
                <input type="text" name="end_date" class="form-control datepicker" placeholder="结束日期" value="{:I('end_date')}">
            </div>
            <button type="submit" class="btn btn-primary">搜索</button>
            <a href="{:U('adjust')}" class="btn btn-success">调整积分</a>
            <a href="{:U('statistics')}" class="btn btn-info">积分统计</a>
            <a href="{:U('rules')}" class="btn btn-warning">积分规则</a>
        </form>
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>学生</th>
                    <th>类型</th>
                    <th>积分</th>
                    <th>动作</th>
                    <th>描述</th>
                    <th>时间</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><tr>
                    <td>{$item.id}</td>
                    <td>{$item.student_name}</td>
                    <td>
                        <?php if($item["type"] == 'earn'): ?><span class="label label-success">获取</span>
                        <?php else: ?>
                            <span class="label label-warning">消费</span><?php endif; ?>
                    </td>
                    <td>{$item.point}</td>
                    <td>{$item.action}</td>
                    <td>{$item.description}</td>
                    <td>{$item.create_time|date='Y-m-d H:i',###}</td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
        
        {$page}
    </div>
</div>
</body>
</html>