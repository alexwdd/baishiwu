<?php
namespace Daigou\Controller;

class GoodsController extends CommonController {

    #列表
    public function index() {
        if (IS_POST) {
            $map['agentID']=$this->user['id'];
            $cateArr = M('AgentCate')->where($map)->getField('id,name');
            unset($map);

            $path = I('path');
            $keyword  = I('keyword');
            if ($keyword!='') {
                $map['name'] = array('like','%'.$keyword.'%');
            }
            if($path!=''){
                $map['path'] = array('like', $path.'%');
            }

            $map['agentID'] = $this->user['id'];
            $obj = M('DgGoods');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['cate'] = $cateArr[$value['cid']];
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
            $map['agentID'] = $this->user['id'];
            $cate = M("AgentCate")->where($map)->order('path,id asc')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);
            $this->display();
        }           
    }

    public function add(){
        if($_POST){
            $this->all_add('DgGoods',U('Goods/index'));  
        }else{
            $map['agentID'] = $this->user['id'];
            $cate = M("AgentCate")->where($map)->order('path,id asc')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);

            $server = M("Server")->order("sort asc")->select();
            $this->assign('server', $server);

            unset($map);
            $map['agentID'] = $this->user['id'];
            $attr = M("GoodsAttribute")->where($map)->field('id,name,values')->order("sort asc")->select();
            foreach ($attr as $key => $value) {
                $attr[$key]['item'] = explode("\n", $value['values']);
            }
            $this->assign('attr', $attr);

            $wuliu = M("Wuliu")->order("sort asc")->select();
            $this->assign('wuliu', $wuliu);

            $this->assign('tag',C('GOODS_TAG'));
            $this->display();
        }
    }

    #编辑
    public function edit() {
        if ($_POST) {
            $this->all_save('DgGoods',U('Goods/index'));  
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('DgGoods');
            $map['agentID'] = $this->user['id'];
            $map['id'] = $id;
            $list = $obj->where($map)->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                if ($list['image']) {
                    $image = explode(",", $list['image']);
                }else{
                    $image = [];
                }
                $this->assign('image', $image);

                if ($list['server']) {
                    $list['server'] = explode(",", $list['server']);
                }
                $this->assign('list', $list);

                $spec = M("DgGoodsIndex")->where(array('goodsID'=>$list['id'],'base'=>0))->select();
                $this->assign('spec', $spec);

                unset($map);
                $map['agentID'] = $this->user['id'];
                $cate = M("AgentCate")->where($map)->order('path,id asc')->select();
                foreach ($cate as $key => $value) {
                    $count = count(explode('-', $value['path'])) - 3;
                    $cate[$key]['count'] = $count;
                }
                $this->assign('cate', $cate);

                $server = M("Server")->order("sort asc")->select();
                $this->assign('server', $server);

                unset($map);
                $map['agentID'] = $this->user['id'];
                $attr = M("GoodsAttribute")->where($map)->field('id,name,values')->order("sort asc")->select();
                foreach ($attr as $key => $value) {
                    $attr[$key]['item'] = explode("\n", $value['values']);
                }
                $this->assign('attr', $attr);

                $wuliu = M("Wuliu")->order("sort asc")->select();
                $this->assign('wuliu', $wuliu);

                $this->assign('tag',C('GOODS_TAG'));
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
            $obj = M('DgGoods');
            $list = $obj->where($map)->delete();
            if ($list) {
                $where['goodsID'] = array('in',$id);
                M("DgGoodsIndex")->where($where)->delete(); //商品规格
                //M("GoodsAttr")->where($where)->delete(); //商品属性
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    public function getSpec(){
        $wuliu = M("Wuliu")->order("sort asc")->select();
        $this->assign("wuliu",$wuliu);

        unset($map);
        $map['agentID'] = $this->user['id'];
        $cate = M("AgentCate")->where($map)->order('path,id asc')->select();
        foreach ($cate as $key => $value) {
            $count = count(explode('-', $value['path'])) - 3;
            $cate[$key]['count'] = $count;
        }
        $this->assign('cate', $cate);

        $this->assign('tag',C('GOODS_TAG'));
        $res = $this->fetch();        
        echo $res;
    }

    public function delspec()
    {
        if ($_POST) {
            M("DgGoodsIndex")->where(array('id'=>I('post.id')))->delete();
        }
    }
}
?>