<?php
namespace Adminx\Model;
use Think\Model;

class PushCityModel extends Model {

    protected $_validate = array (
		array('city','require','城市不能为空', 1),
		array('key','require','APP_KEY不能为空', 1),
		array('secret','require','APP_SECRET不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>