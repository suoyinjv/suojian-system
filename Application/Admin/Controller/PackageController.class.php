<?php
namespace Admin\Controller;

/**
 * 课程套餐管理控制器
 * 课时管理-课程套餐
 */
class PackageController extends CommonController {
    
    /**
     * 套餐列表
     */
    public function index() {
        $page = I('page', 1, 'intval');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $type = I('type', 0, 'intval');
        if ($type) {
            $where['type'] = $type;
        }
        
        $count = M('package')->where($where)->count();
        $list = M('package')->where($where)
            ->order('id DESC')
            ->limit($offset, $limit)
            ->select();
        
        $type_map = [1=>'课时包', 2=>'学期包', 3=>'年卡'];
        $status_map = [0=>'禁用', 1=>'启用'];
        
        foreach ($list as &$item) {
            $item['type_text'] = $type_map[$item['type']];
            $item['status_text'] = $status_map[$item['status']];
        }
        
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$limit));
        $this->display();
    }
    
    /**
     * 添加套餐
     */
    public function add() {
        if (IS_POST) {
            $data = [
                'name' => I('post.name', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'total_hours' => I('post.total_hours', 0, 'floatval'),
                'price' => I('post.price', 0, 'floatval'),
                'gift_hours' => I('post.gift_hours', 0, 'floatval'),
                'valid_days' => I('post.valid_days', 0, 'intval'),
                'status' => I('post.status', 1, 'intval'),
                'add_time' => time(),
            ];
            
            if (empty($data['name']) || empty($data['total_hours'])) {
                $this->error('请填写完整信息');
            }
            
            $result = M('package')->add($data);
            if ($result) {
                $this->success('添加成功', U('index'));
            } else {
                $this->error('添加失败');
            }
        }
        
        $this->display();
    }
    
    /**
     * 编辑套餐
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = [
                'name' => I('post.name', '', 'trim'),
                'type' => I('post.type', 1, 'intval'),
                'total_hours' => I('post.total_hours', 0, 'floatval'),
                'price' => I('post.price', 0, 'floatval'),
                'gift_hours' => I('post.gift_hours', 0, 'floatval'),
                'valid_days' => I('post.valid_days', 0, 'intval'),
                'status' => I('post.status', 1, 'intval'),
                'upd_time' => time(),
            ];
            
            M('package')->where(['id'=>$id])->save($data);
            $this->success('更新成功', U('index'));
        }
        
        $info = M('package')->find($id);
        $this->assign('info', $info);
        $this->display();
    }
    
    /**
     * 删除套餐
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        M('package')->where(['id'=>$id])->delete();
        $this->success('删除成功');
    }
    
    /**
     * 学员课时列表
     */
    public function studentPackage() {
        $page = I('page', 1, 'intval');
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $keyword = I('keyword', '', 'trim');
        $where = [];
        if ($keyword) {
            $where[] = "s.student_name LIKE '%{$keyword}%'";
        }
        
        $where_str = $where ? implode(' AND ', $where) : 1;
        
        $prefix = C('DB_PREFIX');
        $count = M('student_package')->table($prefix.'student_package sp')
            ->join($prefix.'student s ON sp.student_id=s.id')
            ->where($where_str)->count();
        
        $list = M('student_package')->table($prefix.'student_package sp')
            ->join($prefix.'student s ON sp.student_id=s.id')
            ->join($prefix.'package p ON sp.package_id=p.id')
            ->field('sp.*, s.student_name, p.name as package_name')
            ->where($where_str)
            ->order('sp.add_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        $status_map = [1=>'正常', 2=>'已用完', 3=>'已过期'];
        
        $this->assign('list', $list);
        $this->assign('status_map', $status_map);
        $this->display();
    }
    
    /**
     * 购买课时
     */
    public function buyPackage() {
        if (IS_POST) {
            $student_id = I('post.student_id', 0, 'intval');
            $package_id = I('post.package_id', 0, 'intval');
            
            $package = M('package')->find($package_id);
            if (!$package) {
                $this->error('套餐不存在');
            }
            
            // 检查学员是否存在
            $student = M('student')->find($student_id);
            if (!$student) {
                $this->error('学员不存在');
            }
            
            // 计算有效期
            $expire_time = time() + $package['valid_days'] * 86400;
            
            // 添加学员课时
            $data = [
                'student_id' => $student_id,
                'package_id' => $package_id,
                'total_hours' => $package['total_hours'] + $package['gift_hours'],
                'used_hours' => 0,
                'freeze_hours' => 0,
                'balance_hours' => $package['total_hours'] + $package['gift_hours'],
                'expire_time' => $expire_time,
                'status' => 1,
                'add_time' => time(),
            ];
            
            $result = M('student_package')->add($data);
            if ($result) {
                // 记录消费
                M('hour_consumption')->add([
                    'student_id' => $student_id,
                    'package_id' => $package_id,
                    'hours' => $data['total_hours'],
                    'type' => 0,
                    'remark' => '购买套餐: '.$package['name'],
                    'operator_id' => session('admin_id'),
                    'add_time' => time()
                ]);
                
                $this->success('购买成功');
            } else {
                $this->error('购买失败');
            }
        }
        
        // 获取套餐列表
        $packages = M('package')->where(['status'=>1])->select();
        $this->assign('packages', $packages);
        $this->display();
    }
    
    /**
     * 课时消费记录
     */
    public function consumptionLog() {
        $page = I('page', 1, 'intval');
        $limit = 30;
        $offset = ($page - 1) * $limit;
        
        $student_id = I('student_id', 0, 'intval');
        $where = [];
        if ($student_id) {
            $where['student_id'] = $student_id;
        }
        
        $prefix = C('DB_PREFIX');
        $count = M('hour_consumption')->where($where)->count();
        
        $list = M('hour_consumption')->where($where)
            ->order('add_time DESC')
            ->limit($offset, $limit)
            ->select();
        
        // 获取学员和课程名称
        $student_ids = array_unique(array_column($list, 'student_id'));
        $students = M('student')->where(['id'=>['in', $student_ids]])->getField('id,student_name', true);
        
        $type_map = [0=>'购买', 1=>'上课消费', 2=>'冻结', 3=>'解冻', 4=>'退费'];
        
        foreach ($list as &$item) {
            $item['student_name'] = $students[$item['student_id']] ?: '';
            $item['type_text'] = $type_map[$item['type']];
        }
        
        $this->assign('list', $list);
        $this->display();
    }
}