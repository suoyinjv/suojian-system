<?php
namespace Admin\Controller;

/**
 * 优惠券管理控制器
 */
class CouponController extends AdminController {
    
    /**
     * 优惠券列表
     */
    public function index() {
        $map = array();
        
        $keyword = I('keyword');
        if ($keyword) {
            $map['name'] = array('like', '%' . $keyword . '%');
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
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 添加优惠券
     */
    public function add() {
        if (IS_POST) {
            $data = array(
                'name' => I('name'),
                'type' => I('type', 1, 'intval'),
                'value' => I('value', 0, 'floatval'),
                'min_amount' => I('min_amount', 0, 'floatval'),
                'max_deduct' => I('max_deduct', 0, 'floatval'),
                'total_count' => I('total_count', 0, 'intval'),
                'remain_count' => I('total_count', 0, 'intval'),
                'valid_start' => I('valid_start'),
                'valid_end' => I('valid_end'),
                'status' => I('status', 1, 'intval'),
                'create_time' => time()
            );
            
            if (empty($data['name'])) {
                $this->error('优惠券名称不能为空');
            }
            
            if ($data['value'] <= 0) {
                $this->error('优惠券面值必须大于0');
            }
            
            $id = M('coupon')->add($data);
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
     * 编辑优惠券
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = array(
                'name' => I('name'),
                'type' => I('type', 1, 'intval'),
                'value' => I('value', 0, 'floatval'),
                'min_amount' => I('min_amount', 0, 'floatval'),
                'max_deduct' => I('max_deduct', 0, 'floatval'),
                'total_count' => I('total_count', 0, 'intval'),
                'valid_start' => I('valid_start'),
                'valid_end' => I('valid_end'),
                'status' => I('status', 1, 'intval'),
            );
            
            if (M('coupon')->where(array('id' => $id))->save($data)) {
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
    
    /**
     * 删除优惠券
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        
        if (M('coupon')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
    
    /**
     * 发放优惠券
     */
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
            
            if ($coupon['remain_count'] <= 0) {
                $this->error('优惠券已发完');
            }
            
            $studentIdArr = explode(',', $studentIds);
            $success = 0;
            
            foreach ($studentIdArr as $studentId) {
                $studentId = intval($studentId);
                if ($studentId > 0) {
                    // 检查是否已领取
                    $exist = M('student_coupon')->where(array('student_id' => $studentId, 'coupon_id' => $couponId))->count();
                    if ($exist > 0) continue;
                    
                    $data = array(
                        'student_id' => $studentId,
                        'coupon_id' => $couponId,
                        'status' => 0,
                        'create_time' => time()
                    );
                    
                    if (M('student_coupon')->add($data)) {
                        $success++;
                    }
                }
            }
            
            // 更新剩余数量
            M('coupon')->where(array('id' => $couponId))->setDec('remain_count', $success);
            
            $this->success("发放成功，共发放{$success}张");
        } else {
            $this->display();
        }
    }
    
    /**
     * 发放记录
     */
    public function sendLog() {
        $map = array();
        
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
        $list = M('student_coupon')->where($map)->order('id desc')->limit($page['limit'])->select();
        
        // 获取关联数据
        if ($list) {
            $couponIds = array_filter(array_unique(array_column($list, 'coupon_id')));
            $studentIds = array_filter(array_unique(array_column($list, 'student_id')));
            
            $coupons = M('coupon')->where(array('id' => array('in', $couponIds)))->index('id')->select();
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            
            foreach ($list as &$item) {
                $item['coupon_name'] = $coupons[$item['coupon_id']]['name'] ?: '';
                $item['student_name'] = $students[$item['student_id']]['name'] ?: '';
            }
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page['html']);
        $this->display();
    }
    
    /**
     * 使用优惠券
     */
    public function useCoupon() {
        $id = I('id', 0, 'intval');
        
        $data = array(
            'status' => 1,
            'use_time' => time()
        );
        
        if (M('student_coupon')->where(array('id' => $id))->save($data)) {
            $this->success('使用成功');
        } else {
            $this->error('使用失败');
        }
    }
}