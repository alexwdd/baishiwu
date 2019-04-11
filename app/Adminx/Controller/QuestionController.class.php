<?php
namespace Adminx\Controller;

class QuestionController extends AdminController {

	public function index(){
		$cateArr = M('QuestionCate')->where($map)->getField('id,name');
		$path = I('path');
		$keyword  = I('keyword');
		unset($map);
		if($path!=''){
			$map['path'] = array('like', $path.'%');
		}

		if($keyword!=''){
			$map['name'] = array('like', '%'.$keyword.'%');
		}

		$obj = M('Question');
		$count = $obj->where($map)->count();

		import("Common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

		$cate = M('QuestionCate')->field("id,name,fid,path")->order('path,sort asc')->select();
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
		if ($_POST) {
	        $obj = D('Question');
	        if ($data = $obj->create()) {
	        	$r = M('Question')->where(array('cid'=>$data['cid'],'sort'=>$data['sort']))->count();
	        	if ($r>0) {
	        		$this->error('题号重复');
	        	}
	            if ($list = $obj->add($data)) {
	                $this->success('操作成功',U('Question/index'));
	            } else {
	                $this->error('操作失败');
	            }
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$cate = M('QuestionCate')->field("id,name,fid,path")->order('path,sort asc')->select();		
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
		if ($_POST) {
			$this->all_save('Question',U('Question/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = M('Question');
			$list = $obj->where("id=$id")->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				unset($map);
				$cate = M('QuestionCate')->field("id,name,fid,path")->order('path,sort asc')->select();
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
		$id=I('post.selectedids');
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$obj = M('Question');
				$where['id'] = $v;
				$obj->where($where)->delete();
				unset($where);
				$obj = M('QuestionItem');
				$where['sid'] = $v;
				$obj->where($where)->delete();
			}
			$this->success('删除成功','reload');
		}
	}

	#清空票数
	public function item(){
		$obj = M('QuestionItem');
		$sid = I('get.sid');

		if ($sid=='' || !is_numeric($sid)) {
			$this->error('参数错误');
		}
		$question = M('Question')->where(array('id'=>$sid))->find();
		if (!$question) {
			$this->error('试题不存在');
		}
		$map['sid']=$sid;		
		$list = $obj->where($map)->order('id asc')->select();
		$this->assign('list', $list);
		$this->assign('question', $question);
		$this->display();
	}

	#添加
	public function itemadd() {
		if ($_POST) {
			$name = I('post.name');
			$picname = I('post.picname');
			$status = I('post.status');
			$sort = I('post.sort');
			$sid = I('post.sid');
			$type = I('post.type');

			if ($status=='' || $sort=='' || $sid=='' || $type=='') {
				$this->error('内容不完整');
			}

			if ($name=='' && $picname=='') {
				$this->error('请上传图片或输入选项文字');
			}

			if ($status==1 && $type==1) {
				M('QuestionItem')->where(array('sid'=>$sid))->setField('status','0');
			}
			$data = array(
				'name'=>$name,
				'picname'=>$picname,
				'type'=>$type,
				'status'=>$status,
				'sort'=>$sort,
				'sid'=>$sid,
				'createTime'=>time(),
				'updateTime'=>time(),
				);
			$obj = M('QuestionItem');
	        if ($list = $obj->add($data)) {
	        	M("Question")->where(array('id'=>$sid))->setInc('number');
                $this->success('操作成功',U('Question/item',array('sid'=>I('post.sid'))));
            } else {
                $this->error('操作失败');
            }
		}else{
			$sid = I('get.sid');
			$question = M('Question')->where(array('id'=>$sid))->find();
			if (!$question) {
				$this->error('问题不存在');
			}
			$this->assign('question', $question);
			$this->display();
		}
	}

	#编辑
	public function itemedit() {
		if ($_POST) {
			$name = I('post.name');
			$picname = I('post.picname');
			$status = I('post.status');
			$sort = I('post.sort');
			$sid = I('post.sid');
			$id = I('post.id');
			$type = I('post.type');

			if ($id=='' || $type=='' || $status=='' || $sort=='' || $sid=='') {
				$this->error('内容不完整');
			}

			if ($name=='' && $picname=='') {
				$this->error('请上传图片或输入选项文字');
			}

			if ($status==1 && $type==1) {
				M('QuestionItem')->where(array('sid'=>$sid))->setField('status','0');
			}
			$data = array(
				'name'=>$name,
				'picname'=>$picname,
				'status'=>$status,
				'sort'=>$sort,
				'sid'=>$sid,
				'updateTime'=>time(),
				);
			$obj = M('QuestionItem');
	        if ($list = $obj->where(array('id'=>$id))->save($data)) {
                $this->success('操作成功',U('Question/item',array('sid'=>I('post.sid'))));
            } else {
                $this->error('操作失败');
            }
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id)) {
				$this->error('参数错误');
			}

			$obj = M('QuestionItem');

			$list = $obj->where("id=$id")->find();

			if (!$list) {
				$this->error('信息不存在');
			} else {
				$sid = $list['sid'];
				$question = M('Question')->where(array('id'=>$sid))->find();
				$this->assign('list', $list);
				$this->assign('question', $question);
				$this->display();
			}
		}
	}

	#删除
	public function itemdel() {
		$id=I('post.selectedids');
		$sid=I('post.sid');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $obj = M('QuestionItem');
            $list = $obj->where($map)->delete();
            if ($list) {
            	$number = count($id);
            	M("Question")->where(array('id'=>$sid))->setDec('number',$number);
                $this->success('操作成功','reload');
            }else{
                $this->error('操作失败');
            }
        }
	}
}
?>