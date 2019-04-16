<?php
namespace V1\Controller;

class CategoryController extends CommonController {

	public function getModel($type){
		foreach (C('infoArr') as $key => $value) {
			if ($value['py'] == $type) {
	            return $value;
	        }
		}
		return false;
	}

	public function infocate(){
		if (!checkFormDate()){
			returnJson('-1','未知错误');
		}
		$type = I('type');
		$cityID = I('cityID');
		$arr = $this->getModel($type);
    	if (!$arr) {
    		returnJson('-1','type类型错误');
    	}
    	$cityID = I('post.cityID');
		if ($cityID=='' || !is_numeric($cityID)) {
			returnJson('-1','缺少cityID');
		}
    	$map['fid'] = $arr['fid'];
    	$map['cityID'] = $cityID;
		$list = M("CityCate")->where($map)->field("cid as id ,name as title,icon")->order('sort asc,id asc')->select();
		foreach ($list as $key => $value) {
			$list[$key]['icon'] = getRealUrl($value['icon']);
		}
		return returnJson('0',C('SUCCESS_RETURN'),array('sort'=>$list));
    } 

	public function option(){
		if (!checkFormDate()){
			returnJson('-1','未知错误');
		}
		$list = M("OptionCate")->field("id as cateid,name,value")->select();
		return returnJson('0',C('SUCCESS_RETURN'),$list);
    }  

    public function item(){	
		if (!checkFormDate()){
			returnJson('-1','未知错误');
		}
		$cate = I('cateid');
		if ($cate=='' || !is_numeric($cate)) {
			returnJson('-1','cateid参数不正确，应为数字');
		}

		$list = M("OptionItem")->where(array('cate'=>$cate))->field("id as itemid,name,value")->select();
		return returnJson('0',C('SUCCESS_RETURN'),$list);
    }  

    public function subway(){
    	if (!checkFormDate()){
			returnJson('-1','未知错误');
		}
    	$cityID = I('post.cityID');
		if ($cityID=='' || !is_numeric($cityID)) {
			returnJson('-1','缺少cityID');
		}
    	$map['fid'] = 0;
    	$map['cityID'] = $cityID;
		$list = M("Subway")->where($map)->field("id as value,name as text")->order('sort asc,id asc')->select();
		foreach ($list as $key => $value) {
			$list[$key]['children'] = M("Subway")->where(array('fid'=>$value['value']))->field("id as value,name as text")->order('sort asc,id asc')->select();
		}
		return returnJson('0',C('SUCCESS_RETURN'),array('subway'=>$list));
    }
}