<?php
namespace Agent\Model;
use Think\Model;

class AgentArticleModel extends Model {

    protected $_validate = array (
		array('title','require','请输入标题', 1),
		array('content','require','内容不能为空', 1),
        array('createTime','require','发布日期不能为空', 1),
    );	

    protected $_auto = array (	
 		array('createTime','strtotime',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('hit','131',Model::MODEL_INSERT,'string')      
	);
}
?>