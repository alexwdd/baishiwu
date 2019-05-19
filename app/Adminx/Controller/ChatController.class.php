<?php
namespace Adminx\Controller;

class ChatController extends AdminController {

    #列表
    public function index() {
        if (IS_POST) {
            $createDate  = I('createDate');
            $type  = I('type');
            $keyword  = I('keyword');

            if ($type!='') {
                $map['type'] = $type;
            }
            if ($keyword!='') {
                $map['content'] = array('like','%'.$keyword.'%');
            }
            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            $obj = M('Chat');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                if ($value['type']==1) {
                    $list[$key]['typeName'] = '图文';
                }else{
                    $list[$key]['typeName'] = '小视频';
                }
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

    public function image(){
        $id = (int) $_GET['id'];
        if (!isset ($id)) {
            $this->error('参数错误');
        }
        $obj = M('Chat');
        $list = $obj->where("id=$id")->find();
        if (!$list) {
            $this->error('信息不存在');
        } else {
            if ($list['images']) {
                $list['images'] = explode("|", $list['images']);
            }
            $this->assign('list', $list);
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
        $obj = M('Chat');
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
            $obj = M('Chat');
            $list = $obj->where($map)->delete();
            if ($list) {
                unset($map);
                $map['chatID'] = array('in',$articleid);
                M('ChatComment')->where($map)->delete();
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>