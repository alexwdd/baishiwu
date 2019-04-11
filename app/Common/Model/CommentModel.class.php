<?php
namespace Common\Model;
use Think\Model;

class CommentModel extends Model {

    protected $_validate = array (
		array('articleid','require','articleid不能为空', 1),
		array('type','require','type模块类型不能为空', 1),
		array('comments','require','评论内容不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('createIP', 'get_client_ip', 3, 'function'),
	);
}
?>