<?php
namespace Adminx\Controller;

class LinkController extends AdminController {

	public $modelID=7;
	#列表
	public function index() {

		$map['model']=$this->modelID;
		$cateArr = M('Category')->where($map)->getField('id,name');
		$path = I('path');
		$keyword  = I('keyword');

		if($path!=''){
			$map['path'] = array('like', $path.'%');
		}

		if($keyword!=''){
			$map['name'] = array('like', '%'.$name.'%');
		}		

		if (!$_SESSION['administrator']) {
			$map['cid'] = array('in',$this->cateArray);
		}

		$obj = M('Link');
		$count = $obj->where($map)->count();
		import("Common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $obj->where($map)->order('sort asc , id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		unset($map);
		$map['model']=$this->modelID;
		$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();
		foreach ($cate as $key => $value) {
			$count = count(explode('-', $value['path'])) - 3;
			$cate[$key]['count'] = $count;
		}

		$this->assign('list', $list);
		$this->assign('cateArr', $cateArr);
		$this->assign('page', $show);
		$this->assign('cate', $cate);
		$this->assign('path', $path);
		$this->assign('keyword', $keyword);
		$this->display();
	}

	#添加
	public function add() {
		if($_POST){
			if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					echo $this->echo_json_str('没有管理该分类的权限');die;
				}
			}
			$this->all_add("Link",U('Link/index'));
		}else{
			$map['model']=$this->modelID;
			$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();		

			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}

			$this->assign('cate', $cate);
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
			if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					echo $this->echo_json_str('没有管理该分类的权限');die;
				}
			}
			$this->all_save("Link",U('Link/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map['id'] = $id;
			$obj = M('Link');
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				unset($map);
				$map['model']=$this->modelID;
				$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();
				foreach ($cate as $key => $value) {
					$count = count(explode('-', $value['path'])) - 3;
					$cate[$key]['count'] = $count;
				}
				$this->assign('list', $list);
				$this->assign('cate', $cate);
				$this->display();
			}
		}
	}

	#删除
	public function del() {
		$this->all_del('Link',U('Link/index'));
	}
}
?>