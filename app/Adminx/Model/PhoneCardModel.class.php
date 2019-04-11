<?php
namespace Adminx\Model;
use Think\Model;

class PhoneCardModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
        array('type','require','类型不能为空', 1),
		array('picname','require','图片不能为空', 1),
        array('money','require','充值金额不能为空', 1),
		array('price','require','价格不能为空', 1),
		
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>