<?php
namespace Adminx\Controller;

class VoteController extends AdminController{

    public function index(){
        if (IS_POST) {            
            $obj = M('VoteSubject');
            $total = $obj->count();
            $pageSize = I('post.pageSize',20);
            $pageNum = I('post.pageNum',1);

            $pages = ceil($total/$pageSize);            
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order('id desc')->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['endTime'] = date("Y-m-d",$value['endTime']);
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
            $this->all_add('VoteSubject',U('Vote/index'));
        }else{
            $this->display();
        }
    }

    #编辑
    public function edit() {
        if($_POST){
            $this->all_save('VoteSubject',U('Vote/index'));
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('VoteSubject');
            $map['id']=$id;
            $list = $obj->where($map)->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                $list['endTime'] = date("Y-m-d",$list['endTime']);
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
                $obj = M('VoteSubject');
                $where['id'] = $v;
                $obj->where($where)->delete();
                M('VoteItem')->where('voteId='.$v)->delete();
            }
            $this->success('操作成功',U('Vote/index'));
        }
    }

    #列表
    public function item() {
        if (IS_POST) {
            $voteId = I('get.voteId');
            $map['voteId'] = $voteId;
            $obj = M('VoteItem');
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
            $voteId = I('get.voteId');
            $map['id'] = $voteId;
            $vote = M('VoteSubject')->where($map)->find();
            if ($vote) {
                $this->assign('vote',$vote);
                $this->display();
            }else{
                $this->error('主题不存在');
            }
        }               
    }

    #添加
    public function addItem() {
        if($_POST){
            $this->all_add('VoteItem');
        }else{
            $voteId = I('get.voteId');
            $this->assign('voteId',$voteId);
            $this->display();
        }       
    }

    #编辑
    public function editItem() {
        if($_POST){
            $this->all_save('VoteItem');
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('VoteItem');
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
    public function delItem() {
        $id = explode(",",I('post.id'));
        $this->all_del('VoteItem',$id);
    }
}