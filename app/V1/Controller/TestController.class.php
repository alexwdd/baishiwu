<?php
namespace V1\Controller;

class TestController extends CommonController {

	public function index(){
		foreach (C('infoArr') as $key => $value) {
			unset($map);
			$obj = M($value['db']);
			$map['html'] = array('neq','');
			$list = $obj->where($map)->select();
			foreach ($list as $key => $value) {
				$html = str_replace("//","/",$value['html']);
				$obj->where(array('articleid'=>$value['articleid']))->setField('html',$html);
			}
		}
	
    }
}