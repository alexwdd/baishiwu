<?php
namespace Adminx\Model;
use Think\Model;
use Think\Model\AdvModel;

class UserModel extends AdvModel{

    protected $readonlyField = array('username');

    protected $_validate = array (
		array('username','require','用户名不能为空',1,0,Model::MODEL_INSERT), 
        array('username','','用户名已经存在！',1,'unique', Model::MODEL_INSERT),
        array('password','require','密码不能为空', 1 , 0 , Model::MODEL_INSERT),
        array('group','require','用户组不能为空', 1),
        array('status','require','状态不能为空', 1),
    );	

    protected $_auto = array (
        array('createTime','time',Model::MODEL_INSERT,'function'),      
        array('updateTime','time',Model::MODEL_BOTH,'function'),
        array('password','md5',Model::MODEL_INSERT,'function'),
    );
}
?>