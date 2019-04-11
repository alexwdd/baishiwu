<?php
namespace Adminx\Controller;

class CommendController extends AdminController {

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
                $map['title'] = array('like','%'.$keyword.'%');
            }
            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            $obj = M('Commend');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order('sort asc,id desc')->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                if ($value['type']=='article') {
                    $list[$key]['typeName'] = '文章';
                }else{
                    $db = $this->getModel($value['type']);
                    $list[$key]['typeName'] = $db['name'];
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

    public function getModel($type){
        foreach (C('infoArr') as $key => $value) {
            if ($value['py'] == $type) {
                return $value;
            }
        }
        return false;       
    }

    public function add(){
        if($_POST){
            $type = I('post.type');
            $articleid = I('post.id');
            if ($type=='' || $articleid=='') {
                $this->error("参数错误");
            }

            if ($type=='article') {
                $db['db'] = 'article';
                $map['id'] = $articleid;  
            }else{
                $db = $this->getModel($type);
                if (!$db) {
                    $this->error("type类型错误");
                }
                $map['articleid'] = $articleid;  
            }                   
            $list = $obj = M($db['db'])->where($map)->find();
            if (!$list) {
                $this->error("文章不存在");
            }
            $obj = M('Commend');

            $data['type'] = $type;
            $data['articleid'] = $articleid;
            $data['cityID'] = $list['cityID'];
            $data['title'] = $list['title'];
            if ($type=='article') {
                $data['image'] = $list['picname'];
                $data['userid'] = 0;
            }else{
                $data['image'] = $list['thumb_b'];
                $data['userid'] = $list['userid'];
            }
            $data['hit'] = $list['hit'];
            
            unset($map);
            $map['articleid'] = $articleid;
            $map['type'] = $type;
            $r = $obj->where($map)->find();
            if ($r) {
                $res = $obj->where($map)->save($data);
            }else{
                $data['sort'] = 50;
                $res = $obj->add($data);
            }
            if ($res) {
                $this->error("操作成功");
            }else{
                $this->error("操作失败");
            }
        }
    }

    #编辑
    public function edit() {
        if ($_POST) {
            $map['id'] = I('post.id');
            $data[I('post.field')] = I('post.val');
            M('Commend')->where($map)->save($data);
        }
    }   

    #删除
    public function del() {
        $articleid=I('post.id');
        if($articleid==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$articleid);
            $obj = M('Commend');
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