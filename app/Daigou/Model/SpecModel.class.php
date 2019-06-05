<?php
namespace Daigou\Model;
use Think\Model;

class SpecModel extends Model {

    protected $_validate = array (//字段验证
    	array('name','require','属性名称不能为空', 1),
    	array('typeID','require','模型不能为空', 1),
    	array('values','require','可选值不能为空', 1),
    	array('sort','require','排序不能为空', 1)
    );	

    protected $_auto = array ( //自动完成
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);

    protected function _after_insert($data, $options) {
        $values = I('values');
        $itemData = [];
        $itemArr = explode("\n", trim($values));
        for ($i=0; $i <count($itemArr) ; $i++) {
            if ($itemArr[$i]!='') {
                array_push($itemData, ['specID'=>$data['id'],'item'=>$itemArr[$i]]);
            }                
        }
        M('SpecItem')->addAll($itemData);
    }

    protected function _after_update($data, $options) {
        $values = I('values');
        $itemData = [];
        $itemArr = explode("\n", trim($values));
        for ($i=0; $i <count($itemArr) ; $i++) {
            if ($itemArr[$i]!='') {
                array_push($itemData, ['specID'=>$data['id'],'item'=>$itemArr[$i]]);
            }                
        }
        M('SpecItem')->where(array('specID'=>$data['id']))->delete();
        M('SpecItem')->addAll($itemData);
    }
}
?>