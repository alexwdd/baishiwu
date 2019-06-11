<?php
namespace Adminx\Model;
use Think\Model;

class WuliuModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('price','require','价格不能为空', 1),
		array('weight','require','重量不能为空', 1),
		array('otherPrice','require','偏远地区收费不能为空', 1),
		
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>