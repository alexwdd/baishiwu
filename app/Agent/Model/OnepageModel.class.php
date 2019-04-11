<?php
namespace Adminx\Model;
use Think\Model;

class OnepageModel extends Model {

    protected $_validate = array (
		array('title','require','标题不能为空', 1),
		array('content','require','内容不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>