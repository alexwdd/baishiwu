<?php
namespace Adminx\Model;
use Think\Model;

class QuestionItemModel extends Model {

    protected $_validate = array (
		array('name','require','请输入选项', 1 , unique , Model::MODEL_INSERT),
		array('status','require','请选择选项是否正确', 1 , 0 , Model::MODEL_BOTH),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),	
	);

}
?>