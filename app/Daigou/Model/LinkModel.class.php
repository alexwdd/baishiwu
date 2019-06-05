<?php
namespace Adminx\Model;
use Think\Model;

class LinkModel extends Model {

    protected $_validate = array (
		array('name','require','名称不能为空', 1),
		array('url','require','链接地址不能为空', 1),
		array('cid','require','分类不能为空', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('cid','_cid',Model::MODEL_BOTH,'callback'),
		array('path','_path',Model::MODEL_BOTH,'callback'),
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