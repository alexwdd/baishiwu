<?php
namespace Adminx\Model;
use Think\Model;

class ActivityModel extends Model {

    protected $_validate = array (//字段验证
    	array('name','require','请输入活动名称', 1),
    	array('type','require','请选择类型', 1),
    	array('totalNumber','require','请输入数量', 1),
    );	

    protected $_auto = array ( //自动完成
    	array('startTime','strtotime',Model::MODEL_BOTH,'function'),
		array('endTime','strtotime',Model::MODEL_BOTH,'function'),
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>