<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>考勤管理 - 所以学教学系统</title>
    <link rel="stylesheet" type="text/css" href="./Public/bootstrap-3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./Public/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-clock-o"></i> 考勤记录</h3>
    </div>
    <div class="panel-body">
        <!-- 操作按钮 -->
        <div class="table-actions">
            <button class="btn btn-primary" onclick="showCheckinModal()">
                <i class="fa fa-sign-in"></i> 学员签到
            </button>
            <button class="btn btn-success" onclick="showScanModal()">
                <i class="fa fa-credit-card"></i> 刷卡考勤
            </button>
            <button class="btn btn-info" onclick="showStatsModal()">
                <i class="fa fa-bar-chart"></i> 考勤统计
            </button>
        </div>
        
        <!-- 搜索 -->
        <div class="search-form">
            <form class="form-inline" id="searchForm">
                <div class="form-group">
                    <label>学员：</label>
                    <input type="text" name="keyword" class="form-control" placeholder="姓名/手机号">
                </div>
                <div class="form-group">
                    <label>日期：</label>
                    <input type="date" name="start_date" class="form-control">
                    -
                    <input type="date" name="end_date" class="form-control">
                </div>
                <button type="button" class="btn btn-primary" onclick="loadData()">搜索</button>
            </form>
        </div>
        
        <!-- 表格 -->
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>学员</th>
                    <th>电话</th>
                    <th>课程</th>
                    <th>考勤日期</th>
                    <th>状态</th>
                    <th>扣课时</th>
                    <th>自动消课</th>
                    <th>备注</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8">加载中...</td></tr>
            </tbody>
        </table>
        
        <div class="text-center">
            <ul class="pagination" id="pagination"></ul>
        </div>
    </div>
</div>

<!-- 签到模态框 -->
<div class="modal fade" id="checkinModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">学员签到</h4>
            </div>
            <div class="modal-body">
                <form id="checkinForm">
                    <div class="form-group">
                        <label>学员手机号：</label>
                        <input type="text" name="phone" class="form-control" placeholder="输入学员手机号">
                    </div>
                    <div class="form-group">
                        <label>考勤状态：</label>
                        <select name="status" class="form-control">
                            <option value="1">正常</option>
                            <option value="2">请假</option>
                            <option value="3">旷课</option>
                            <option value="4">迟到</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="doCheckin()">签到</button>
            </div>
        </div>
    </div>
</div>

<!-- 刷卡模态框 -->
<div class="modal fade" id="scanModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">刷卡考勤</h4>
            </div>
            <div class="modal-body">
                <form id="scanForm">
                    <div class="form-group">
                        <label>刷卡/手机号：</label>
                        <input type="text" name="phone" class="form-control" placeholder="刷卡或输入手机号">
                    </div>
                    <div class="form-group">
                        <label>选择课程：</label>
                        <select name="schedule_id" class="form-control" id="scheduleSelect">
                            <option value="">请先选择课程</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-success" onclick="doScan()">确认签到</button>
            </div>
        </div>
    </div>
</div>

<script src="./Public/static/js/jquery-2.0.0.min.js"></script>
<script src="./Public/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script>
var page = 1;
$(function(){
    loadData();
});

function loadData() {
    var params = $('#searchForm').serialize() + '&page=' + page + '&rows=20';
    $.get('/index.php?m=Admin&c=Attendance&a=index&json=1&' + params, function(res){
        var html = '';
        if(res.rows && res.rows.length > 0) {
            $.each(res.rows, function(i, v){
                var statusClass = {1:'success',2:'info',3:'danger',4:'warning'};
                html += '<tr>';
                html += '<td>'+(v.student_name||'-')+'</td>';
                html += '<td>'+(v.phone||'-')+'</td>';
                html += '<td>'+(v.course_name||'-')+'</td>';
                html += '<td>'+v.attend_date+'</td>';
                html += '<td><span class="label label-'+statusClass[v.status]+'">'+v.status_text+'</span></td>';
                html += '<td>'+v.hours+'</td>';
                html += '<td>'+v.auto_deduct_text+'</td>';
                html += '<td>'+(v.remark||'-')+'</td></tr>';
            });
        } else {
            html = '<tr><td colspan="8" class="text-center">暂无考勤记录</td></tr>';
        }
        $('#tableBody').html(html);
    });
}

function showCheckinModal() {
    $('#checkinModal').modal('show');
}

function doCheckin() {
    var phone = $('input[name="phone"]').val();
    if(!phone) { alert('请输入手机号'); return; }
    
    $.get('/index.php?m=Admin&c=Attendance&a=scanCard&phone='+phone+'&json=1', function(res){
        alert(res.msg);
        if(res.code) {
            $('#checkinModal').modal('hide');
            loadData();
        }
    });
}

function showScanModal() {
    $('#scanModal').modal('show');
}

function doScan() {
    alert('刷卡功能需要配合硬件设备使用');
}

function showStatsModal() {
    alert('考勤统计功能开发中...\n\n可统计：\n- 按状态分布（正常/请假/旷课）\n- 每日考勤趋势\n- 课消统计');
}
</script>
</body>
</html>