<?php
namespace Adminx\Model;
use Think\Model;

class ServerModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('price','require','价格不能为空', 1),
		
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>