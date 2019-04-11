<?php
namespace Adminx\Controller;

class AlbumController extends AdminController {
	public $modelID=4;//相册模型ID
	public $db;
	public $imageDb;
	public $category;
	public function _initialize(){
		parent::_initialize();
		//实例化数据模型
		$this->db=D("Album");
		$this->imageDb=D("AlbumImage");
		$this->category=D("Category");
	}
	
    public function index(){
    	$map['model']=$this->modelID;
    	$cate = $this->category->where($map)->getField('id,name');
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
		$count = $this->db->where($map)->count();
		import("Common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $this->db->where($map)->order('sort asc , id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		
		unset($map);
		if (!$_SESSION['administrator']) {		
			$map['id'] = array('in',$this->cateArray);
		}
		$map['model']=$this->modelID;
		$sclass = $this->category->field("id,name,fid,path")->where($map)->order('path')->select();

		foreach ($sclass as $key => $value) {

			$count = count(explode('-', $value['path'])) - 3;

			$sclass[$key]['count'] = $count;

		}

		$this->assign('sclass', $sclass);
		$this->assign('list', $list);
		$this->assign('cate', $cate);
		$this->assign('page', $show);
		$this->assign('path', $path);
    	$this->display();
    }

    //创建相册
    public function add(){
    	if($_POST){
    		$this->all_add('Album',U('Album/index'));
    	}else{
			if (!$_SESSION['administrator']) {		
				$map['id'] = array('in',$this->cateArray);
			}
			$map['model']=$this->modelID;
			$list = $this->category->field("id,name,fid,path")->where($map)->order('path')->select();
			foreach ($list as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$list[$key]['count'] = $count;
			}
			$this->assign('list', $list);
			$this->assign('date',date("Y-m-d",time()));
			$this->display();
		}
    }

	//编辑相册
	public function edit(){
		if($_POST){
			$this->all_save('Album',U('Album/index'));
		}else{
			$id = I('get.id');
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			if (!$_SESSION['administrator']) {		
				$map['id'] = array('in',$this->cateArray);
			}
			$map['model']=$this->modelID;
			$clist = $this->category->field("id,name,fid,path")->where($map)->order('path')->select();
			foreach ($clist as $key => $value) {
				$count = count(explode('-', $value['path'])) - 3;
				$clist[$key]['count'] = $count;
			}
			$list = $this->db->where("id=$id")->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {	
				$this->assign('list', $list);
				$this->assign('clist', $clist);
				$this->display();
			}
		}
	}
	//删除
	public function del() {
		$id=I('post.selectedids');
		if($id==''){
			$this->error("您没有选择任何信息！");
		}else{
			foreach ($id as $v) {
				$where['id'] = $v;
				$list = $this->db->where($where)->find();
				if($list['num']>0){
					$this->error("请删除“".$list['name']."”相册中的照片");
				}else{
					if ($list['picname']!='') {
						unlink(C('SITE_PATH').$list['picname']);
					}
					$this->db->where($where)->delete();
				}
			}
            $this->success("删除成功",U('Album/index'));
		}
	}

	/************相册照片管理*******************/
	//列表
	public function image(){
		$id = I('get.id');
		if (!isset ($id) || !is_numeric($id)) {
			$this->error('参数错误');
		}

		//相册信息
		$map['id']=$id;
		$album=$this->db->where($map)->find();
		if(!$album){
			$this->error("相册不存在");
		}
		unset($map);
		$map['albumID']=$id;
		$count = $this->imageDb->where($map)->count();
		import("Common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $this->imageDb->where($map)->order('sort asc , id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('album', $album);
		$this->display();
	}
	//添加图片
	public function addimage(){
		if($_POST){
			$this->all_add('AlbumImage',U('Album/image',array('id'=>I("albumID"))));
		}else{
			$albumID = I('get.albumID');
			if (!isset ($albumID) || !is_numeric($albumID)) {
				$this->error('参数错误');
			}
			//相册信息
			$map['id']=$albumID;
			$album=$this->db->where($map)->find();
			if(!$album){
				$this->error("相册不存在");
			}
			$this->assign('album', $album);
			$this->display();
		}
	}

	//编辑图片
	public function editimg(){
		if($_POST){
			$this->all_save('AlbumImage',U('Album/image',array('id'=>I("albumID"))));
		}else{
			$id = I('get.id');
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map["id"]=$id;
			$list=$this->imageDb->where($map)->find();
			if($list){
				//相册信息
				unset($map);
				$map['id']=$list['albumID'];
				$album=$this->db->where($map)->find();
				if(!$album){
					$this->error("相册不存在");
				}
				$this->assign('album', $album);
				$this->assign('list', $list);
				$this->display();
			}else{
				$this->error("信息不存在");
			}
		}
	}
	//删除图片
	public function delimg(){
		$id = I('get.id');
		if (!isset ($id) || !is_numeric($id)) {
			$this->error('参数错误');
		}
		$map["id"]=$id;
		$image=$this->imageDb->where($map)->find();
		if($image){
			if ($image['picname']!='') {
				unlink(C('SITE_PATH').$image['picname']);
			}
			$this->imageDb->where($map)->delete();
		}
		//更新相册图片数
		unset($map);
		$map['id']=$image['albumID'];
        M("Album")->where($map)->setDec("num");
		$this->success("删除成功");
	}
}