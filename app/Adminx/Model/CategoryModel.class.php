<?php
namespace Adminx\Model;
use Think\Model;

class CategoryModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('sort','require','排序不能为空', 1),
		array('fid','require','fid不能为空', 1),
    );	

    protected $_auto = array (

		array('fid','getFid',Model::MODEL_BOTH,'callback'),
		array('path','getPath',Model::MODEL_BOTH,'callback'),
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),

	);

    protected function getFid(){
    	if(I('post.fid')!=0){			
			$class = explode(',', I('post.fid'));
			return $class[0];
		}else{
			return 0;
		}
    }

    protected function getPath(){
    	if(I('post.fid')!=0){			
			$class = explode(',', I('post.fid'));
			return $class[1];
		}else{
			return '0-';
		}
    }
}
?>