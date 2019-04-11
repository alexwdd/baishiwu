<?php
namespace Adminx\Model;
use Think\Model;

class AlbumImageModel extends Model {

    protected $_validate = array (
		array('title','require','请输入标题', 1),
        array('picname','require','请上传图片', 1),
    );	

    protected $_auto = array (
		array('createTime','time',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
	);

    protected function _after_insert($data, $options) {
        $map['id']=$data['albumID'];
        M("Album")->where($map)->setInc("num");
    }
}
?>