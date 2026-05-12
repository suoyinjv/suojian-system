<?php
namespace Admin\Controller;
use Think\Controller;

class LeadsController extends Controller {
    
    // 线索列表
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        $status = I('status', 0, 'intval');
        $keyword = I('keyword', '');
        
        $where = [];
        if ($status) $where['l.status'] = $status;
        if ($keyword) {
            $where['l.name|l.phone|l.wechat'] = ['like', "%{$keyword}%"];
        }
        
        $list = M('leads')
            ->alias('l')
            ->field('l.*,u.name as follow_user_name')
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
            $v['status_text'] = $status_arr[$v['status']];
            $v['create_date'] = date('Y-m-d', $v['create_time']);
        }
        
        $this->ajaxReturn(['total'=>$total,'rows'=>$list]);
    }
    
    // 添加线索
    public function add() {
        $data = I('post.');
        $data['create_time'] = time();
        
        // 检查重复线索
        $exist = M('leads')->where(['phone'=>$data['phone']])->find();
        if ($exist) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'该电话已存在']);
        }
        
        $result = M('leads')->add($data);
        $this->ajaxReturn(['code'=>$result?1:0, 'msg'=>$result?'添加成功':'添加失败']);
    }
    
    // 跟进记录
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
            'status' => 2 // 已跟进
        ]);
        
        $this->ajaxReturn(['code'=>$result!==false?1:0, 'msg'=>$result!==false?'跟进成功':'跟进失败']);
    }
    
    // 转化为正式学员
    public function convert() {
        $leads_id = I('leads_id', 0, 'intval');
        
        $leads = M('leads')->find($leads_id);
        if (!$leads) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'线索不存在']);
        }
        
        // 检查学员是否已存在
        $exist = M('student')->where(['phone'=>$leads['phone']])->find();
        if ($exist) {
            $this->ajaxReturn(['code'=>0, 'msg'=>'该学员已存在']);
        }
        
        // 创建学员
        $student_id = M('student')->add([
            'name' => $leads['name'],
            'phone' => $leads['phone'],
            'wechat' => $leads['wechat'],
            'source' => $leads['source'],
            'create_time' => time()
        ]);
        
        // 更新线索状态
        M('leads')->save([
            'id' => $leads_id,
            'status' => 3, // 已转化
            'update_time' => time()
        ]);
        
        $this->ajaxReturn(['code'=>1, 'msg'=>'转化成功', 'student_id'=>$student_id]);
    }
    
    // 统计
    public function statistics() {
        $start_date = I('start_date', date('Y-m-01'));
        $end_date = I('end_date', date('Y-m-d'));
        
        // 线索统计
        $total = M('leads')->where("create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date}')")->count();
        $converted = M('leads')->where("status=3 AND create_time >= UNIX_TIMESTAMP('{$start_date}') AND create_time <= UNIX_TIMESTAMP('{$end_date}')")->count();
        
        // 转化率
        $convert_rate = $total > 0 ? round($converted/$total*100, 1) : 0;
        
        // 状态分布
        $status_stats = M('leads')->field('status, COUNT(*) as cnt')->group('status')->select();
        
        $this->ajaxReturn([
            'total' => $total,
            'converted' => $converted,
            'convert_rate' => $convert_rate,
            'status_stats' => $status_stats
        ]);
    }
}