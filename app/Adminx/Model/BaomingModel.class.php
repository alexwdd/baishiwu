<?php
namespace Adminx\Model;
use Think\Model;

class BaomingModel extends Model {

    protected $_validate = array (//字段验证
    	array('title','require','请输入活动名称', 1),
        array('content','require','请输入活动内容', 1),
    	array('endTime','require','请选择截止日期', 1),
    );	

    protected $_auto = array ( //自动完成
		array('createTime','time',Model::MODEL_INSERT,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
        array('endTime','strtotime',Model::MODEL_BOTH,'function'),
        array('intr','_intr',Model::MODEL_BOTH,'callback'),
	);

    protected function _intr(){
        $intr = I('post.intr');
        $content = $this->cutstr_html($_POST['content'],150);        
        if ($intr=='') {
            return $content;
        }else{
            return $intr;
        }
    }

    protected function cutstr_html($string, $sublen){
        $string = strip_tags($string);
        $string = preg_replace ('/\n/is', '', $string);
        $string = preg_replace ('/ |　/is', '', $string);
        $string = preg_replace ('/&nbsp;/is', '', $string);   
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);   
        if(count($t_string[0]) - 0 > $sublen) $string = join('', array_slice($t_string[0], 0, $sublen))."…";   
        else $string = join('', array_slice($t_string[0], 0, $sublen));   
        return $string;
    }
}
?>