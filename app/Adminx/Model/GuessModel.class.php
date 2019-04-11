<?php
namespace Adminx\Model;
use Think\Model;

class GuessModel extends Model {

    protected $_validate = array (//字段验证
    	array('question','require','请输入问题', 1),
    	array('intr','require','请输入解释', 1),
    	array('point','require','请输入积分', 1),
    	array('true','require','请选择真假', 1),
        array('show','require','请选择状态', 1),
    );	

    protected $_auto = array ( //自动完成
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>