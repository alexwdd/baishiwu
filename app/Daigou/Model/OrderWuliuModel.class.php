<?php
namespace Daigou\Model;
use Think\Model;

class OrderWuliuModel extends Model {

    protected $_validate = array (
		array('wuliu','require','请选物流', 1),
		array('number','require','请输入物流单号', 1),
    );	

   protected $_auto = array (
		array('image','_image',Model::MODEL_BOTH,'callback'),
		//array('createTime','time',Model::MODEL_BOTH,'function'),		
		//array('updateTime','time',Model::MODEL_BOTH,'function'),		
	);
    
	protected function _image(){
		if (I('post.image')!='') {
            return implode(",", I('post.image'));
        }else{
        	return "";
        }
    }
}
?>