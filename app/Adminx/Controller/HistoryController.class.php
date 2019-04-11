<?php
namespace Adminx\Controller;

class HistoryController extends AdminController {

	#列表
	public function index() {
		$type  = I('type');
		if ($type!='') {
			$map['type'] = $type;
		}
		$obj = M('History');
		$count = $obj->where($map)->count();
		
		import("Common.ORG.Page");
		$page = new \Page($count, 15);

		$show = $page->show();
		$list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['bonus'] = unserialize($value['bonus']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('type', $type);

		$cateArr = M('Type')->where($map)->order('id asc')->getField('id,shortName');
		$this->assign('cateArr', $cateArr);
		$this->display();
	}	
	
}
?>