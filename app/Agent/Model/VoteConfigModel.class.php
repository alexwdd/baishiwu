<?php
namespace Adminx\Model;
use Think\Model;

class VoteConfigModel extends Model {

    protected $_validate = array (
		array('restrictIP','require','IP选项不能为空', 1),
		array('restrictTime','require','时间间隔不能为空', 1),
		array('canView','require','查看票数不能为空', 1),
    );	

}
?>