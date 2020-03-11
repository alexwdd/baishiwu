<?php
namespace Adminx\Model;
use Think\Model;

class WaterModel extends Model {

    protected $_validate = array (
		array('water_image','require','图片不能为空', 1),	
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>