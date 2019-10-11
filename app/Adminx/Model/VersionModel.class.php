<?php
namespace Adminx\Model;
use Think\Model;

class VersionModel extends Model {

    protected $_validate = array (
		array('version','require','版本号不能为空', 1),
		array('type','require','类型不能为空', 1),
		array('url','require','更新地址不能为空', 1),
		array('cityID','require','请选择城市', 1),
		
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>