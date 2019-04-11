<?php
namespace Common\Model;
use Think\Model;

class TuanDetailModel extends Model {

    protected $_validate = array (
    	//array('title','require','标题不能为空', 1),		
		array('articleid','require','articleid不能为空',0,'number',1),
		array('company','require','国内物流公司不能为空',1,'',1),
		array('order','require','国内物流单号不能为空', 1,'',1),
		array('contact','require','联系人不能为空', 1,'',1),
		array('phone','require','联系电话不能为空', 1,'',1),	
		array('detail','require','请输入成员留言', 1,'',1),	
		array('weight','is_numeric','包裹预估重量不能为空，且必须位数字','1','function')
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>