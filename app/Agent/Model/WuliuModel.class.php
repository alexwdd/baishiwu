<?php
namespace Agent\Model;
use Think\Model;

class WuliuModel extends Model {

    protected $_validate = array (
		array('name','require','请输入物流名称', 1),
		array('url','require','查询网址不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function')	
	);	
}
?>