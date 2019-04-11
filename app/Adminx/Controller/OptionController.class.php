<?php
namespace Adminx\Controller;

class OptionController extends AdminController {

	#列表
	public function index() {
		if (IS_POST) {
			$obj = M('OptionCate');
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
		}else{
			$this->display();
		}
	}

	#添加
	public function add() {
		if($_POST){
    		$this->all_add('OptionCate',U('Option/index'));
    	}else{
			$this->display();
		}
	}

	#编辑
	public function edit() {
		if($_POST){
    		$this->all_save('OptionCate',U('Option/index'));
    	}else{
			$id = (int) $_GET['id'];
			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = M('OptionCate');
			$map['id']=$id;
			$list = $obj->where($map)->find();
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
		$id = explode(",",I('post.id'));
		if($id==''){
			$this->error('您没有选择任何信息！');
		}else{
			foreach ($id as $v) {
				$obj = M('OptionCate');
				$where['id'] = $v;
				$obj->where($where)->delete();
				M('OptionItem')->where('cate='.$v)->delete();
			}
	        $this->success('操作成功',U('Option/index'));
		}
	}
}
?>