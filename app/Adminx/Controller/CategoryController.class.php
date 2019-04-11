<?php
namespace Adminx\Controller;

class CategoryController extends AdminController {
    
    Public $arr = array();

	public function _initialize(){    	

		parent::_initialize();

		$where['user'] = array('like','%'.$this->adminName.'%');
		$list = M('Category')->field('id')->where($where)->select();
		foreach ($list as $key => $value) {
			$this->arr[] = $value['id'];
			$this->getClassID($value['id'],$arr);
		}

	}

    public function getClassID($fid,$arr){
		$map['fid']=$fid;
		$list = M('Category')->field('id')->where($map)->select();
		foreach ($list as $key => $value) {			
			$this->arr[] = $value['id'];
			$this->getClassID($list['id'],$arr);
		}
	}

	#分类列表
	public function index() {
		if (IS_POST) {
			$mid=I("get.mid");
			if(empty($mid)){die;}
			$map['model']=$mid;
			$list = M('Category')->where($map)->field("id,name,comm,user,fid,path,sort")->order('path,id asc')->select();
			foreach ($list as $key => $value) {
				$count = count(explode('-', $value['path'])) - 2;
				if ($value['fid'] > 0) {
					$list[$key]['style'] = 'style="padding-left:' . (($count * 10) + 10) . 'px;"';
				}
			}
			$result = array(
                'data'=>$list
            );
            echo $this->return_json($result);			
		}else{
			$mid=I("mid",0,"intval");
			if(empty($mid)){
				$mid=1;
			}
			$this->assign('mid', $mid);
			$this->display();
		}		
	}
	
	/*添加分类*/
 	public function add(){
 		if($_POST){
 			$cate = D('Category');
			if($data = $cate->create()){
				if ($list=$cate->add($data)) {
					$data['path']=$data['path'].$list.'-';
					$cate->where('id='.$list)->save($data);
	                $this->success('分类添加成功',$url);
				} else {
					$this->error('分类添加失败');
				}
			}else{
				$this->error($cate->getError());
			}
 		}else{
 			$fid = I('get.id');
 			$path = I('get.path');
			$mid=I('get.mid',1);
			$map['model']=$mid;
				$artClass = M('Category');
			$cate = $artClass->where($map)->field("id,name,fid,path")->order('path')->select();
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			$this->assign('mid', $mid);
			$this->assign('fid', $fid);
			$this->assign('path', $path);
			$this->display();
		}
 	}


	public function edit(){
		if($_POST){
			$cate = D('Category');
			if ($data = $cate->create()) {
				if($data['id']==$data['fid']){
					$this->error('不能以自身为上级栏目');
				}
				$thisFID = $_POST['thisFID'];

				if($thisFID==0 && $data['fid']>0){
					$this->error('根栏目不能移动');
				}
				if($data['fid']>0){
					$tClass = M('Category');

					$fdata = $tClass->field('path')->where('id='.$data['fid'])->find();

					if(strstr($fdata['path'],$data['path'])){
						$this->error('不能移动到自身下级栏目');
					}
					$data['path']=$fdata['path'].$data['id'].'-';
				}else{
					$data['path']=I('post.path');
				}
				$oldPath = I('post.path');

				if ($cate->save($data)) {
					$cate->execute("UPDATE __PREFIX__category SET path = replace(path, '".$oldPath."','".$data['path']."')");
					$url = U('Category/index');
					$this->success('分类编辑成功',$url);
				} else {
					$this->error('分类编辑失败');
				}
			} else {
				$this->error($cate->getError());
			}
		}else{
			$id = I('get.id');
			if(isset($id)){
				$list=M('Category')->where('id='.$id)->find();
				if($list){
					$map['model']=$list['model'];
					$cate = M('Category')->where($map)->field("id,name,fid,path")->order('path')->select();
					foreach ($cate as $key => $value) {
						$count = count(explode('-', $value['path'])) - 3;
						$cate[$key]['count'] = $count;
					}

					$this->assign('list',$list);
					$this->assign('cate', $cate);
					$this->display();
				}else{
					$this->error('没有该分类');
				}
			}else{
				$this->error('参数错误');
			}
		}
	}
	
	public function del(){
		$id = I('get.id');
		if(!isset($id) || !is_numeric($id)){
			$this->error('您没有选择任何分类！');
		}

		$cate = M('Category');
		$list = $cate->where('fid='.$id)->find();

		if($list){
			$this->error('请先删除子栏目');
		}

		$list = $cate->where('id='.$id)->delete();		
		if($list){
			$this->success("操作成功","reload");
		}else{
			$this->error('操作失败');
		}
	}
}