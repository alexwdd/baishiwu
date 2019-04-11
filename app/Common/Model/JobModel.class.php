<?php
namespace Common\Model;
use Think\Model;

class JobModel extends Model {

    protected $_validate = array (
		array('articleid','require','articleid不能为空',1,'number',2),
		array('title','require','标题不能为空', 1,'',1),
		array('cityID','require','请选择城市', 1,'',1),
		array('sort','require','请选择分类', 1,'',1),		
		/*array('detail','require','请输入描述', 1,'',1),
		array('address','require','请输入联系地址', 1,'',1),
		array('contact','require','联系人不能为空', 1,'',1),*/
		#array('phone','require','联系电话不能为空', 1),	

		/*array('company','require','输入公司名称', 1,'',1),	
		array('visa','require','输入签证类型', 1,'',1),	
		array('work','require','输入上班时间', 1,'',1),	
		array('price','require','输入薪资', 1,'',1),	*/
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