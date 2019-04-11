<?php
namespace Agent\Controller;

class FeedbackController extends CommonController {

	public $modelID=8;

	public function index(){
		if (IS_POST) {
			if($keyword!=''){
				$map['title'] = array('like', '%'.$keyword.'%');
			}
			$map['agentID'] = $this->user['id'];
			$obj = M('AgentFeedback');
			$total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
            }
            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>$pageNum,
                    "pageSize"=>$pageSize,
                    "pages"=>$pageSize,
                    "total"=>$total
                )
            );
            echo $this->return_json($result);
		}else{
			$this->display();
		}
	}

	public function reply(){
		if($_POST){//如果是post
			$data['reply'] = I('post.reply');
			$id = I('post.id');			
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			if ($data['reply']=='') {
				$this->error('请输入回复内容');
			}

			if ($data['open']==0) {
				$data['open'] = 0;
			}
			$map['id'] = $id;
			$map['agentID'] = $this->user['id'];
			$list = M('AgentFeedback')->where($map)->save($data);
			if ($list) {
				$this->success('操作成功',U('Feedback/index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$id = (int) $_GET['id'];

			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = M('AgentFeedback');
			$map['agentID'] = $this->user['id'];
			$map['id'] = $id;
			$list = $obj->where($map)->find();

			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->display();
			}
		}			
	}

	public function del(){	
		$id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('AgentFeedback');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>