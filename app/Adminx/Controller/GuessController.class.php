<?php
namespace Adminx\Controller;

class GuessController extends AdminController {

	#列表
	public function index() {

		$title = I('title');

		if($title!=''){
			$map['title'] = array('like','%'.$title.'%');
		}		

		$obj = M('Guess');
		$count = $obj->where($map)->count();
		import("Common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}	

	public function add(){
		if ($_POST) {
			$this->all_add('Guess',U('Guess/index'));
		}else{
			$this->display();
		}
	}

	public function edit(){
		if ($_POST) {
			$this->all_save('Guess',U('Guess/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id)) {
				$this->error('参数错误');
			}

			$obj = M('Guess');

			$list = $obj->where("id=$id")->find();

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
		$this->all_del('Guess','reload');
	}
}
?>