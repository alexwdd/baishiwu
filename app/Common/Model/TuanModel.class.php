<?php
namespace Common\Model;
use Think\Model;

class TuanModel extends Model {

    protected $_validate = array (
		array('articleid','require','articleid不能为空', 1,'number',2),
		//array('title','require','标题不能为空', 1),
		array('cityID','require','请选择城市', 1),
		array('goodstype','require','货物类型不能为空', 1),
		array('maxWeight','is_numeric','包裹最大重量不能为空，且必须位数字',1,'function'),
		//array('maxNumber','number','人数上限不能为空，且必须位数字', 1),
		array('address','require','取货地址不能为空', 1),
		array('contact','require','联系人不能为空', 1),
		array('phone','require','联系电话不能为空', 1),	
		array('wechat','require','微信号不能为空', 1),	
		array('detail','require','请输入拼邮描述', 1),	
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);
}
?>