<?php
namespace Daigou\Controller;

class CategoryController extends CommonController {

	#分类列表
	public function index() {		
		if (IS_POST) {
			$map['agentID']=$this->user['id'];
			$list = M('AgentCate')->where($map)->field("id,name,user,fid,path,comm,sort")->order('path,id asc')->select();
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
			$this->display();
		}		
	}
	
	/*添加分类*/
 	public function add(){
 		if($_POST){
 			$cate = D('AgentCate');
			if($data = $cate->create()){
				$data['agentID'] = $this->user['id'];
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
			$map['agentID']=$this->user['id'];
			$artClass = M('AgentCate');
			$cate = $artClass->where($map)->field("id,name,fid,path")->order('path')->select();
			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}
			$this->assign('cate', $cate);
			$this->assign('fid', $fid);
			$this->assign('path', $path);
			$this->display();
		}
 	}


	public function edit(){
		if($_POST){
			$cate = D('AgentCate');
			if ($data = $cate->create()) {
				if($data['id']==$data['fid']){
					$this->error('不能以自身为上级栏目');
				}
				$thisFID = $_POST['thisFID'];

				if($thisFID==0 && $data['fid']>0){
					$this->error('根栏目不能移动');
				}
				if($data['fid']>0){
					$tClass = M('AgentCate');

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
					$url = U('AgentCate/index');
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
				$map['agentID']=$this->user['id'];
				$map['id'] = $id;
				$list=M('AgentCate')->where($map)->find();
				if($list){
					unset($map);
					$map['agentID']=$this->user['id'];
					$cate = M('AgentCate')->where($map)->field("id,name,fid,path")->order('path')->select();
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

		$map['fid'] = $id;
		$map['agentID'] = $this->user['id'];
		$cate = M('AgentCate');
		$list = $cate->where($map)->find();

		if($list){
			$this->error('请先删除子栏目');
		}
		unset($map);
		$map['id'] = $id;
		$map['agentID'] = $this->user['id'];
		$list = $cate->where($map)->delete();		
		if($list){
			$this->success("操作成功","reload");
		}else{
			$this->error('操作失败');
		}
	}
}