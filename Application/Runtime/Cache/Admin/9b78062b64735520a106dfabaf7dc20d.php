<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>排课管理 - 所以学教学系统</title>
    <link rel="stylesheet" type="text/css" href="./Public/bootstrap-3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./Public/font-awesome-4.4.0/css/font-awesome.min.css">
    <style>
        .search-form { background: #f5f5f5; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .table-actions { margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-calendar"></i> 排课管理</h3>
    </div>
    <div class="panel-body">
        <!-- 操作按钮 -->
        <div class="table-actions">
            <button class="btn btn-success" onclick="showAddModal()">
                <i class="fa fa-plus"></i> 添加排课
            </button>
            <button class="btn btn-info" onclick="showScheduleView()">
                <i class="fa fa-th"></i> 课表视图
            </button>
        </div>
        
        <!-- 搜索表单 -->
        <div class="search-form">
            <form class="form-inline" id="searchForm">
                <div class="form-group">
                    <label>课程：</label>
                    <select name="course_id" class="form-control" id="courseSelect">
                        <option value="">全部课程</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>老师：</label>
                    <select name="teacher_id" class="form-control" id="teacherSelect">
                        <option value="">全部老师</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>星期：</label>
                    <select name="week_day" class="form-control">
                        <option value="">全部</option>
                        <option value="1">周一</option>
                        <option value="2">周二</option>
                        <option value="3">周三</option>
                        <option value="4">周四</option>
                        <option value="5">周五</option>
                        <option value="6">周六</option>
                        <option value="7">周日</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary" onclick="loadData()">
                    <i class="fa fa-search"></i> 搜索
                </button>
            </form>
        </div>
        
        <!-- 数据表格 -->
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>课程</th>
                    <th>老师</th>
                    <th>班级</th>
                    <th>上课时间</th>
                    <th>教室</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8">加载中...</td></tr>
            </tbody>
        </table>
        
        <!-- 分页 -->
        <div class="text-center">
            <ul class="pagination" id="pagination"></ul>
        </div>
    </div>
</div>

<!-- 添加/编辑模态框 -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">添加排课</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="addForm">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">课程：</label>
                        <div class="col-sm-10">
                            <select name="course_id" class="form-control" required>
                                <option value="">选择课程</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">老师：</label>
                        <div class="col-sm-10">
                            <select name="teacher_id" class="form-control" required>
                                <option value="">选择老师</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">班级：</label>
                        <div class="col-sm-10">
                            <select name="class_id" class="form-control">
                                <option value="">不指定班级</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">星期：</label>
                        <div class="col-sm-10">
                            <select name="week_day" class="form-control" required>
                                <option value="1">周一</option>
                                <option value="2">周二</option>
                                <option value="3">周三</option>
                                <option value="4">周四</option>
                                <option value="5">周五</option>
                                <option value="6">周六</option>
                                <option value="7">周日</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">时间：</label>
                        <div class="col-sm-5">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-sm-5">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">教室：</label>
                        <div class="col-sm-10">
                            <input type="text" name="room" class="form-control" placeholder="如：101教室">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">保存</button>
            </div>
        </div>
    </div>
</div>

<script src="./Public/static/js/jquery-2.0.0.min.js"></script>
<script src="./Public/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script>
var page = 1;
$(function(){
    loadOptions();
    loadData();
});

function loadOptions() {
    // 加载课程
    $.get('/index.php?m=Admin&c=Course&a=index&json=1', function(res){
        var html = '<option value="">选择课程</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'">'+v.name+'</option>';
            });
        }
        $('#courseSelect, select[name="course_id"]').html(html);
    });
    
    // 加载老师
    $.get('/index.php?m=Admin&c=Teacher&a=index&json=1', function(res){
        var html = '<option value="">选择老师</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'">'+v.name+'</option>';
            });
        }
        $('#teacherSelect, select[name="teacher_id"]').html(html);
    });
}

function loadData() {
    var params = $('#searchForm').serialize() + '&page=' + page + '&rows=20';
    $.get('/index.php?m=Admin&c=Schedule&a=index&json=1&' + params, function(res){
        var html = '';
        if(res.rows && res.rows.length > 0) {
            $.each(res.rows, function(i, v){
                html += '<tr>';
                html += '<td>'+v.id+'</td>';
                html += '<td>'+(v.course_name||'-')+'</td>';
                html += '<td>'+(v.teacher_name||'-')+'</td>';
                html += '<td>'+(v.class_name||'-')+'</td>';
                html += '<td>'+v.week_text+' '+v.time_text+'</td>';
                html += '<td>'+(v.room||'-')+'</td>';
                html += '<td><span class="label '+(v.status==1?'label-success':'label-default')+'">'+v.status_text+'</span></td>';
                html += '<td>';
                html += '<button class="btn btn-xs btn-primary" onclick="editData('+v.id+')">编辑</button> ';
                html += '<button class="btn btn-xs btn-danger" onclick="delData('+v.id+')">删除</button>';
                html += '</td></tr>';
            });
        } else {
            html = '<tr><td colspan="8" class="text-center">暂无数据</td></tr>';
        }
        $('#tableBody').html(html);
    });
}

function showAddModal() {
    $('#addForm')[0].reset();
    $('#addModal').modal('show');
}

function saveData() {
    var data = $('#addForm').serialize();
    $.post('/index.php?m=Admin&c=Schedule&a=add&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#addModal').modal('hide');
            loadData();
        }
    });
}

function editData(id) {
    alert('编辑功能开发中...');
}

function delData(id) {
    if(confirm('确定要删除这条排课吗？')) {
        $.get('/index.php?m=Admin&c=Schedule&a=del&id='+id+'&json=1', function(res){
            alert(res.msg);
            if(res.code) loadData();
        });
    }
}

function showScheduleView() {
    alert('课表视图开发中...');
}
</script>
</body>
</html>