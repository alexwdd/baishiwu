<?php
namespace Daigou\Model;
use Think\Model;

class ServerModel extends Model {

    protected $_validate = array (
		array('name','require','请输入名称', 1),
		array('price','require','请输入价格', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function')	
	);	
}
?>