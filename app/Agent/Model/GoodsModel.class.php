<?php
namespace Agent\Model;
use Think\Model;

class GoodsModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('picname','require','图片不能为空', 1),
		array('price','require','服务费不能为空', 1),
		array('content','require','介绍不能为空', 1),
    );	

    protected $_auto = array (
    	array('cid','_cid',Model::MODEL_BOTH,'callback'),
        array('path','_path',Model::MODEL_BOTH,'callback'),
        array('image','_image',Model::MODEL_BOTH,'callback'),
		array('server','_server',Model::MODEL_BOTH,'callback'),
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);

    protected function _server(){
        if (I('post.server')!='') {
            return implode(",", I('post.server'));
        }else{
            return '';
        }
    }

    protected function _image(){
        if (I('post.image')!='') {
            return implode(",", I('post.image'));
        }else{
            return '';
        }
    }

	protected function _cid(){
		$class = explode(',', I('post.cid'));
		return $class[0];
    }

    protected function _path(){
		$class = explode(',', I('post.cid'));
		return $class[1];
    }

	/**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    protected function _after_insert($data, $options){
        $goods_id = $data['id'];    
        // 商品规格价钱处理
        $goods_item = I('item');
        $eidt_goods_id = I('goods_id');
        $specStock = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,store_count');
        if ($goods_item) {
            $keyArr = '';//规格key数组
            foreach ($goods_item as $k => $v) {
                $keyArr .= $k.',';
                // 批量添加数据
                $v['price'] = trim($v['price']);
                $store_count = $v['store_count'] = trim($v['store_count']); // 记录商品总库存
                $v['weight'] = trim($v['weight']);
                $v['number'] = trim($v['number']);
                $v['fencheng1'] = trim($v['fencheng1']);
                $v['fencheng2'] = trim($v['fencheng2']);
                $v['isBaoyou'] = trim($v['isBaoyou']);
                $data = ['goods_id' => $goods_id, 'key' => $k, 'key_name' => $v['key_name'], 'price' => $v['price'], 'number' => $v['number'],'store_count' => $v['store_count'], 'weight' => $v['weight'],'isBaoyou' => $v['isBaoyou'],'fencheng1' => $v['fencheng1'],'fencheng2' => $v['fencheng2']];
                
                if ($item_img) {
                    $spec_key_arr = explode('_', $k);
                    foreach ($item_img as $key => $val) {
                        if (in_array($key, $spec_key_arr)) {
                            $data['spec_img'] = $val;
                            break;
                        }
                    }
                }
                
                if (!empty($specStock[$k])) {
                    M('SpecGoodsPrice')->where(['goods_id' => $goods_id, 'key' => $k])->save($data);
                } else {
                    M('SpecGoodsPrice')->add($data);
                }
                
                if(!empty($specStock[$k]) && $v['store_count'] != $specStock[$k] && $eidt_goods_id>0){
                    $stock = $v['store_count'] - $specStock[$k];
                }else{
                    $stock = $v['store_count'];
                }
            }
            if($keyArr){
                $where['key'] = array('not in',$keyArr);
                M('SpecGoodsPrice')->where(['goods_id'=>$goods_id])->where($where)->delete();
            }
        }

        $this->saveGoodsAttr($goods_id,I('typeID'));
    }

    public function saveGoodsAttr($goods_id,$goods_type)
    {  
        $GoodsAttr = M('GoodsAttr');                
        // 属性类型被更改了 就先删除以前的属性类型 或者没有属性 则删除        
        if($goods_type == 0)
        {
            $GoodsAttr->where('goods_id = '.$goods_id)->delete(); 
            return;
        }
        
        $GoodsAttrList = $GoodsAttr->where('goods_id = '.$goods_id)->select();

        $old_goods_attr = array(); // 数据库中的的属性  以 attr_id _ 和值的 组合为键名
        foreach($GoodsAttrList as $k => $v)
        {                
            $old_goods_attr[$v['attr_id'].'_'.$v['attr_value']] = $v;
        }            
                          
        // post 提交的属性  以 attr_id _ 和值的 组合为键名    
        $post_goods_attr = array();
        $post = I("post.");
        foreach($post as $k => $v)
        {
            $attr_id = str_replace('attr_','',$k);
            if(!strstr($k, 'attr_') || strstr($k, 'attr_price_')){
               continue;                                 
            }
            foreach ($v as $k2 => $v2)
            {                      
               $v2 = str_replace('_', '', $v2); // 替换特殊字符
               $v2 = str_replace('@', '', $v2); // 替换特殊字符
               $v2 = trim($v2);
               
               if(empty($v2)){
                   continue;
               }
               
               $tmp_key = $attr_id."_".$v2;
               $post_attr_price = I("post.attr_price_{$attr_id}");
               $attr_price = $post_attr_price[$k2]; 
               $attr_price = $attr_price ? $attr_price : 0;
               if(array_key_exists($tmp_key , $old_goods_attr)) // 如果这个属性 原来就存在
               {   
                   if($old_goods_attr[$tmp_key]['attr_price'] != $attr_price) // 并且价格不一样 就做更新处理
                   {                       
                        $goods_attr_id = $old_goods_attr[$tmp_key]['goods_attr_id'];                         
                        $GoodsAttr->where("goods_attr_id = $goods_attr_id")->save(array('attr_price'=>$attr_price));                       
                   }
               }else{ // 否则这个属性 数据库中不存在 说明要做删除操作               
                   $GoodsAttr->add(array('goods_id'=>$goods_id,'attr_id'=>$attr_id,'attr_value'=>$v2,'attr_price'=>$attr_price));                       
               }
               unset($old_goods_attr[$tmp_key]);
           }            
        }     
        // 没有被 unset($old_goods_attr[$tmp_key]); 掉是 说明 数据库中存在 表单中没有提交过来则要删除操作
        foreach($old_goods_attr as $k => $v)
        {                
           $GoodsAttr->where('goods_attr_id = '.$v['goods_attr_id'])->delete(); // 
        }
    }

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    protected function _after_update($data, $options){
        $goods_id = $data['id'];
        // 商品规格价钱处理
        $goods_item = I('item');
        $eidt_goods_id = I('goods_id');
        $specStock = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,store_count');
        if ($goods_item) {
            $keyArr = '';//规格key数组
            foreach ($goods_item as $k => $v) {
                $keyArr .= $k.',';
                // 批量添加数据
                $v['price'] = trim($v['price']);
                $store_count = $v['store_count'] = trim($v['store_count']); // 记录商品总库存
                $v['weight'] = trim($v['weight']);
                $v['number'] = trim($v['number']);
                $v['fencheng1'] = trim($v['fencheng1']);
                $v['fencheng2'] = trim($v['fencheng2']);
                $v['isBaoyou'] = trim($v['isBaoyou']);
                $data = ['goods_id' => $goods_id, 'key' => $k, 'key_name' => $v['key_name'], 'price' => $v['price'], 'store_count' => $v['store_count'], 'number' => $v['number'], 'weight' => $v['weight'],'isBaoyou' => $v['isBaoyou'],'fencheng1' => $v['fencheng1'],'fencheng2' => $v['fencheng2']];
                
                if ($item_img) {
                    $spec_key_arr = explode('_', $k);
                    foreach ($item_img as $key => $val) {
                        if (in_array($key, $spec_key_arr)) {
                            $data['spec_img'] = $val;
                            break;
                        }
                    }
                }
                
                if ($specStock[$k]) {
                    M('SpecGoodsPrice')->where(['goods_id' => $goods_id, 'key' => $k])->save($data);
                } else {
                    M('SpecGoodsPrice')->add($data);
                }
            }
            if($keyArr){
                $where['key'] = array('not in',$keyArr);
                M('SpecGoodsPrice')->where(['goods_id'=>$goods_id])->where($where)->delete();
            }
        }

        $this->saveGoodsAttr($goods_id,I('typeID'));
    }
}
?>