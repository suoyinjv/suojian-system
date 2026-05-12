<?php
namespace Admin\Controller;

class CouponController extends AdminController {
    
    public function index() {
        $map = [];
        
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
            
            if (M('coupon')->where(['id' => $id])->save($data) !== false) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = M('coupon')->find($id);
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    public function delete() {
        $id = I('id', 0, 'intval');
        if (M('coupon')->delete($id)) {
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
            
            $coupon = M('coupon')->find($couponId);
            if (!$coupon) {
                $this->error('优惠券不存在');
            }
            
            $remain = $coupon['total_count'] - $coupon['used_count'];
            if ($remain <= 0) {
                $this->error('优惠券已发完');
            }
            
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
        $map = [];
        
        $couponId = I('coupon_id', 0, 'intval');
        if ($couponId) {
            $map['coupon_id'] = $couponId;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $map['status'] = $status;
        }
        
        $count = M('student_coupon')->where($map)->count();
        $page = $this->showPage($count, 20);
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
