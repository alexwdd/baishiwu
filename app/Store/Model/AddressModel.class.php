<?php
namespace Store\Model;
use Think\Model;

class AddressModel extends Model{

    protected $_validate = array (
        array('name', 'require', '请输入姓名'),
        array('mobile', 'require', '请输入手机号码'),
		array('address', 'require', '请输入详细地址'),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),
    );
}
?>