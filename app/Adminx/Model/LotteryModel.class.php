<?php
namespace Adminx\Model;
use Think\Model;

class LotteryModel extends Model {

    protected $_validate = array (//字段验证
    	array('name','require','请输入活动名称', 1),
    );	

    protected $_auto = array ( //自动完成
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>