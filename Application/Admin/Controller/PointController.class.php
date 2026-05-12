<?php
namespace Admin\Controller;

/**
 * 积分管理控制器
 */
class PointController extends AdminController {
    
    /**
     * 积分记录列表
     */
    public function index() {
        $map = array();
        
        $studentId = I('student_id', 0, 'intval');
        if ($studentId) {
            $map['student_id'] = $studentId;
        }
        
        $type = I('type');
        if ($type) {
            $map['type'] = $type;
        }
        
        $startDate = I('start_date');
        $endDate = I('end_date');
        if ($startDate && $endDate) {
            $map['create_time'] = array('between', strtotime($startDate) . ',' . strtotime($endDate) . ' 23:59:59');
        }
        
        $count = M('point')->where($map)->count();
        $page = $this->showPage($count, 20);
        $list = M('point')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        // 获取学生信息
        if ($list) {
            $studentIds = array_filter(array_unique(array_column($list, 'student_id')));
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            
            foreach ($list as &$item) {
                $item['student_name'] = $students[$item['student_id']]['username'] ?: '';
            }
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 积分规则配置
     */
    public function rules() {
        $map = array('type' => 'point_rule');
        
        $count = M('config')->where($map)->count();
        $page = $this->showPage($count, 20);
        $list = M('config')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 添加积分规则
     */
    public function addRule() {
        if (IS_POST) {
            $data = array(
                'name' => I('name'),
                'value' => I('value'),
                'type' => 'point_rule',
                'create_time' => time()
            );
            
            $id = M('config')->add($data);
            if ($id) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->display();
        }
    }
    
    /**
     * 积分统计
     */
    public function statistics() {
        $startDate = I('start_date', date('Y-m-01'));
        $endDate = I('end_date', date('Y-m-d'));
        
        // 总积分
        $totalPoints = M('point')->sum('point');
        
        // 获取积分
        $earnPoints = M('point')->where(array('type' => 'earn'))->sum('point');
        
        // 消费积分
        $consumePoints = M('point')->where(array('type' => 'consume'))->sum('point');
        
        // 学生积分排名
        $studentStats = M('point')
            ->field('student_id, SUM(IF(type="earn", point, -point)) as total')
            ->group('student_id')
            ->order('total DESC')
            ->limit(20)
            ->select();
        
        if ($studentStats) {
            $studentIds = array_column($studentStats, 'student_id');
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            
            foreach ($studentStats as &$item) {
                $item['student_name'] = $students[$item['student_id']]['name'] ?: '';
            }
        }
        
        // 积分动作分布
        $actionStats = M('point')
            ->field('action, SUM(point) as total')
            ->group('action')
            ->select();
        
        $this->assign('totalPoints', $totalPoints ?: 0);
        $this->assign('earnPoints', $earnPoints ?: 0);
        $this->assign('consumePoints', $consumePoints ?: 0);
        $this->assign('studentStats', $studentStats);
        $this->assign('actionStats', $actionStats);
        $this->assign('startDate', $startDate);
        $this->assign('endDate', $endDate);
        $this->display();
    }
    
    /**
     * 手动调整积分
     */
    public function adjust() {
        if (IS_POST) {
            $studentId = I('student_id', 0, 'intval');
            $point = I('point', 0, 'intval');
            $type = I('type', 'earn');
            $description = I('description');
            
            if (empty($studentId)) {
                $this->error('请选择学生');
            }
            
            if ($point <= 0) {
                $this->error('积分必须大于0');
            }
            
            // 添加积分记录
            $data = array(
                'student_id' => $studentId,
                'type' => $type,
                'point' => $point,
                'action' => 'manual',
                'description' => $description ?: '手动调整',
                'create_time' => time()
            );
            
            if (M('point')->add($data)) {
                $this->success('积分调整成功');
            } else {
                $this->error('调整失败');
            }
        } else {
            $this->display();
        }
    }
}