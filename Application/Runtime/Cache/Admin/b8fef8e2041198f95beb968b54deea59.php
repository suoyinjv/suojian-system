<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>课时管理 - 所以学教学系统</title>
    <link rel="stylesheet" type="text/css" href="./Public/bootstrap-3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./Public/font-awesome-4.4.0/css/font-awesome.min.css">
    <style>
        .progress { height: 20px; margin-bottom: 0; }
        .progress-bar { line-height: 20px; font-size: 12px; }
    </style>
</head>
<body>
<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-clock-o"></i> 课时管理</h3>
    </div>
    <div class="panel-body">
        <div class="table-actions">
            <button class="btn btn-primary" onclick="showAddModal()">
                <i class="fa fa-plus"></i> 添加课时
            </button>
            <button class="btn btn-info" onclick="showConsumption()">
                <i fa fa-list></i> 课消记录
            </button>
        </div>
        
        <div class="search-form">
            <form class="form-inline">
                <div class="form-group">
                    <input type="text" name="keyword" class="form-control" placeholder="学员姓名/手机号">
                </div>
                <button type="button" class="btn btn-primary" onclick="loadData()">搜索</button>
            </form>
        </div>
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>学员</th>
                    <th>电话</th>
                    <th>课程</th>
                    <th>总课时</th>
                    <th>已消耗</th>
                    <th>剩余</th>
                    <th>进度</th>
                    <th>过期日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="9">加载中...</td></tr>
            </tbody>
        </table>
        
        <div class="text-center">
            <ul class="pagination" id="pagination"></ul>
        </div>
    </div>
</div>

<!-- 添加课时模态框 -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">添加课时</h4>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    <div class="form-group">
                        <label>学员：</label>
                        <select name="student_id" class="form-control" id="studentSelect" required>
                            <option value="">选择学员</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>课程：</label>
                        <select name="course_id" class="form-control" id="courseSelect" required>
                            <option value="">选择课程</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>课时数：</label>
                        <input type="number" name="total_hours" class="form-control" placeholder="请输入课时数" required>
                    </div>
                    <div class="form-group">
                        <label>过期日期：</label>
                        <input type="date" name="expire_date" class="form-control">
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
    // 加载学员
    $.get('/index.php?m=Admin&c=Student&a=index&json=1', function(res){
        var html = '<option value="">选择学员</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'">'+v.name+' ('+(v.phone||'')+')</option>';
            });
        }
        $('#studentSelect').html(html);
    });
    
    // 加载课程
    $.get('/index.php?m=Admin&c=Course&a=index&json=1', function(res){
        var html = '<option value="">选择课程</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'">'+v.name+'</option>';
            });
        }
        $('#courseSelect').html(html);
    });
}

function loadData() {
    var keyword = $('input[name="keyword"]').val();
    var url = '/index.php?m=Admin&c=StudentCourse&a=index&json=1&page='+page+'&rows=20';
    if(keyword) url += '&keyword='+keyword;
    
    $.get(url, function(res){
        var html = '';
        if(res.rows && res.rows.length > 0) {
            $.each(res.rows, function(i, v){
                var progressClass = v.used_percent > 80 ? 'progress-bar-danger' : (v.used_percent > 50 ? 'progress-bar-warning' : 'progress-bar-success');
                html += '<tr>';
                html += '<td>'+(v.student_name||'-')+'</td>';
                html += '<td>'+(v.phone||'-')+'</td>';
                html += '<td>'+(v.course_name||'-')+'</td>';
                html += '<td>'+v.total_hours+'</td>';
                html += '<td>'+v.used_hours+'</td>';
                html += '<td><strong>'+v.remaining_hours+'</strong></td>';
                html += '<td><div class="progress"><div class="progress-bar '+progressClass+'" style="width:'+v.used_percent+'%">'+v.used_percent+'%</div></div></td>';
                html += '<td>'+(v.expire_date||'永久')+'</td>';
                html += '<td><button class="btn btn-xs btn-info" onclick="showDetail('+v.student_id+')">详情</button></td></tr>';
            });
        } else {
            html = '<tr><td colspan="9" class="text-center">暂无课时记录</td></tr>';
        }
        $('#tableBody').html(html);
    });
}

function showAddModal() {
    $('#addModal').modal('show');
}

function saveData() {
    var data = $('#addForm').serialize();
    $.post('/index.php?m=Admin&c=StudentCourse&a=add&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#addModal').modal('hide');
            loadData();
        }
    });
}

function showDetail(student_id) {
    $.get('/index.php?m=Admin&c=StudentCourse&a=detail&student_id='+student_id+'&json=1', function(res){
        var html = '<h4>学员课时详情</h4>';
        if(res.courses) {
            html += '<table class="table table-bordered"><tr><th>课程</th><th>总课时</th><th>剩余</th></tr>';
            $.each(res.courses, function(i, v){
                html += '<tr><td>'+(v.course_name||'-')+'</td><td>'+v.total_hours+'</td><td>'+v.remaining_hours+'</td></tr>';
            });
            html += '</table>';
        }
        html += '<h4>最近课消记录</h4>';
        if(res.consumptions) {
            html += '<table class="table table-bordered"><tr><th>时间</th><th>消耗课时</th><th>类型</th></tr>';
            $.each(res.consumptions, function(i, v){
                var typeText = {attendance:'考勤', gift:'赠送'};
                html += '<tr><td>'+new Date(v.create_time*1000).toLocaleString()+'</td><td>'+v.hours+'</td><td>'+typeText[v.type]+'</td></tr>';
            });
            html += '</table>';
        }
        alert(html);
    });
}

function showConsumption() {
    alert('课消记录功能开发中...\n\n可查看：\n- 所有学员的课消明细\n- 按课程/老师统计\n- 每日课消趋势');
}
</script>
</body>
</html>