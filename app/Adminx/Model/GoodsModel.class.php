<?php
namespace Adminx\Model;
use Think\Model;

class GoodsModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('picname','require','图片不能为空', 1),
		array('price','require','服务费不能为空', 1),
		array('content','require','介绍不能为空', 1),
    );	

    protected $_auto = array (
    	array('cid','_cid',Model::MODEL_BOTH,'callback'),
		array('path','_path',Model::MODEL_BOTH,'callback'),
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('index','_index',Model::MODEL_BOTH,'callback'),
		array('tejia','_tejia',Model::MODEL_BOTH,'callback'),
		array('comm','_comm',Model::MODEL_BOTH,'callback'),
	);

	protected function _cid(){
		$class = explode(',', I('post.cid'));
		return $class[0];
    }

    protected function _path(){
		$class = explode(',', I('post.cid'));
		return $class[1];
    }

	protected function _index(){
    	if(I('post.index')==''){
    		return 0;
    	}else{
    		return 1;
    	}  
    }

    protected function _tejia(){
    	if(I('post.tejia')==''){
    		return 0;
    	}else{
    		return 1;
    	}  
    }

    protected function _comm(){
    	if(I('post.comm')==''){
    		return 0;
    	}else{
    		return 1;
    	}  
    }

}
?>