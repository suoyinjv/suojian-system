<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>线索管理 - 所以学教学系统</title>
    <link rel="stylesheet" type="text/css" href="./Public/bootstrap-3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./Public/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-users"></i> 销售线索</h3>
    </div>
    <div class="panel-body">
        <div class="table-actions">
            <button class="btn btn-primary" onclick="showAddModal()">
                <i class="fa fa-plus"></i> 添加线索
            </button>
            <button class="btn btn-info" onclick="showStats()">
                <i class="fa fa-bar-chart"></i> 线索统计
            </button>
        </div>
        
        <div class="search-form">
            <form class="form-inline">
                <div class="form-group">
                    <label>状态：</label>
                    <select name="status" class="form-control">
                        <option value="">全部</option>
                        <option value="1">新线索</option>
                        <option value="2">已跟进</option>
                        <option value="3">已转化</option>
                        <option value="4">已流失</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>关键词：</label>
                    <input type="text" name="keyword" class="form-control" placeholder="姓名/电话/微信">
                </div>
                <button type="button" class="btn btn-primary" onclick="loadData()">搜索</button>
            </form>
        </div>
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>姓名</th>
                    <th>电话</th>
                    <th>微信</th>
                    <th>来源</th>
                    <th>状态</th>
                    <th>跟进人</th>
                    <th>创建时间</th>
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

<!-- 添加线索模态框 -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">添加线索</h4>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    <div class="form-group">
                        <label>姓名：</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>电话：</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>微信：</label>
                        <input type="text" name="wechat" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>来源：</label>
                        <select name="source" class="form-control">
                            <option value="线上推广">线上推广</option>
                            <option value="地推">地推</option>
                            <option value="转介绍">转介绍</option>
                            <option value="其他">其他</option>
                        </select>
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

<!-- 跟进模态框 -->
<div class="modal fade" id="followModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">跟进记录</h4>
            </div>
            <div class="modal-body">
                <form id="followForm">
                    <input type="hidden" name="id" id="followId">
                    <div class="form-group">
                        <label>跟进记录：</label>
                        <textarea name="record" class="form-control" rows="4" placeholder="填写跟进情况..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>下次跟进时间：</label>
                        <input type="datetime-local" name="next_time" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveFollow()">保存跟进</button>
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
    var status = $('select[name="status"]').val();
    var keyword = $('input[name="keyword"]').val();
    var url = '/index.php?m=Admin&c=Leads&a=index&json=1&page='+page+'&rows=20';
    if(status) url += '&status='+status;
    if(keyword) url += '&keyword='+keyword;
    
    $.get(url, function(res){
        var html = '';
        if(res.rows && res.rows.length > 0) {
            var statusClass = {1:'default',2:'info',3:'success',4:'danger'};
            $.each(res.rows, function(i, v){
                html += '<tr>';
                html += '<td>'+v.id+'</td>';
                html += '<td>'+(v.name||'-')+'</td>';
                html += '<td>'+(v.phone||'-')+'</td>';
                html += '<td>'+(v.wechat||'-')+'</td>';
                html += '<td>'+(v.source||'-')+'</td>';
                html += '<td><span class="label label-'+statusClass[v.status]+'">'+v.status_text+'</span></td>';
                html += '<td>'+(v.follow_user_name||'-')+'</td>';
                html += '<td>'+v.create_date+'</td>';
                html += '<td>';
                html += '<button class="btn btn-xs btn-primary" onclick="showFollow('+v.id+')">跟进</button> ';
                if(v.status != 3) {
                    html += '<button class="btn btn-xs btn-success" onclick="convert('+v.id+')">转化</button>';
                }
                html += '</td></tr>';
            });
        } else {
            html = '<tr><td colspan="9" class="text-center">暂无线索</td></tr>';
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
    $.post('/index.php?m=Admin&c=Leads&a=add&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#addModal').modal('hide');
            loadData();
        }
    });
}

function showFollow(id) {
    $('#followId').val(id);
    $('#followModal').modal('show');
}

function saveFollow() {
    var data = $('#followForm').serialize();
    $.post('/index.php?m=Admin&c=Leads&a=follow&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#followModal').modal('hide');
            loadData();
        }
    });
}

function convert(id) {
    if(confirm('确定要将此线索转化为正式学员吗？')) {
        $.get('/index.php?m=Admin&c=Leads&a=convert&leads_id='+id+'&json=1', function(res){
            alert(res.msg);
            if(res.code) loadData();
        });
    }
}

function showStats() {
    alert('线索统计开发中...\n\n可统计：\n- 线索总数\n- 转化率\n- 各状态分布\n- 来源分析');
}
</script>
</body>
</html>