<?php
namespace Adminx\Controller;

class CommentController extends AdminController {

    #列表
    public function index() {
        if (IS_POST) {
            $createDate  = I('createDate');
            $keyword  = I('keyword');
            if ($keyword!='') {
                $map['content'] = array('like','%'.$keyword.'%');
            }
            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            $obj = M('ChatComment');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();
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
        $obj = M('ChatComment');
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

    
    #删除
    public function del() {
        $articleid=I('post.id');
        if($articleid==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$articleid);
            $obj = M('ChatComment');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>