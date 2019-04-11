<?php
namespace Adminx\Controller;

class ArticleController extends AdminController {

	public $modelID=1;

	#文章列表
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

			if($type!=''){
				$map['status'] = $type;
			}	

			if (!$_SESSION['administrator']) {
				$map['cityID'] = $this->user['cityID'];
			}

			$map['del'] = 0;
			$obj = M('Article');
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
                $list[$key]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$value['createTime']).'/'.$value['id'].'.html';
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

	public function trash() {		
		if (IS_POST) {
			$map['model']=$this->modelID;
			$cateArr = M('Category')->where($map)->getField('id,name');

			unset($map);			
			$map['del'] = 1;
			$obj = M('Article');
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
			$this->display();
		}
	}

	#文章添加
	public function add() {
		if($_POST){
			if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					$this->error('没有管理该分类的权限');
				}
			}

	        $obj = D('Article');
	        if ($data = $obj->create()) {
	            if ($list = $obj->add($data)) {
	            	$data['id'] = $list;
	            	$this->mk($data);
	                $this->success('操作成功',U('Article/index'));
	            } else {
	                $this->error('操作失败');
	            }
	        } else {
	            $this->error($obj->getError());
	        }			
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

	#文章编辑
	public function edit() {
		if($_POST){
			if (!$_SESSION['administrator']) {
				$cate = explode(',', I('post.cid'));
				if(!in_array($cate[0], $this->cateArray)) {
					$this->error('没有管理该分类的权限');
				}
			}

	        $obj = D('Article');
	        if ($data = $obj->create()) {
	            if ($list = $obj->save($data)) {  
	            	$this->mk($data);              
	                $this->success('操作成功',U('Article/index'));
	            } else {
	                $this->error('操作失败');
	            }            
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$id = I('get.id');
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}		
			$map['id'] = $id;
			$list = M('Article')->where($map)->find();

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

	public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $id = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = M('Article');
        $map['id'] = $id;
        $rs=$obj->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $obj->where(array('id'=>$id))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

	#文章删除
	public function del() {
		$id = explode(",", I('post.id'));
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$article = M('Article');
				$where['id'] = $v;
				$article->where($where)->setField('del','1');
			}

			$map['articleid'] = array('in',$id);
			$map['type'] = 'article';
			M('Commend')->where($map)->delete();
            $this->success('删除成功');
		}
	}

	#文章删除
	public function truedel() {
		$id = explode(",", I('post.id'));
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$article = M('Article');
				$where['id'] = $v;
				$list = $article->field('id,picname,createTime')->where($where)->find();
				if ($list['picname']!='') {
					unlink(C('SITE_PATH').$list['picname']);
					unlink(C('SITE_PATH').'/HTML/Article/'.date("ym",$list['createTime']).'/'.$list['id'].'.html');
				}
				$article->where($where)->delete();
			}
            $url = U('Article/trash');
            $this->success('删除成功',$url);
		}
	}

	#还原
	public function restore(){
		$id = explode(",", I('post.id'));
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$article = M('Article');
				$where['id'] = $v;
				$article->where($where)->setField('del','0');
			}
            $url = U('Article/trash');
            $this->success('操作成功');
		}
	}

	//批量移动
	public function move(){
		if (IS_POST) {
			$id=I('post.id');
			$id = explode("-", $id);
			$class = explode(',', I('post.cid'));

			$data['cid'] = $class[0];
			$data['path'] = $class[1];

			if($id==''){
				$this->error('您没有选择任何信息！');
			}else{
				foreach ($id as $v) {
					$article = M('Article');
					$where['id'] = $v;
					$list = $article->where($where)->save($data);
				}
	            $url = "reload";
	            $this->success('操作成功');
			}
		}else{
			$id=I('post.id');
			$this->assign('id',$id);
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

	public function mk($data){
		if ($data) {
			if ($data['picname']!='') {
                $data['picname'] = C('site.domain').$data['picname'];
            }
			$this->assign("list", $data);			
			$this->buildHtml($data['id'], '.'.HTML_PATH . '/'.CONTROLLER_NAME.'/'.date('ym',$data['createTime']).'/', 'tpl');
		}
	}
}
?>