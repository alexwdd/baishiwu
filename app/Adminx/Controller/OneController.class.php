<?php
namespace Adminx\Controller;

class OneController extends AdminController {

	#列表
	public function index() {
		$this->display();
	}

	public function data(){
		$obj = M('Onepage');

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
	}

	#添加
	public function add() {
		if($_POST){
	        $obj = D('Onepage');
	        if ($data = $obj->create()) {
	            if ($list = $obj->add($data)) {
	            	$data['id'] = $list;
	            	$this->mk($data);
	                $this->success('操作成功');
	            } else {
	                $this->error('操作失败');
	            }
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
	        $obj = D('Onepage');
	        if ($data = $obj->create()) {
	            if ($list = $obj->save($data)) { 
	            	$this->mk($data);
	                $this->success('操作成功');
	            } else {
	                $this->error('操作失败');
	            }            
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$id = I('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$obj = M('Onepage');
			$map['id'] = $id;
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->display();
			}
		}
	}

	public function mk($data){
		if ($data) {
			$this->assign("list", $data);			
			$this->buildHtml($data['id'], '.'.HTML_PATH . '/'.CONTROLLER_NAME.'/', 'tpl');
		}
	}

	#删除
	public function del() {
		$id = explode(",",I('post.id'));
		$this->all_del('Onepage',$id);
	}
}
?>