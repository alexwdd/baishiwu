<?php
namespace Daigou\Model;
use Think\Model;

class GoodsAttributeModel extends Model {

    protected $_validate = array (
		array('name','require','属性名称不能为空', 1),
        array('typeID','require','模型不能为空', 1),
        array('values','require','可选值不能为空', 1),
        array('sort','require','排序不能为空', 1),
    );	

    protected $_auto = array ( //自动完成
        array('createTime','time',Model::MODEL_INSERT,'function'),      
        array('updateTime','time',Model::MODEL_BOTH,'function'),
    );
}
?>