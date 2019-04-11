<?php
namespace Adminx\Model;
use Think\Model;

class PushModel extends Model {

    protected $_validate = array (
		array('city','require','城市不能为空', 1),
		array('type','require','类型不能为空', 1),
		array('title','require','推送内容不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>