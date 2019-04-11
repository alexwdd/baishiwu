<?php
namespace Adminx\Model;
use Think\Model;

class AlbumModel extends Model {

    protected $_validate = array (
		array('title','require','请输入标题', 1),
        array('picname','require','请上传缩略图', 1),
        array('cid','require','分类不能为空', 1),
        array('createTime','require','发布日期不能为空', 1),
    );	

    protected $_auto = array (

		array('cid','_cid',Model::MODEL_BOTH,'callback'),
		array('path','_path',Model::MODEL_BOTH,'callback'),
		array('createTime','_createTime',Model::MODEL_BOTH,'callback'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('hit','_hit',Model::MODEL_INSERT,'callback'),
        array('editer','_editer',Model::MODEL_BOTH,'callback'),
		array('top','_top',Model::MODEL_BOTH,'callback'),
		array('flash','_flash',Model::MODEL_BOTH,'callback'),
        array('comm','_comm',Model::MODEL_BOTH,'callback'),
        array('year','_year',Model::MODEL_BOTH,'callback'),
	);

    protected function _createTime(){
        return strtotime(I('post.createTime'));
    }

    protected function _year(){
        return date('Y',strtotime(I('post.createTime')));
    }

	protected function _cid(){
		$class = explode(',', I('post.cid'));
		return $class[0];
    }

    protected function _path(){
		$class = explode(',', I('post.cid'));
		return $class[1];
    }

    protected function _hit(){
    	return rand(50,500);
    }

    protected function _editer(){
    	return $_SESSION['adminName'];
    }

    protected function _top(){
    	if(I('post.top')==''){
    		return 0;
    	}else{
    		return 1;
    	}    	
    }

    protected function _flash(){
    	if(I('post.flash')==''){
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