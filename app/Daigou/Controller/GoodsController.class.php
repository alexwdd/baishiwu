<?php
namespace Daigou\Controller;

class GoodsController extends CommonController {

    #列表
    public function index() {
        if (IS_POST) {
            $map['agentID']=$this->user['id'];
            $cateArr = M('DaigouCate')->where($map)->getField('id,name');
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
            $obj = M('Goods');
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
            $cate = M("DaigouCate")->where($map)->order('path,id asc')->select();
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
            $this->all_add('Goods',U('Goods/index'));  
        }else{
            $map['agentID'] = $this->user['id'];
            $cate = M("DaigouCate")->where($map)->order('path,id asc')->select();
            foreach ($cate as $key => $value) {
                $count = count(explode('-', $value['path'])) - 3;
                $cate[$key]['count'] = $count;
            }
            $this->assign('cate', $cate);

            $server = M("Server")->where($map)->order("sort asc")->select();
            $this->assign('server', $server);
            $this->display();
        }
    }

    #编辑
    public function edit() {
        if ($_POST) {
            $this->all_save('Goods',U('Goods/index'));  
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('Goods');
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

                unset($map);
                $map['agentID'] = $this->user['id'];
                $cate = M("DaigouCate")->where($map)->order('path,id asc')->select();
                foreach ($cate as $key => $value) {
                    $count = count(explode('-', $value['path'])) - 3;
                    $cate[$key]['count'] = $count;
                }
                $this->assign('cate', $cate);

                $server = M("Server")->where($map)->order("sort asc")->select();
                $this->assign('server', $server);
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
            $obj = M('Goods');
            $list = $obj->where($map)->delete();
            if ($list) {
                $where['goods_id'] = array('in',$id);
                M("spec_goods_price")->where($where)->delete();  //商品规格
                M("goods_attr")->where($where)->delete();  //商品属性
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = I('get.goods_id') ? I('get.goods_id') : 0;        
        $spec_type = I('get.spec_type') ? I('get.spec_type') : 0;        
        $specList = M('Spec')->where("typeID = ".$spec_type)->order('sort asc')->select();
        foreach($specList as $k => $v){
            $specList[$k]['spec_item'] = M('SpecItem')->field('id,item')->where("specID = ".$v['id'])->select(); // 获取规格项  
        }                
        
        $items_id = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);
        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        echo $this->fetch('ajax_spec_select');        
    }

    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     * @param int $goods_id 商品id
     * @param int $type_id 商品属性类型id
     */
    public function ajaxGetAttrInput(){
        $goods_id = I('get.goods_id') ? I('get.goods_id') : 0;        
        $type_id = I('get.type_id') ? I('get.type_id') : 0;        
        $attr = M('GoodsAttribute')->where("typeID = ".$type_id)->order('sort asc')->select();
        $goods_attr = M('GoodsAttr')->field('attr_id,attr_value')->where("goods_id=".$goods_id)->select();
        foreach ($attr as $key => $value) {
            $attr[$key]['item'] = explode("\n", $value['values']);
            foreach ($goods_attr as $k => $v) {
                if ($v['attr_id'] == $value['id']) {
                    $attr[$key]['select'] = $v['attr_value'];
                }
            }
        }
        $this->assign('attr',$attr);        
        echo $this->fetch('ajax_attr_select'); 
    }

    /**
     * 获取 tp_goods_attr 表中指定 goods_id  指定 attr_id  或者 指定 goods_attr_id 的值 可是字符串 可是数组
     * @param int $goods_attr_id tp_goods_attr表id
     * @param int $goods_id 商品id
     * @param int $attr_id 商品属性id
     * @return array 返回数组
     */
    public function getGoodsAttrVal($goods_attr_id = 0 ,$goods_id = 0, $attr_id = 0)
    {
        $GoodsAttr = M('GoodsAttr');        
        if($goods_attr_id > 0)
            return $GoodsAttr->where("goods_attr_id = $goods_attr_id")->select(); 
        if($goods_id > 0 && $attr_id > 0)
            return $GoodsAttr->where("goods_id = $goods_id and attr_id = $attr_id")->select();        
    }   

    public function getModel(){
        $id = I('post.id');
        $attr = M('GoodsAttribute')->where(['typeID'=>$id])->select();
        foreach ($attr as $key => $value) {
            $attr[$key]['item'] = explode("\n", $value['values']);
        }
        $spec = M('Spec')->field('id,name')->where(['typeID'=>$id])->order('sort asc')->select();
        foreach ($spec as $key => $value) {
            $spec[$key]['item'] = M('SpecItem')->where(['specID'=>$value['id']])->select();
        }
        echo json_encode(['attr'=>$attr,'spec'=>$spec]);
    }

    public function ajaxGetSpecInput(){
        $goods_id = I('goods_id/d') ? I('goods_id/d') : 0;
        $str = $this->getSpecInput($goods_id ,I('post.spec_arr',[[]]));
        exit($str);   
    }

    public function getSpecInput($goods_id, $spec_arr)
    {
        // 排序
        foreach ($spec_arr as $k => $v)
        {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);        
        foreach ($spec_arr_sort as $key =>$val)
        {
            $spec_arr2[$key] = $spec_arr[$key];
        }     
        
        $clo_name = array_keys($spec_arr2);         
        $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积                 
                   
        $spec = M('Spec')->getField('id,name'); // 规格表
        $specItem = M('SpecItem')->getField('id,item,specID');//规格项
        $keySpecGoodsPrice = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,number,store_count,bar_code,weight,isBaoyou,fencheng1,fencheng2');//规格项                          
        $str = "<table class='layui-table' id='spec_input_tab'>";
        $str .="<tr>";       
        // 显示第一行的数据
        foreach ($clo_name as $k => $v) 
        {
            $str .=" <td><b>{$spec[$v]}</b></td>";
        }    
        $str .="<td><b>价格</b></td>
               <td><b>单品数量</b></td>
               <td><b>库存</b></td>
               <td><b>重量(kg)</b></td>
               <td><b>包邮</b></td>
             </tr>";
        // 显示第二行开始 
        foreach ($spec_arr2 as $k => $v) 
        {
            $str .="<tr>";
            $item_key_name = array();
            foreach($v as $k2 => $v2)
            {
                $str .="<td>{$specItem[$v2]['item']}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['specID']].':'.$specItem[$v2]['item'];
            }   
            ksort($item_key_name);            
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);
            
            $keySpecGoodsPrice[$item_key]['price'] ? false : $keySpecGoodsPrice[$item_key]['price'] = 0; // 价格默认为0
            $keySpecGoodsPrice[$item_key]['store_count'] ? false : $keySpecGoodsPrice[$item_key]['store_count'] = 0; //库存默认为0
            $keySpecGoodsPrice[$item_key]['number'] ? false : $keySpecGoodsPrice[$item_key]['number'] = 1; //默认数量为1
            $str .="<td><input class='layui-input' name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .="<td><input class='layui-input' name='item[$item_key][number]' value='{$keySpecGoodsPrice[$item_key][number]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
            $str .="<td><input class='layui-input' name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";            
            $str .="<td><input class='layui-input' name='item[$item_key][weight]' value='{$keySpecGoodsPrice[$item_key][weight]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/><input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="<td><select name='item[$item_key][isBaoyou]' class='layui-input'><option value='0' ";
            if ($keySpecGoodsPrice[$item_key][isBaoyou]==0) {
              $str .= "selected";
            }
            $str .= ">否</option><option value='1' ";
            if ($keySpecGoodsPrice[$item_key][isBaoyou]==1) {
              $str .= "selected";
            }
            $str .= ">是</option></select></td>";
            //$str .="<td><input class='layui-input' name='item[$item_key][fencheng1]' value='{$keySpecGoodsPrice[$item_key][fencheng1]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/><input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            //$str .="<td><input class='layui-input' name='item[$item_key][fencheng2]' value='{$keySpecGoodsPrice[$item_key][fencheng2]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/><input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="</tr>";
        }
        $str .= "</table>";
        return $str;   
    }
}
?>