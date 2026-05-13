<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>订单管理 - 所以学教学系统</title>
    <link rel="stylesheet" type="text/css" href="./Public/bootstrap-3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./Public/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-shopping-cart"></i> 订单管理</h3>
    </div>
    <div class="panel-body">
        <div class="table-actions">
            <button class="btn btn-success" onclick="showAddModal()">
                <i class="fa fa-plus"></i> 创建订单
            </button>
            <button class="btn btn-info" onclick="showStats()">
                <i class="fa fa-bar-chart"></i> 订单统计
            </button>
        </div>
        
        <div class="search-form">
            <form class="form-inline">
                <div class="form-group">
                    <label>状态：</label>
                    <select name="status" class="form-control">
                        <option value="-1">全部</option>
                        <option value="0">待支付</option>
                        <option value="1">已支付</option>
                        <option value="2">已取消</option>
                        <option value="3">已退款</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>关键词：</label>
                    <input type="text" name="keyword" class="form-control" placeholder="订单号/学员姓名/电话">
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
        
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>订单号</th>
                    <th>学员</th>
                    <th>课程/套餐</th>
                    <th>课时</th>
                    <th>金额</th>
                    <th>实付</th>
                    <th>支付方式</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="10">加载中...</td></tr>
            </tbody>
        </table>
        
        <div class="text-center">
            <ul class="pagination" id="pagination"></ul>
        </div>
        
        <!-- 统计汇总 -->
        <div class="panel panel-default" style="margin-top:20px;">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4>总订单数</h4>
                        <h2 id="totalOrders">0</h2>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4>已支付</h4>
                        <h2 id="paidOrders">0</h2>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4>总收入</h4>
                        <h2 id="totalRevenue">¥0</h2>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4>实收</h4>
                        <h2 id="paidRevenue">¥0</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 创建订单模态框 -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">创建订单</h4>
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
                        <label>课程/套餐：</label>
                        <select name="course_id" class="form-control" id="courseSelect" required>
                            <option value="">选择课程</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>购买课时：</label>
                        <input type="number" name="total_hours" class="form-control" placeholder="请输入课时数" required>
                    </div>
                    <div class="form-group">
                        <label>订单金额：</label>
                        <input type="number" name="total_amount" class="form-control" placeholder="请输入金额" required>
                    </div>
                    <div class="form-group">
                        <label>备注：</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">创建订单</button>
            </div>
        </div>
    </div>
</div>

<!-- 支付模态框 -->
<div class="modal fade" id="payModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">订单支付</h4>
            </div>
            <div class="modal-body">
                <form id="payForm">
                    <input type="hidden" name="id" id="payId">
                    <div class="form-group">
                        <label>支付方式：</label>
                        <select name="pay_type" class="form-control">
                            <option value="wechat">微信支付</option>
                            <option value="alipay">支付宝</option>
                            <option value="cash">现金</option>
                            <option value="bank">银行转账</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-success" onclick="doPay()">确认支付</button>
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
    $.get('/index.php?m=Admin&c=Student&a=index&json=1', function(res){
        var html = '<option value="">选择学员</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'">'+v.name+' ('+(v.phone||'')+')</option>';
            });
        }
        $('#studentSelect').html(html);
    });
    
    $.get('/index.php?m=Admin&c=Course&a=index&json=1', function(res){
        var html = '<option value="">选择课程</option>';
        if(res.rows) {
            $.each(res.rows, function(i, v){
                html += '<option value="'+v.id+'" data-price="'+v.price+'">'+v.name+' (¥'+v.price+'/课时)</option>';
            });
        }
        $('#courseSelect').html(html);
    });
    
    // 课程选择时自动填充价格
    $('#courseSelect').change(function(){
        var price = $(this).find(':selected').data('price');
        var hours = $('input[name="total_hours"]').val();
        if(price && hours) {
            $('input[name="total_amount"]').val(price * hours);
        }
    });
    $('input[name="total_hours"]').change(function(){
        var price = $('#courseSelect').find(':selected').data('price');
        if(price) {
            $('input[name="total_amount"]').val(price * $(this).val());
        }
    });
}

function loadData() {
    var params = $('#searchForm').serialize() + '&page=' + page + '&rows=20';
    $.get('/index.php?m=Admin&c=Order&a=index&json=1&' + params, function(res){
        var html = '';
        if(res.rows && res.rows.length > 0) {
            var statusClass = {0:'warning',1:'success',2:'default',3:'danger'};
            $.each(res.rows, function(i, v){
                html += '<tr>';
                html += '<td>'+v.order_no+'</td>';
                html += '<td>'+(v.student_name||'-')+'</td>';
                html += '<td>'+(v.course_name||'-')+'</td>';
                html += '<td>'+v.total_hours+'</td>';
                html += '<td>¥'+v.total_amount+'</td>';
                html += '<td><strong>¥'+v.pay_amount+'</strong></td>';
                html += '<td>'+(v.pay_type_text||'-')+'</td>';
                html += '<td><span class="label label-'+statusClass[v.status]+'">'+v.status_text+'</span></td>';
                html += '<td>'+v.create_date+'</td>';
                html += '<td>';
                if(v.status == 0) {
                    html += '<button class="btn btn-xs btn-success" onclick="showPay('+v.id+')">支付</button> ';
                }
                if(v.status == 1) {
                    html += '<button class="btn btn-xs btn-warning" onclick="refund('+v.id+')">退款</button>';
                }
                html += '</td></tr>';
            });
        } else {
            html = '<tr><td colspan="10" class="text-center">暂无订单</td></tr>';
        }
        $('#tableBody').html(html);
        
        // 更新统计
        if(res.total) {
            // 这里可以添加统计数据的显示
        }
    });
}

function showAddModal() {
    $('#addForm')[0].reset();
    $('#addModal').modal('show');
}

function saveData() {
    var data = $('#addForm').serialize();
    $.post('/index.php?m=Admin&c=Order&a=add&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#addModal').modal('hide');
            loadData();
        }
    });
}

function showPay(id) {
    $('#payId').val(id);
    $('#payModal').modal('show');
}

function doPay() {
    var data = $('#payForm').serialize();
    $.post('/index.php?m=Admin&c=Order&a=pay&json=1', data, function(res){
        alert(res.msg);
        if(res.code) {
            $('#payModal').modal('hide');
            loadData();
        }
    });
}

function refund(id) {
    if(confirm('确定要退款吗？')) {
        var remark = prompt('请输入退款原因：');
        if(remark !== null) {
            $.get('/index.php?m=Admin&c=Order&a=refund&id='+id+'&remark='+encodeURIComponent(remark)+'&json=1', function(res){
                alert(res.msg);
                if(res.code) loadData();
            });
        }
    }
}

function showStats() {
    alert('订单统计开发中...\n\n可统计：\n- 订单趋势\n- 收入趋势\n- 课程收入排名\n- 支付方式分布');
}
</script>
</body>
</html>