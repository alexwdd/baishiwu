<?php
namespace Adminx\Model;
use Think\Model;

class ArticleModel extends Model {

    protected $_validate = array (
		array('title','require','请输入标题', 1),
		array('content','require','内容不能为空', 1),
        array('cid','require','分类不能为空', 1),
        array('createTime','require','发布日期不能为空', 1),
    );	

    protected $_auto = array (

		array('cid','_cid',Model::MODEL_BOTH,'callback'),
		array('path','_path',Model::MODEL_BOTH,'callback'),
        array('picname','_picname',Model::MODEL_BOTH,'callback'),
		array('createTime','strtotime',Model::MODEL_BOTH,'function'),		
		array('updateTime','time',Model::MODEL_BOTH,'function'),
		array('hit','_hit',Model::MODEL_INSERT,'callback'),
        array('editer','_editer',Model::MODEL_BOTH,'callback'),
		array('intr','_intr',Model::MODEL_BOTH,'callback'),
		array('top','_top',Model::MODEL_BOTH,'callback'),
		array('flash','_flash',Model::MODEL_BOTH,'callback'),
        array('comm','_comm',Model::MODEL_BOTH,'callback'),
        array('bold','_bold',Model::MODEL_BOTH,'callback'),
        array('red','_red',Model::MODEL_BOTH,'callback'),
        array('year','_year',Model::MODEL_BOTH,'callback'),
	);

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
    	return 0;
    }

    protected function _editer(){
    	return $_SESSION['adminName'];
    }

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

    protected function _bold(){
        if(I('post.bold')==''){
            return 0;
        }else{
            return 1;
        }  
    }

    protected function _red(){
        if(I('post.red')==''){
            return 0;
        }else{
            return 1;
        }  
    }

    protected function _picname(){        
        if(I('post.exp')==1){       
            $content = $_POST['content'];   
            preg_match_all("/src=\"?(.*?)\"/", $content, $match);
            return $match[1][0];
        }else{
            return I('post.picname');
        }
    }
}
?>