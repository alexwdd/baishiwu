<?php
namespace V1\Controller;

class AutoController extends CommonController {

	public function index(){
		foreach (C('infoArr') as $key => $value) {
			unset($map);
			$obj = M($value['db']);
			$map['isTop'] = 1;
			$list = $obj->where($map)->select();
			foreach ($list as $key => $value) {
				if ($value['showTime'] < time()) {
					$obj->where(array('articleid'=>$value['articleid']))->setField('isTop',0);
				}
			}
		}
    }
}