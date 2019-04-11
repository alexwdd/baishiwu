<?php
namespace Common\Model;
use Think\Model;

class TongchengModel extends Model {

    protected $_validate = array (
		array('articleid','require','articleid不能为空', 1,'number',2),
		array('title','require','标题不能为空', 1,'',1),
		array('cityID','require','请选择城市', 1,'',1),
		array('sort','require','请选择分类', 1,'',1),
		/*array('begin','require','请选择开始时间', 1,'',1,'',1),
		array('price','require','输入人均消费', 1,'',1),
		array('detail','require','请输入活动描述', 1,'',1),
		array('address','require','请输入活动地址', 1,'',1),
		array('contact','require','联系人不能为空', 1,'',1),*/
		#array('phone','require','联系电话不能为空', 1),		
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('showTime','time',Model::MODEL_BOTH,'function'),
		#array('begin','strtotime',Model::MODEL_BOTH,'function'),
		#array('end','_strtotime',Model::MODEL_BOTH,'callback'),
		#array('image','_image',Model::MODEL_BOTH,'callback'),
        #array('thumb','_thumb',Model::MODEL_BOTH,'callback'),
	);

	protected function _strtotime($v){
		if ($v!='') {
			return strtotime($v);
		}else{
			return 0;
		} 
    }

    protected function _image($image){
        if ($image) {
            return implode(";", $image);
        }else{
            return '';
        }        
    }

    protected function _thumb($thumb){
        if ($thumb) {
            return $thumb;
        }else{
            $image = I('post.image');
            return $image[0];
        }        
    }  
}
?>