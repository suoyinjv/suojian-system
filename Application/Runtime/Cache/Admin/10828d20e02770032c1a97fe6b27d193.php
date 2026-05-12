<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>消课记录 - 所以学教学系统</title>
    <link rel="stylesheet" href="/Public/AmazeUI/css/amazeui.min.css">
    <link rel="stylesheet" href="/Public/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body>
<div class="am-cf admin-main">
    <div class="admin-content">
        <div class="am-g">
            <div class="am-u-sm-12">
                <div class="am-breadcrumb am-margin-bottom-sm">
                    <a href="<?php echo U('index'); ?>"><i class="am-icon-home"></i> 首页</a>
                    <a href="<?php echo U('index'); ?>">课时管理</a>
                    <span class="am-active">消课记录</span>
                </div>

                <!-- 统计卡片 -->
                <div class="am-g am-margin-bottom">
                    <div class="am-u-sm-3">
                        <div class="am-panel am-panel-success">
                            <div class="am-panel-bd am-text-center">
                                <p class="am-text-xxxl am-text-success"><?php echo $today_count ?? 0; ?></p>
                                <p class="am-text-sm">今日消课数</p>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-3">
                        <div class="am-panel am-panel-primary">
                            <div class="am-panel-bd am-text-center">
                                <p class="am-text-xxxl am-text-primary"><?php echo $today_hours ?? 0; ?></p>
                                <p class="am-text-sm">今日消课课时</p>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-3">
                        <div class="am-panel am-panel-warning">
                            <div class="am-panel-bd am-text-center">
                                <p class="am-text-xxxl am-text-warning"><?php echo $month_count ?? 0; ?></p>
                                <p class="am-text-sm">本月消课数</p>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-3">
                        <div class="am-panel am-panel-secondary">
                            <div class="am-panel-bd am-text-center">
                                <p class="am-text-xxxl"><?php echo $month_hours ?? 0; ?></p>
                                <p class="am-text-sm">本月消课课时</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 消课记录列表 -->
                <div class="am-panel am-panel-primary">
                    <div class="am-panel-hd">
                        <h3 class="am-panel-title">
                            <i class="am-icon-history"></i> 消课记录
                        </h3>
                    </div>
                    <div class="am-panel-bd">
                        <!-- 筛选表单 -->
                        <form class="am-form am-form-inline am-margin-bottom" method="GET" action="">
                            <input type="hidden" name="m" value="Admin">
                            <input type="hidden" name="c" value="StudentCourse">
                            <input type="hidden" name="a" value="consumption">

                            <div class="am-form-group">
                                <input type="text" name="keyword" class="am-form-field" placeholder="学员姓名/手机号" value="<?php echo htmlspecialchars(I('keyword')); ?>">
                            </div>

                            <div class="am-form-group">
                                <select name="course_id" class="am-form-field">
                                    <option value="">全部课程</option>
                                    <?php if (!empty($courses)): ?>
                                    <?php $sel_course_id = I('course_id', 0, 'intval'); ?>
                                    <?php foreach ($courses as $vo): ?>
                                    <option value="<?php echo $vo['id']; ?>" <?php echo $sel_course_id == $vo['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($vo['name']); ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="am-form-group">
                                <select name="type" class="am-form-field">
                                    <option value="">全部类型</option>
                                    <option value="attendance" <?php echo I('type') == 'attendance' ? 'selected' : ''; ?>>考勤</option>
                                    <option value="gift" <?php echo I('type') == 'gift' ? 'selected' : ''; ?>>赠送</option>
                                    <option value="manual" <?php echo I('type') == 'manual' ? 'selected' : ''; ?>>手动</option>
                                </select>
                            </div>

                            <div class="am-form-group">
                                <input type="date" name="start_date" class="am-form-field" value="<?php echo htmlspecialchars(I('start_date')); ?>">
                            </div>
                            <div class="am-form-group">-</div>
                            <div class="am-form-group">
                                <input type="date" name="end_date" class="am-form-field" value="<?php echo htmlspecialchars(I('end_date')); ?>">
                            </div>

                            <button type="submit" class="am-btn am-btn-primary">
                                <i class="am-icon-search"></i> 筛选
                            </button>
                            <a href="<?php echo U('consumption'); ?>" class="am-btn am-btn-default">
                                <i class="am-icon-refresh"></i> 重置
                            </a>
                        </form>

                        <table class="am-table am-table-bordered am-table-striped am-table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>时间</th>
                                    <th>学员</th>
                                    <th>电话</th>
                                    <th>课程</th>
                                    <th>消耗课时</th>
                                    <th>类型</th>
                                    <th>操作人</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list)): ?>
                                <?php foreach ($list as $vo): ?>
                                <tr>
                                    <td><?php echo $vo['id']; ?></td>
                                    <td><?php echo $vo['create_time'] ? date('Y-m-d H:i', $vo['create_time']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($vo['student_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($vo['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($vo['course_name'] ?? '-'); ?></td>
                                    <td><strong class="am-text-danger">-<?php echo $vo['hours']; ?></strong></td>
                                    <td>
                                        <?php
 $t = $vo['type'] ?? ''; if ($t == 'attendance'): ?>
                                            <span class="am-badge am-badge-primary">考勤</span>
                                        <?php elseif ($t == 'gift'): ?>
                                            <span class="am-badge am-badge-warning">赠送</span>
                                        <?php elseif ($t == 'manual'): ?>
                                            <span class="am-badge am-badge-secondary">手动</span>
                                        <?php elseif ($t == 'refund'): ?>
                                            <span class="am-badge am-badge-danger">退费</span>
                                        <?php else: ?>
                                            <span class="am-badge">其他</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vo['operator_name'] ?? '-'); ?></td>
                                    <td class="am-text-truncate" style="max-width:150px;" title="<?php echo htmlspecialchars($vo['remark'] ?? ''); ?>"><?php echo htmlspecialchars($vo['remark'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr><td colspan="9" class="am-text-center">暂无消课记录</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="am-cf">
                            <div class="am-fr">
                                <?php echo $page ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="am-margin-top">
                    <a href="<?php echo U('index'); ?>" class="am-btn am-btn-default">
                        <i class="am-icon-arrow-left"></i> 返回课时管理
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/static/js/jquery-2.0.0.min.js"></script>
<script src="/Public/AmazeUI/js/amazeui.min.js"></script>
</body>
</html>