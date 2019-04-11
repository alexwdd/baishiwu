<?php
namespace Adminx\Model;
use Think\Model;

class OptionCateModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('name', '', '名称已经存在！', 0, 'unique', 1),
		/*array('value','require','英文不能为空', 1),
		array('value', '', '英文名称已经存在！', 0, 'unique', 1),*/
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);

}
?>