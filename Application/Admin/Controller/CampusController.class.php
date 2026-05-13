<?php
namespace Admin\Controller;

/**
 * 校区管理控制器
 * 多校区管理
 */
class CampusController extends CommonController {
    
    /**
     * 校区列表
     */
    public function index() {
        $list = M('campus')->order('id DESC')->select();
        
        $status_map = [0=>'禁用', 1=>'启用'];
        
        foreach ($list as &$item) {
            $item['status_text'] = $status_map[$item['status']];
            $item['add_time'] = $item['add_time'] ? date('Y-m-d', $item['add_time']) : '-';
            // 扩展字段格式化
            $item['expire_date'] = $item['expire_date'] ? date('Y-m-d', $item['expire_date']) : '-';
        }
        
        // 统计各校区数据
        foreach ($list as &$item) {
            $item['student_count'] = M('student')->where(['campus_id'=>$item['id']])->count();
            $item['teacher_count'] = M('teacher')->where(['campus_id'=>$item['id']])->count();
            $item['class_count'] = M('class')->where(['campus_id'=>$item['id']])->count();
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 添加校区
     */
    public function add() {
        if (IS_POST) {
            $data = [
                'name' => I('post.name', '', 'trim'),
                'code' => I('post.code', '', 'trim'),
                'address' => I('post.address', '', 'trim'),
                'phone' => I('post.phone', '', 'trim'),
                'principal' => I('post.principal', '', 'trim'),
                'status' => I('post.status', 1, 'intval'),
                'add_time' => time(),
                'domain' => I('post.domain', '', 'trim'),
                'site_name' => I('post.site_name', '', 'trim'),
                'theme_color' => I('post.theme_color', '', 'trim'),
                'icp' => I('post.icp', '', 'trim'),
                'expire_date' => I('post.expire_date', 0, 'strtotime'),
            ];
            
            // LOGO 上传处理
            if (!empty($_FILES['logo']['name'])) {
                $upload = new \Think\Upload();
                $upload->maxSize = 3145728; // 3MB
                $upload->exts = array('jpg', 'png', 'gif', 'jpeg');
                $upload->savePath = 'Campus/';
                $info = $upload->upload();
                if ($info) {
                    $data['logo'] = '/Uploads/' . $info['logo']['savepath'] . $info['logo']['savename'];
                } else {
                    $this->error($upload->getError());
                }
            }
            
            if (empty($data['name'])) {
                $this->error('校区名称不能为空');
            }
            
            $result = M('campus')->add($data);
            if ($result) {
                $this->success('添加成功', U('index'));
            } else {
                $this->error('添加失败');
            }
        }
        
        $this->display();
    }
    
    /**
     * 编辑校区
     */
    public function edit() {
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $data = [
                'name' => I('post.name', '', 'trim'),
                'code' => I('post.code', '', 'trim'),
                'address' => I('post.address', '', 'trim'),
                'phone' => I('post.phone', '', 'trim'),
                'principal' => I('post.principal', '', 'trim'),
                'status' => I('post.status', 1, 'intval'),
                'upd_time' => time(),
                'domain' => I('post.domain', '', 'trim'),
                'site_name' => I('post.site_name', '', 'trim'),
                'theme_color' => I('post.theme_color', '', 'trim'),
                'icp' => I('post.icp', '', 'trim'),
                'expire_date' => I('post.expire_date', 0, 'strtotime'),
            ];
            
            // LOGO 上传处理
            if (!empty($_FILES['logo']['name'])) {
                $upload = new \Think\Upload();
                $upload->maxSize = 3145728; // 3MB
                $upload->exts = array('jpg', 'png', 'gif', 'jpeg');
                $upload->savePath = 'Campus/';
                $info = $upload->upload();
                if ($info) {
                    $data['logo'] = '/Uploads/' . $info['logo']['savepath'] . $info['logo']['savename'];
                } else {
                    $this->error($upload->getError());
                }
            }
            
            M('campus')->where(['id'=>$id])->save($data);
            $this->success('更新成功', U('index'));
        }
        
        $info = M('campus')->find($id);
        $info['expire_date'] = $info['expire_date'] ? date('Y-m-d', $info['expire_date']) : '';
        $this->assign('info', $info);
        $this->display();
    }
    
    /**
     * 删除校区
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        
        // 检查是否有学员
        $student_count = M('student')->where(['campus_id'=>$id])->count();
        if ($student_count > 0) {
            $this->error('该校区下有学员，无法删除');
        }
        
        M('campus')->where(['id'=>$id])->delete();
        $this->success('删除成功');
    }
    
    /**
     * 校区数据概览
     */
    public function overview() {
        $campus_id = I('id', 0, 'intval');
        
        $campus = M('campus')->find($campus_id);
        
        // 学员统计
        $student_count = M('student')->where(['campus_id'=>$campus_id])->count();
        $new_students = M('student')->where([
            'campus_id'=>$campus_id,
            'add_time'=>['gt', strtotime('-30 days')]
        ])->count();
        
        // 老师统计
        $teacher_count = M('teacher')->where(['campus_id'=>$campus_id])->count();
        
        // 班级统计
        $class_count = M('class')->where(['campus_id'=>$campus_id])->count();
        
        // 营收统计
        $month_start = strtotime(date('Y-m-01'));
        $month_income = M('order')->where([
            'campus_id'=>$campus_id,
            'create_time'=>['gt', $month_start],
            'status'=>['in', '1,2']
        ])->sum('pay_amount');
        
        // 课消统计
        $month_consumption = M('hour_consumption')->where([
            'add_time'=>['gt', $month_start]
        ])->sum('hours');
        
        $this->assign('campus', $campus);
        $this->assign('student_count', $student_count);
        $this->assign('new_students', $new_students);
        $this->assign('teacher_count', $teacher_count);
        $this->assign('class_count', $class_count);
        $this->assign('month_income', $month_income ?: 0);
        $this->assign('month_consumption', $month_consumption ?: 0);
        
        $this->display();
    }
    
    /**
     * 跨校区学员查询
     */
    public function crossCampusStudent() {
        $keyword = I('keyword', '', 'trim');
        
        if ($keyword) {
            $where = "username LIKE '%{$keyword}%' OR my_mobile LIKE '%{$keyword}%'";
        } else {
            $where = 1;
        }
        
        $list = M('student')->where($where)->select();
        
        // 获取学员所在校区
        $campus_ids = array_unique(array_column($list, 'campus_id'));
        $campuses = !empty($campus_ids) ? M('campus')->where(['id'=>['in', $campus_ids]])->getField('id,name', true) : [];
        
        // 获取学员课时
        foreach ($list as &$item) {
            $package = M('student_package')->where([
                'student_id'=>$item['id'],
                'status'=>1
            ])->find();
            
            $item['campus_name'] = $campuses[$item['campus_id']] ?: '未分配';
            $item['balance_hours'] = $package['balance_hours'] ?: 0;
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /**
     * 跨校区老师查询
     */
    public function crossCampusTeacher() {
        $keyword = I('keyword', '', 'trim');
        
        if ($keyword) {
            $where = "username LIKE '%{$keyword}%' OR mobile LIKE '%{$keyword}%'";
        } else {
            $where = 1;
        }
        
        $list = M('teacher')->field('id, username, mobile, sex, campus_id, add_time')->where($where)->select();
        
        $campus_ids = array_unique(array_column($list, 'campus_id'));
        $campuses = !empty($campus_ids) ? M('campus')->where(['id'=>['in', $campus_ids]])->getField('id,name', true) : [];
        
        foreach ($list as &$item) {
            $item['campus_name'] = $campuses[$item['campus_id']] ?: '未分配';
            // 统计老师课程数
            $item['course_count'] = M('teacher_subject')->where(['teacher_id'=>$item['id']])->count();
        }
        
        $this->assign('list', $list);
        $this->display();
    }
}