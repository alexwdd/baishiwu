<?php
namespace Daigou\Model;
use Think\Model;

class DgGoodsModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('picname','require','图片不能为空', 1),
		array('price','require','服务费不能为空', 1),
		array('content','require','介绍不能为空', 1),
    );	

    protected $_auto = array (
        array('cid','_cid',Model::MODEL_BOTH,'callback'),
    	array('cid1','_cid',Model::MODEL_BOTH,'callback'),
        array('path','_path',Model::MODEL_BOTH,'callback'),
        array('path1','_path1',Model::MODEL_BOTH,'callback'),
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

	protected function _cid($value){
		$class = explode(',', $value);
		return $class[0];
    }

    protected function _path(){
		$class = explode(',', I('post.cid'));
		return $class[1];
    }

    protected function _path1(){
        $class = explode(',', I('post.cid1'));
        return $class[1];
    }

	/**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    protected function _after_insert($data, $options){

        // 商品规格价钱处理
        $spec_id = I("post.spec_id/a");
        $spec_name = I("post.spec_name/a");
        $spec_short = I("post.spec_short/a");
        $spec_cid = I("post.spec_cid/a");
        $spec_cid1 = I("post.spec_cid1/a");
        $spec_price = I("post.spec_price/a");
        $spec_number = I("post.spec_number/a");
        $spec_wuliu = I("post.spec_wuliu/a");
        $spec_tag = I("post.spec_tag/a");
        $spec_show = I("post.spec_show/a");

        $spec_data = array();   
        $baseData = array(
            'cid' => $data['cid'],
            'path' => $data['path'],
            'cid1' => $data['cid1'],
            'path1' => $data['path1'],
            'typeID' => $data['typeID'],
            'brandID' => $data['brandID'],
            'picname' => $data['picname'],
            'show' => $data['show'],
            'empty' => $data['empty'],
            'comm' => $data['comm'],
            'sort' => $data['sort'],
            'base' => 1,
            'goodsID' => $data['id'],
            'name' => $data['name'],
            'short' => $data['short'],
            'say' => $data['say'],
            'price' => $data['price'],
            'number' => $data['number'],
            'keyword' => $data['keyword'],
            'weight' => $data['weight'],
            'wuliuWeight' => $data['wuliuWeight'],
            'server' => $data['server'],
            'tag' => $data['tag'],
            'wuliu'=>$data['wuliu']
        );
        array_push($spec_data,$baseData);   

        for ($i=0; $i <count($spec_name) ; $i++) { 
            if($spec_name[$i]!=''){
                $scid = explode(',', $spec_cid[$i]);
                if ($spec_cid1[$i]!='') {
                    $scid1 = explode(',', $spec_cid1[$i]);
                }else{
                    $scid1 = [0,''];
                }
                $temp = $baseData;
                $temp['base'] = 0;
                $temp['cid'] = $scid[0];
                $temp['path'] = $scid[1];
                $temp['cid1'] = $scid1[0];
                $temp['path1'] = $scid1[1];
                $temp['name'] = $spec_name[$i];
                $temp['short'] = $spec_short[$i];
                $temp['price'] = $spec_price[$i];
                $temp['number'] = $spec_number[$i];
                $temp['wuliu'] = $spec_wuliu[$i];
                $temp['show'] = $spec_show[$i];
                $temp['tag'] = $spec_tag[$i];                
                array_push($spec_data,$temp);
            }
        } 
        M("DgGoodsIndex")->addAll($spec_data);
    }

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    protected function _after_update($data, $options){
        // 商品规格价钱处理
        $spec_id = I("post.spec_id/a");
        $spec_cid = I("post.spec_cid/a");
        $spec_cid1 = I("post.spec_cid1/a");
        $spec_name = I("post.spec_name/a");
        $spec_short = I("post.spec_short/a");
        $spec_price = I("post.spec_price/a");
        $spec_number = I("post.spec_number/a");
        $spec_wuliu = I("post.spec_wuliu/a");
        $spec_tag = I("post.spec_tag/a");
        $spec_show = I("post.spec_show/a");

        $baseData = array(
            'cid' => $data['cid'],
            'path' => $data['path'],
            'cid1' => $data['cid1'],
            'path1' => $data['path1'],
            'typeID' => $data['typeID'],
            'brandID' => $data['brandID'],
            'picname' => $data['picname'],
            'show' => $data['show'],
            'empty' => $data['empty'],
            'comm' => $data['comm'],
            'sort' => $data['sort'],
            'name' => $data['name'],
            'short' => $data['short'],
            'say' => $data['say'],
            'price' => $data['price'],
            'number' => $data['number'],
            'keyword' => $data['keyword'],
            'weight' => $data['weight'],
            'wuliuWeight' => $data['wuliuWeight'],
            'server' => $data['server'],
            'tag' => $data['tag'],
            'wuliu'=>$data['wuliu']
        ); 
        M("DgGoodsIndex")->where(array('goodsID'=>$data['id'],'base'=>1))->save($baseData);

        for ($i=0; $i <count($spec_name) ; $i++) { 
            if($spec_name[$i]!=''){
                $scid = explode(',', $spec_cid[$i]);
                if ($spec_cid1[$i]!='') {
                    $scid1 = explode(',', $spec_cid1[$i]);
                }else{
                    $scid1 = [0,''];
                }
                $temp = $baseData;
                $temp['base'] = 0;
                $temp['cid'] = $scid[0];
                $temp['path'] = $scid[1];
                $temp['cid1'] = $scid1[0];
                $temp['path1'] = $scid1[1];
                $temp['name'] = $spec_name[$i];
                $temp['short'] = $spec_short[$i];
                $temp['price'] = $spec_price[$i];
                $temp['number'] = $spec_number[$i];
                $temp['wuliu'] = $spec_wuliu[$i];
                $temp['show'] = $spec_show[$i];
                $temp['tag'] = $spec_tag[$i];
                $temp['goodsID'] = $data['id'];
              
                if ($spec_id[$i]!='') {
                    M("DgGoodsIndex")->where(array('id'=>$spec_id[$i]))->save($temp);
                }else{
                    M("DgGoodsIndex")->add($temp);
                }
            }
        } 
    }
}
?>