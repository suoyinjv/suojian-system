<?php
namespace Admin\Controller;

/**
 * 报表统计控制器
 * 包含经营报表、招生报表、教学报表等
 */
class ReportController extends AdminController {
    
    /**
     * 经营日报表
     */
    public function dailyReport() {
        $date = I('date', date('Y-m-d'));
        $campusId = I('campus_id', 0, 'intval');
        
        // 招生数据
        $leadsData = $this->getLeadsData($date, $campusId);
        
        // 销售数据
        $orderData = $this->getOrderData($date, $campusId);
        
        // 耗课数据
        $consumptionData = $this->getConsumptionData($date, $campusId);
        
        // 出勤数据
        $attendanceData = $this->getAttendanceData($date, $campusId);
        
        $this->assign('date', $date);
        $this->assign('campusId', $campusId);
        $this->assign('leadsData', $leadsData);
        $this->assign('orderData', $orderData);
        $this->assign('consumptionData', $consumptionData);
        $this->assign('attendanceData', $attendanceData);
        $this->display();
    }
    
    /**
     * 获取线索数据
     */
    private function getLeadsData($date, $campusId) {
        $where = array(
            'DATE(create_time)' => $date
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        $total = M('lead')->where($where)->count();
        $valid = M('lead')->where($where)->where(array('status' => array('in', '1,2')))->count();
        $converted = M('lead')->where($where)->where(array('status' => 2))->count();
        
        return array(
            'total' => $total,
            'valid' => $valid,
            'converted' => $converted,
            'convert_rate' => $total > 0 ? round($converted / $total * 100, 1) : 0
        );
    }
    
    /**
     * 获取销售数据
     */
    private function getOrderData($date, $campusId) {
        $where = array(
            'DATE(create_time)' => $date,
            'status' => array('in', '1,2,3')
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        $count = M('order')->where($where)->count();
        $amount = M('order')->where($where)->sum('actual_amount');
        
        return array(
            'count' => $count,
            'amount' => $amount ? $amount : 0
        );
    }
    
    /**
     * 获取耗课数据
     */
    private function getConsumptionData($date, $campusId) {
        $where = array(
            'DATE(create_time)' => $date
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        $count = M('hour_consumption')->where($where)->count();
        $hours = M('hour_consumption')->where($where)->sum('hours');
        
        return array(
            'count' => $count,
            'hours' => $hours ? $hours : 0
        );
    }
    
    /**
     * 获取出勤数据
     */
    private function getAttendanceData($date, $campusId) {
        $where = array(
            'DATE(create_time)' => $date
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        $total = M('attendance')->where($where)->count();
        $normal = M('attendance')->where($where)->where(array('status' => 1))->count();
        
        return array(
            'total' => $total,
            'normal' => $normal,
            'rate' => $total > 0 ? round($normal / $total * 100, 1) : 0
        );
    }
    
    /**
     * 财务报表
     */
    public function financeReport() {
        $startDate = I('start_date', date('Y-m-01'));
        $endDate = I('end_date', date('Y-m-d'));
        $campusId = I('campus_id', 0, 'intval');
        
        $where = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59'),
            'status' => array('in', '1,2,3')
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        // 收入统计
        $income = M('order')->where($where)->sum('actual_amount');
        
        // 支出统计
        $expenseWhere = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59'),
            'status' => 1
        );
        if ($campusId) {
            $expenseWhere['campus_id'] = $campusId;
        }
        $expense = M('consumption')->where($expenseWhere)->sum('amount');
        
        // 退费统计
        $refundWhere = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59'),
            'status' => -1
        );
        if ($campusId) {
            $refundWhere['campus_id'] = $campusId;
        }
        $refund = M('order')->where($refundWhere)->sum('actual_amount');
        
        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->assign('campusId', $campusId);
        $this->assign('income', $income ? $income : 0);
        $this->assign('expense', $expense ? $expense : 0);
        $this->assign('refund', $refund ? $abs($refund) : 0);
        $this->assign('profit', ($income ? $income : 0) - ($expense ? $expense : 0) - ($refund ? abs($refund) : 0));
        $this->display();
    }
    
    /**
     * 招生漏斗
     */
    public function funnelReport() {
        $startDate = I('start_date', date('Y-m-01'));
        $endDate = I('end_date', date('Y-m-d'));
        $campusId = I('campus_id', 0, 'intval');
        
        $where = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59')
        );
        if ($campusId) {
            $where['campus_id'] = $campusId;
        }
        
        // 线索总数
        $total = M('lead')->where($where)->count();
        
        // 已跟进
        $followed = M('lead')->where($where)->where(array('follow_status' => array('gt', 0)))->count();
        
        // 已邀约
        $appointed = M('lead')->where($where)->where(array('follow_status' => array('egt', 2)))->count();
        
        // 已试听
        $trial = M('lead')->where($where)->where(array('follow_status' => array('egt', 3)))->count();
        
        // 已成交
        $converted = M('lead')->where($where)->where(array('status' => 2))->count();
        
        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->assign('campusId', $campusId);
        $this->assign('data', array(
            array('name' => '线索总数', 'value' => $total, 'rate' => 100),
            array('name' => '已跟进', 'value' => $followed, 'rate' => $total > 0 ? round($followed/$total*100,1) : 0),
            array('name' => '已邀约', 'value' => $appointed, 'rate' => $total > 0 ? round($appointed/$total*100,1) : 0),
            array('name' => '已试听', 'value' => $trial, 'rate' => $total > 0 ? round($trial/$total*100,1) : 0),
            array('name' => '已成交', 'value' => $converted, 'rate' => $total > 0 ? round($converted/$total*100,1) : 0)
        ));
        $this->display();
    }
    
    /**
     * 课程报表
     */
    public function courseReport() {
        $courseId = I('course_id', 0, 'intval');
        $semesterId = I('semester_id', 0, 'intval');
        
        $where = array();
        if ($courseId) {
            $where['course_id'] = $courseId;
        }
        if ($semesterId) {
            $where['semester_id'] = $semesterId;
        }
        
        // 课程报名人数
        $enrolled = M('student_course')->where($where)->count();
        
        // 课时消耗
        $hours = M('hour_consumption')->where($where)->sum('hours');
        
        // 出勤率
        $attendance = M('attendance')->where($where)->count();
        $present = M('attendance')->where($where)->where(array('status' => 1))->count();
        
        $this->assign('courseId', $courseId);
        $this->assign('semesterId', $semesterId);
        $this->assign('enrolled', $enrolled);
        $this->assign('hours', $hours ? $hours : 0);
        $this->assign('attendanceRate', $attendance > 0 ? round($present/$attendance*100,1) : 0);
        $this->display();
    }
    
    /**
     * 导出报表
     */
    public function exportReport() {
        $type = I('type', 'daily');
        
        switch ($type) {
            case 'daily':
                $this->exportDailyReport();
                break;
            case 'finance':
                $this->exportFinanceReport();
                break;
            case 'funnel':
                $this->exportFunnelReport();
                break;
            default:
                $this->error('不支持的报表类型');
        }
    }
    
    /**
     * 导出日报表
     */
    private function exportDailyReport() {
        $date = I('date', date('Y-m-d'));
        
        $campusList = M('campus')->select();
        $data = array();
        
        foreach ($campusList as $campus) {
            $leadsData = $this->getLeadsData($date, $campus['id']);
            $orderData = $this->getOrderData($date, $campus['id']);
            
            $data[] = array(
                'campus_name' => $campus['name'],
                'leads' => $leadsData['total'],
                'valid_leads' => $leadsData['valid'],
                'converted' => $leadsData['converted'],
                'orders' => $orderData['count'],
                'amount' => $orderData['amount']
            );
        }
        
        $this->exportToExcel($data, '日报表-' . $date);
    }
    
    /**
     * 导出财务表
     */
    private function exportFinanceReport() {
        $startDate = I('start_date', date('Y-m-01'));
        $endDate = I('end_date', date('Y-m-d'));
        
        $where = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59')
        );
        
        $orders = M('order')->where($where)->select();
        
        $data = array();
        foreach ($orders as $order) {
            $data[] = array(
                'order_no' => $order['order_no'],
                'student_name' => $order['student_name'],
                'amount' => $order['amount'],
                'actual_amount' => $order['actual_amount'],
                'status' => $order['status'] == 1 ? '已支付' : ($order['status'] == 2 ? '已完成' : '待支付'),
                'create_time' => date('Y-m-d H:i', $order['create_time'])
            );
        }
        
        $this->exportToExcel($data, '财务报表-' . $startDate . '至' . $endDate);
    }
    
    /**
     * 导出漏斗表
     */
    private function exportFunnelReport() {
        $startDate = I('start_date', date('Y-m-01'));
        $endDate = I('end_date', date('Y-m-d'));
        
        $where = array(
            'create_time' => array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59')
        );
        
        $leads = M('lead')->where($where)->select();
        
        $data = array();
        foreach ($leads as $lead) {
            $statusText = $lead['status'] == 0 ? '未跟进' : ($lead['status'] == 1 ? '跟进中' : '已成交');
            $followText = $lead['follow_status'] == 0 ? '未跟进' : ($lead['follow_status'] == 1 ? '已联系' : ($lead['follow_status'] == 2 ? '已邀约' : '已试听'));
            
            $data[] = array(
                'name' => $lead['name'],
                'phone' => $lead['phone'],
                'source' => $lead['source'],
                'status' => $statusText,
                'follow_status' => $followText,
                'create_time' => date('Y-m-d H:i', $lead['create_time'])
            );
        }
        
        $this->exportToExcel($data, '招生漏斗-' . $startDate . '至' . $endDate);
    }
    
    /**
     * 导出为Excel
     */
    private function exportToExcel($data, $filename) {
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()
            ->setCreator("SchoolCMS")
            ->setTitle($filename);
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // 写入数据
        if (!empty($data)) {
            $keys = array_keys($data[0]);
            $col = 'A';
            foreach ($keys as $key) {
                $sheet->setCellValue($col . '1', $key);
                $col++;
            }
            
            $row = 2;
            foreach ($data as $item) {
                $col = 'A';
                foreach ($keys as $key) {
                    $sheet->setCellValue($col . $row, $item[$key]);
                    $col++;
                }
                $row++;
            }
        }
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 招生报表 - 转向经营日报表
     */
    public function recruit() {
        // 转向 dailyReport 方法共享经营日报页面
        $this->redirect('dailyReport');
    }
    
    /**
     * 总览报表 - 占位页面
     */
    public function overview() {
        $this->display();
    }
    
    /**
     * 对比报表 - 占位页面
     */
    public function compare() {
        $this->display();
    }
}