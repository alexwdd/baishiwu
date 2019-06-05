<?php
namespace Adminx\Model;
use Think\Model;

class QuestionModel extends Model {

    protected $_validate = array (
		array('name','require','问题选项不能为空', 1),
		array('intr','require','试题分析不能为空', 1),
        array('cid','require','分类不能为空', 1),
    );	

   protected $_auto = array (
		array('cid','_cid',Model::MODEL_BOTH,'callback'),
		array('path','_path',Model::MODEL_BOTH,'callback'),
		array('createTime','time',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),		
	);
    
	protected function _cid(){
		$class = explode(',', I('post.cid'));
		return $class[0];
    }

    protected function _path(){
		$class = explode(',', I('post.cid'));
		return $class[1];
    }
}
?>