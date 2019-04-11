<?php
namespace Adminx\Controller;

class VideoController extends AdminController {

	public $modelID=3;

	#列表
	public function index() {

		$map['model']=$this->modelID;
		$cateArr = M('Category')->where($map)->getField('id,name');
		$path = I('path');
		$keyword  = I('keyword');
		unset($map);
		if($path!=''){
			$map['path'] = array('like', $path.'%');
		}

		if($keyword!=''){
			$map['title'] = array('like', '%'.$keyword.'%');
		}		

		if (!$_SESSION['administrator']) {
			$map['cid'] = array('in',$this->cateArray);
		}

		$map['del'] = 0;
		$Video = M('Video');
		$count = $Video->where($map)->count();

		import("Common.ORG.Page");
		$page = new \Page($count, 15);

		$show = $page->show();

		$list = $Video->where($map)->order('sort asc , id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		unset($map);
		$map['model']=$this->modelID;
		$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();
		foreach ($cate as $key => $value) {
			$count = count(explode('-', $value['path'])) - 3;
			$cate[$key]['count'] = $count;
		}
		$this->assign('cate', $cate);
		$this->assign('list', $list);
		$this->assign('cateArr', $cateArr);
		$this->assign('page', $show);
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
					$this->error('没有管理该分类的权限');
				}
			}
			$this->all_add('Video',U('Video/index'));
		}else{
			$path = $_GET['path'];
			$map['model']=$this->modelID;
			$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();		
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			$this->assign('path', $path);
			$this->assign('date',date("Y-m-d",time()));
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
			if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					$this->error('没有管理该分类的权限');
				}
			}
			$this->all_save('Video',U('Video/index'));
		}else{
			$id = I('get.id');
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}		
			$map['id'] = $id;
			$list = M('Video')->where($map)->find();

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
		$this->all_del('Video','reload');
	}
}
?>