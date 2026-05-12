<?php
namespace Admin\Controller;

/**
 * 评价管理控制器
 */
class ReviewController extends AdminController {
    
    /**
     * 评价列表
     */
    public function index() {
        $page = I('page', 1, 'intval');
        $rows = I('rows', 20, 'intval');
        
        $where = array();
        $keyword = I('keyword');
        if ($keyword) {
            $where['content'] = array('like', '%' . $keyword . '%');
        }
        
        $teacherId = I('teacher_id', 0, 'intval');
        if ($teacherId) {
            $where['teacher_id'] = $teacherId;
        }
        
        $score = I('score', 0, 'intval');
        if ($score) {
            $where['score'] = $score;
        }
        
        $status = I('status', -1, 'intval');
        if ($status >= 0) {
            $where['status'] = $status;
        }
        
        $list = M('review')->where($where)->order('id desc')->page($page, $rows)->select();
        $count = M('review')->where($where)->count();
        
        // 获取关联数据
        if ($list) {
            $studentIds = array_filter(array_unique(array_column($list, 'student_id')));
            $teacherIds = array_filter(array_unique(array_column($list, 'teacher_id')));
            
            $students = M('student')->where(array('id' => array('in', $studentIds)))->index('id')->select();
            $teachers = M('teacher')->where(array('id' => array('in', $teacherIds)))->index('id')->select();
            
            foreach ($list as &$item) {
                $item['student_name'] = $students[$item['student_id']]['name'] ?: '';
                $item['teacher_name'] = $teachers[$item['teacher_id']]['name'] ?: '';
            }
        }
        
        $this->ajaxReturn(['total'=>$count,'rows'=>$list]);
    }
    
    /**
     * 添加评价
     */
    public function add() {
        if (IS_POST) {
            $data = array(
                'student_id' => I('student_id', 0, 'intval'),
                'teacher_id' => I('teacher_id', 0, 'intval'),
                'course_id' => I('course_id', 0, 'intval'),
                'score' => I('score', 5, 'intval'),
                'content' => I('content'),
                'status' => I('status', 1, 'intval'),
                'create_time' => time()
            );
            
            if (empty($data['student_id']) || empty($data['teacher_id'])) {
                $this->ajaxReturn(['code'=>1,'msg'=>'学生和老师不能为空']);
            }
            
            $id = M('review')->add($data);
            if ($id) {
                $this->ajaxReturn(['code'=>0,'msg'=>'添加成功']);
            } else {
                $this->ajaxReturn(['code'=>1,'msg'=>'添加失败']);
            }
        }
    }
    
    /**
     * 删除评价
     */
    public function delete() {
        $id = I('id', 0, 'intval');
        
        if (M('review')->delete($id)) {
            $this->ajaxReturn(['code'=>0,'msg'=>'删除成功']);
        } else {
            $this->ajaxReturn(['code'=>1,'msg'=>'删除失败']);
        }
    }
    
    /**
     * 评价统计
     */
    public function statistics() {
        $where = array('status' => 1);
        
        $avgScore = M('review')->where($where)->avg('score');
        $scoreDist = array();
        for ($i = 1; $i <= 5; $i++) {
            $scoreDist[$i] = M('review')->where($where)->where(array('score' => $i))->count();
        }
        
        $this->ajaxReturn([
            'code'=>0,
            'data'=>[
                'avgScore'=>round($avgScore, 1),
                'scoreDist'=>$scoreDist
            ]
        ]);
    }
}