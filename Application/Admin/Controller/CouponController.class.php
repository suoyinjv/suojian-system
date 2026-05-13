<?php
namespace Admin\Controller;

class CouponController extends AdminController {
    
    /**
     * 获取租户过滤条件
     */
    private function getCampusWhere() {
        $where = [];
        $is_super = !empty($this->admin['is_super']);
        $campus_id = intval($this->admin['campus_id']);
        if (!$is_super && $campus_id > 0) {
            $where['campus_id'] = $campus_id;
        }
        return $where;
    }
    
    public function index() {
        $map = $this->getCampusWhere();
        
        $keyword = I('keyword');
        if ($keyword) {
            $map['name'] = ['like', '%' . $keyword . '%'];
        }
        
        $type = I('type', 0, 'intval');
        if ($type) {
            $map['type'] = $type;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $map['status'] = $status;
        }
        
        $count = M('coupon')->where($map)->count();
        $page = $this->showPage($count, 20);
        $list = M('coupon')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        foreach ($list as &$v) {
            $v['type_text'] = $v['type'] == 1 ? '折扣券' : '抵扣券';
            $v['remain_count'] = $v['total_count'] - $v['used_count'];
            if ($v['type'] == 1) {
                $v['value_text'] = ($v['discount_rate'] / 10) . '折';
            } else {
                $v['value_text'] = '¥' . $v['discount_amount'];
            }
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    public function add() {
        if (IS_POST) {
            $data = [
                'name' => I('name'),
                'type' => I('type', 1, 'intval'),
                'min_amount' => I('min_amount', 0, 'floatval'),
                'discount_amount' => I('discount_amount', 0, 'floatval'),
                'discount_rate' => I('discount_rate', 100, 'floatval'),
                'total_count' => I('total_count', 0, 'intval'),
                'valid_days' => I('valid_days', 30, 'intval'),
                'status' => I('status', 1, 'intval'),
                'add_time' => time(),
            ];
            
            if (empty($data['name'])) {
                $this->error('优惠券名称不能为空');
            }
            
            // 租户过滤
            $campusWhere = $this->getCampusWhere();
            $data['campus_id'] = $campusWhere['campus_id'];
            
            if (M('coupon')->add($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->display();
        }
    }
    
    public function edit() {
        $id = I('id', 0, 'intval');
        $campusWhere = $this->getCampusWhere();
        
        if (IS_POST) {
            $data = [
                'name' => I('name'),
                'type' => I('type', 1, 'intval'),
                'min_amount' => I('min_amount', 0, 'floatval'),
                'discount_amount' => I('discount_amount', 0, 'floatval'),
                'discount_rate' => I('discount_rate', 100, 'floatval'),
                'total_count' => I('total_count', 0, 'intval'),
                'valid_days' => I('valid_days', 30, 'intval'),
                'status' => I('status', 1, 'intval'),
            ];
            
            if (M('coupon')->where(array_merge(['id' => $id], $campusWhere))->save($data) !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = M('coupon')->where(array_merge(['id' => $id], $campusWhere))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    public function delete() {
        $id = I('id', 0, 'intval');
        $campusWhere = $this->getCampusWhere();
        if (M('coupon')->where(array_merge(['id' => $id], $campusWhere))->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
    
    public function sendCoupon() {
        if (IS_POST) {
            $couponId = I('coupon_id', 0, 'intval');
            $studentIds = I('student_ids');
            
            if (empty($couponId) || empty($studentIds)) {
                $this->error('参数不完整');
            }
            
            $campusWhere = $this->getCampusWhere();
            $coupon = M('coupon')->where(array_merge(['id' => $couponId], $campusWhere))->find();
            if (!$coupon) {
                $this->error('优惠券不存在');
            }
            
            $remain = $coupon['total_count'] - $coupon['used_count'];
            if ($remain <= 0) {
                $this->error('优惠券已发完');
            }
            
            // 学生也需按租户过滤
            $studentWhere = $this->getCampusWhere();
            $studentIdArr = explode(',', $studentIds);
            $success = 0;
            
            foreach ($studentIdArr as $studentId) {
                $studentId = intval($studentId);
                if ($studentId > 0) {
                    $exist = M('student_coupon')->where(['student_id' => $studentId, 'coupon_id' => $couponId])->count();
                    if ($exist > 0) continue;
                    
                    if (M('student_coupon')->add([
                        'student_id' => $studentId,
                        'coupon_id' => $couponId,
                        'status' => 0,
                        'add_time' => time()
                    ])) {
                        $success++;
                    }
                }
            }
            
            M('coupon')->where(['id' => $couponId])->setInc('used_count', $success);
            
            $this->success("发放成功，共发放{$success}张");
        } else {
            $this->display();
        }
    }
    
    public function sendLog() {
        $map = $this->getCampusWhere();
        
        $couponId = I('coupon_id', 0, 'intval');
        if ($couponId) {
            $map['coupon_id'] = $couponId;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $map['status'] = $status;
        }
        
        $count = M('student_coupon')->alias('sc')
            ->join('LEFT JOIN sc_coupon c ON sc.coupon_id=c.id')
            ->where(array_merge($map, ['c.campus_id' => $map['campus_id']]))
            ->count();
        $page = $this->showPage($count, 20);
        
        // 构建带租户过滤的关联查询
        $list = M('student_coupon')->alias('sc')
            ->field('sc.*,c.name as coupon_name,s.username as student_name')
            ->join('LEFT JOIN sc_coupon c ON sc.coupon_id=c.id')
            ->join('LEFT JOIN sc_student s ON sc.student_id=s.id')
            ->where($map)
            ->order('sc.id desc')
            ->limit($page['limit'])
            ->select();
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    public function useCoupon() {
        $id = I('id', 0, 'intval');
        
        // 使用优惠券需要验证归属的优惠券是否属于当前租户
        $scoupon = M('student_coupon')->alias('sc')
            ->join('LEFT JOIN sc_coupon c ON sc.coupon_id=c.id')
            ->where(['sc.id' => $id])
            ->field('sc.*, c.campus_id')
            ->find();
        
        if (!$scoupon) {
            $this->error('记录不存在');
        }
        
        $campusWhere = $this->getCampusWhere();
        if (!$campusWhere && !empty($campusWhere['campus_id']) && $scoupon['campus_id'] != $campusWhere['campus_id']) {
            $this->error('无权操作此记录');
        }
        
        if (M('student_coupon')->where(['id' => $id])->save([
            'status' => 1,
            'use_time' => time()
        ]) !== false) {
            $this->success('使用成功');
        } else {
            $this->error('使用失败');
        }
    }
}
