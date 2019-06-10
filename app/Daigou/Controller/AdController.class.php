<?php
namespace Daigou\Controller;

class AdController extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
			$map['agentID'] = $this->user['id'];
			$obj = M('AgentAd');
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

	#添加
	public function add() {
		if($_POST){
			$this->all_add("AgentAd",U('Ad/index'));
		}else{
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
			$this->all_save("AgentAd",U('Ad/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map['id'] = $id;
			$map['agentID'] = $this->user['id'];
			$obj = M('AgentAd');
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->display();
			}
		}
	}
	
	#删除
	public function del() {
		$id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('AgentAd');
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