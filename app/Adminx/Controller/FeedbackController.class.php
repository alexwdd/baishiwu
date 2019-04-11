<?php
namespace Adminx\Controller;

class FeedbackController extends AdminController {


	public function index(){
		if (IS_POST) {
            $keyword = I('post.keyword');
            $cityID = I('post.cityID');
            $type = I('post.type');
			$articleid = I('post.articleid');
			if($keyword!=''){
				$map['content'] = array('like', '%'.$keyword.'%');
			}

            if($type!=''){
                $map['type'] = $type;
            }
            if($cityID!=''){
                $map['cityID'] = $cityID;
            }
            if($articleid!=''){
                $map['articleid'] = $articleid;
            }

			$obj = M('Comment');
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

	public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $id = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = M('Comment');
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

	
	public function del(){	
		$id = explode(",",I('post.id'));
		$this->all_del('Comment',$id);
    }
}
?>