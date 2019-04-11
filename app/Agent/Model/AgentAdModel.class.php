<?php
namespace Agent\Model;
use Think\Model;

class AgentAdModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('image','require','图片不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>