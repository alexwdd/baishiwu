<?php
namespace Adminx\Controller;

class NodeController extends AdminController {

	#列表
	public function index() {
		$node = M('Node')->where('level=1')->order('sort asc , id asc')->select();
		foreach ($node as $key => $value) {
			$action = M('Node')->where('pid='.$value['id'])->select();
			$node[$key]['child'] = $action;
		}
		$this->assign('node',$node);
		$this->display();

	}

	#获得子节点
	public function getchild(){
		$pid = I('get.pid');
		$map['pid'] = $pid;
		$list = M('Node')->field('id,name,title')->where($map)->order('sort asc')->select();
		echo json_encode($list);
	}

	#添加模型
	public function add() {
		if(isset($_POST['dosubmit'])) {
			$obj = D('Node');
			if ($data = $obj->create()) {
				if ($obj->add($data)) {
					$map['pid'] = $data['pid'];
					$list = M('Node')->where($map)->order('sort asc , id asc')->select();
		            $this->success('添加成功',$list);
				} else {
					$this->error('添加失败');
				}
			} else {
				$this->error($obj->getError());
			}
		}else{
			$level = I('get.level');
			$pid = I('get.pid');
			if ($level==1) {
				$nodeName = '分组';
			}elseif($level==2){
				$nodeName = '控制器';
			}elseif($level==3){
				$nodeName = '方法';
			}

			$map['id'] = $pid;
			$pname = M('Node')->where($map)->getField('title');

			$this->assign('nodeName',$nodeName);		
			$this->assign('level',$level);
			$this->assign('pname',$pname);
			$this->assign('pid',$pid);
			$leftMenu = C('leftMenu');
			unset($leftMenu[0]);
			$this->assign('leftMenu',$leftMenu);
			$this->display();
		}		
	}

	#编辑
	public function edit() {
		if(isset($_POST['dosubmit'])) {
			$obj = D('Node');
			if ($data = $obj->create()) {
				if ($obj->save($data)) {
		            $map['pid'] = $data['pid'];
					$list = M('Node')->where($map)->order('sort asc , id asc')->select();
					$this->success('编辑成功',$list);
				} else {
					$this->error('编辑失败');
				}
			} else {
				$this->error($obj->getError());
			}
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = M('Node');
			$list = $obj->where("id=$id")->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				if ($list['level']==1) {
					$nodeName = '分组';
				}elseif($list['level']==2){
					$nodeName = '控制器';
				}elseif($list['level']==3){
					$nodeName = '方法';
				}
				$map['id']=$list['pid'];
				$pname = M('Node')->where($map)->getField('title');
				$this->assign('pname',$pname);
				$this->assign('nodeName',$nodeName);
				$this->assign('list', $list);
				$leftMenu = C('leftMenu');
				unset($leftMenu[0]);
				$this->assign('leftMenu',$leftMenu);
				$this->display();
			}
		}
	}

	#删除
	public function del() {

		$id = I('get.nodeID');
		if(!isset($id)){
			echo '请选择删除的节点';die;
		}

		$obj = M('Node');
		$list = $obj->where('pid='.$id)->find();
		if($list){
			echo '请先删除子节点';die;
		}

		$list = $obj->where('id='.$id)->delete();

		if($list){
			echo '1';
		}else{
			echo '删除失败';
		}
	}
	
}
?>