<?php
namespace Daigou\Controller;

class KefuController extends CommonController {

	#编辑
	public function index() {
		if($_POST){
			$content = I('post.content');
			if($content==''){
				$this->error("请输入客服信息");
			}

			$map['agentID'] = $this->user['id'];
			$list = M('AgentKefu')->where($map)->find();
			if($list){
				$data['content'] = $content;
				$data['updateTime'] = time();
				M('AgentKefu')->where($map)->save($data);
			}else{
				$data['content'] = $content;
				$data['agentID'] = $this->user['id'];
				$data['updateTime'] = time();
				M('AgentKefu')->add($data);
			}
			$this->success("操作成功");
		}else{
			$map['agentID'] = $this->user['id'];
			$obj = M('AgentKefu');
			$list = $obj->where($map)->find();	
			$this->assign('list', $list);
			$this->display();
		}
	}
}
?>