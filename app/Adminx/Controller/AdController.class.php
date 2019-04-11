<?php
namespace Adminx\Controller;

class AdController extends AdminController {

	public $modelID=6;
	#列表
	public function index() {
		if (IS_POST) {
			$map['model']=$this->modelID;
			$cateArr = M('Category')->where($map)->getField('id,name');
			$path = I('path');
			$keyword  = I('keyword');
			$type  = I('type');
			unset($map);
			if($path!=''){
				$map['path'] = array('like', $path.'%');
			}

			if($keyword!=''){
				$map['title'] = array('like', '%'.$keyword.'%');
			}

			if (!$_SESSION['administrator']) {
				$map['cityID'] = $this->user['cityID'];
			}

			$map['del'] = 0;
			$obj = M('Ad');
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
                $list[$key]['cate'] = $cateArr[$value['cid']];
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
			unset($map);
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

	#添加
	public function add() {
		if($_POST){
			/*if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					$this->error('没有管理该分类的权限');
				}
			}*/
			$this->all_add("Ad",U('Ad/index'));
		}else{
			$path = $_GET['path'];
			$map['model']=$this->modelID;
			$cate = M('Category')->field("id,name,fid,path")->where($map)->order('path')->select();		

			foreach ($cate as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$cate[$key]['count'] = $count;
			}

			$this->assign('cate', $cate);
			$this->assign('infoCate', C('infoArr'));
			$this->assign('path', $path);
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
			/*if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					$this->error('没有管理该分类的权限');
				}
			}*/
			$this->all_save("Ad",U('Ad/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map['id'] = $id;
			$obj = M('Ad');
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
				$this->assign('cate', $cate);
				$this->assign('list', $list);
				$this->assign('infoCate', C('infoArr'));

				$city = M('OptionItem')->field('id,name')->where("cate=1")->select();;
            	$this->assign('city',$city);
				$this->display();
			}
		}
	}
	
	#删除
	public function del() {
		$id = explode(",",I('post.id'));
		$this->all_del('Ad',$id);
	}
}
?>