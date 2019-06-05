<?php
namespace Daigou\Controller;

class GoodsSpecController extends CommonController {

    #列表
    public function index() {
        if (IS_POST) {
            $keyword  = I('keyword');
            if ($keyword!='') {
                $map['name'] = array('like','%'.$keyword.'%');
            }

            $map['agentID'] = $this->user['id'];
            $obj = M('Spec');
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

                $item = M('SpecItem')->where(array('specID'=>$value['id']))->getField('item',true);
                $item = implode(",", $item);
                $list[$key]['item'] = $item;
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

    public function add(){
        if($_POST){
            $this->all_add('Spec',U('GoodsSpec/index'));  
        }else{
            $this->display();
        }
    }

    #编辑
    public function edit() {
        if ($_POST) {
            $this->all_save('Spec',U('GoodsSpec/index'));  
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('Spec');
            $map['agentID'] = $this->user['id'];
            $map['id'] = $id;
            $list = $obj->where($map)->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                $item = M('SpecItem')->where(array('specID'=>$list['id']))->getField('item',true);
                $item = implode("\n", $item);
                $list['item'] = $item;
                $this->assign('list', $list); 
                $this->display();
            }
        }
    }

    #删除
    public function del() {
        $id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('Spec');
            $list = $obj->where($map)->delete();
            if ($list) {
                $map['specID'] = array('in',$id);
                M("SpecItem")->where($map)->delete();
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }
}
?>