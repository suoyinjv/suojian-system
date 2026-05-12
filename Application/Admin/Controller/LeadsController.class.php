<?php
namespace Admin\Controller;
use Think\Controller;

class LeadsController extends Controller {
    
    // 线索列表（页面 / JSON API）
    public function index() {
        $is_json = I('json', 0, 'intval');
        
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $status = I('status', 0, 'intval');
        $keyword = I('keyword', '');
        
        $where = [];
        if ($status) $where['l.status'] = $status;
        if ($keyword) {
            $where['l.name|l.phone|l.wechat'] = ['like', "%{$keyword}%"];
        }
        
        if ($is_json) {
            $list = M('leads')
                ->alias('l')
                ->field('l.*,u.nickname as follow_user_name')
                ->join('LEFT JOIN sc_user u ON l.follow_user_id=u.id')
                ->where($where)
                ->order('l.create_time desc')
                ->page($page, $rows)
                ->select();
                
            $total = M('leads')
                ->alias('l')
                ->where($where)
                ->count();
                
            $status_arr = [1=>'新线索',2=>'已跟进',3=>'已转化',4=>'已流失'];
            foreach ($list as &$v) {
                $v['status_text'] = $status_arr[$v['status']] ?? '未知';
                $v['create_date'] = date('Y-m-d', $v['create_time']);
            }
            
            $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
        }
        
        $this->display();
    }
    
    // 添加线索（API only）
    public function add() {
        $data = I('post.');
        $data['create_time'] = time();
        
        $exist = M('leads')->where(['phone'=>$data['phone']])->find();
        if ($exist) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'该电话已存在']);
        }
        
        $result = M('leads')->add($data);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'添加成功':'添加失败']);
    }
    
    // 跟进记录（API only）
    public function follow() {
        $id = I('id', 0, 'intval');
        $record = I('record', '');
        $next_time = I('next_time', 0, 'intval');
        
        $leads = M('leads')->find($id);
        $old_record = $leads['follow_record'] ?: '';
        
        $new_record = $old_record . "\n" . date('Y-m-d H:i') . '：' . $record;
        
        $result = M('leads')->save([
            'id' => $id,
            'follow_record' => $new_record,
            'follow_user_id' => session('admin_id'),
            'next_follow_time' => $next_time,
            'update_time' => time(),
            'status' => 2
        ]);
        
        $this->ajaxReturn(['code'=>$result!==false?1:0, 'msg'=>$result!==false?'跟进成功':'跟进失败']);
    }
    
    // 转化为正式学员（API only）
    public function convert() {
        $leads_id = I('leads_id', 0, 'intval');
        
        $leads = M('leads')->find($leads_id);
        if (!$leads) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'线索不存在']);
        }
        
        $exist = M('student')->where(['my_mobile'=>$leads['phone']])->find();
        if ($exist) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'该学员已存在']);
        }
        
        $student_id = M('student')->add([
            'username' => $leads['name'],
            'my_mobile' => $leads['phone'],
            'number' => 'STU' . time(),
            'semester_id' => 1,
            'class_id' => 0,
            'region_id' => 0,
            'create_time' => time()
        ]);
        
        M('leads')->save([
            'id' => $leads_id,
            'status' => 3,
            'update_time' => time()
        ]);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'转化成功', 'student_id'=>$student_id]);
    }
    
    // 统计（页面 / JSON API）
    public function statistics() {
        $is_json = I('json', 0, 'intval');
        
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));
        
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date) + 86399;
        
        // 线索统计
        $total = M('leads')->where(['create_time'=>[['egt', $start_ts], ['elt', $end_ts]]])->count();
        $converted = M('leads')->where(['status'=>3, 'create_time'=>[['egt', $start_ts], ['elt', $end_ts]]])->count();
        $convert_rate = $total > 0 ? round($converted/$total*100, 1) : 0;
        
        // 状态分布
        $status_stats = M('leads')->field('status, COUNT(*) as cnt')->group('status')->select();
        $status_arr = [1=>'新线索',2=>'已跟进',3=>'已转化',4=>'已流失'];
        
        if ($is_json) {
            $this->ajaxReturn([
                'total' => $total,
                'converted' => $converted,
                'convert_rate' => $convert_rate,
                'status_stats' => $status_stats
            ]);
        }
        
        $this->assign('total', $total);
        $this->assign('converted', $converted);
        $this->assign('convert_rate', $convert_rate);
        $this->assign('status_stats', $status_stats);
        $this->assign('status_arr', $status_arr);
        $this->assign('start_date', $start_date);
        $this->assign('end_date', $end_date);
        $this->display();
    }
}
