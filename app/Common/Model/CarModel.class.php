<?php
namespace Common\Model;
use Think\Model;

class CarModel extends Model {

    protected $_validate = array (
		array('articleid','require','articleid不能为空',1,'number',2),
		array('title','require','标题不能为空', 1,'',1),
		array('cityID','require','请选择城市', 1,'',1),
		array('sort','require','请选择分类', 1,'',1),		
		/*array('detail','require','请输入描述', 1,'',1),
		array('address','require','请输入联系地址', 1,'',1),
		array('contact','require','联系人不能为空', 1,'',1),*/
		#array('phone','require','联系电话不能为空', 1),	

		/*array('brand','require','选择汽车品牌', 1,'',1),	
		array('trans','require','输入变速箱类型', 1,'',1),	
		array('mileage','require','输入当前里程', 1,'',1),	
		array('year','require','输入购买年份', 1,'',1),	
		array('price','require','输入售价', 1,'',1),	*/
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('showTime','time',Model::MODEL_BOTH,'function'),
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