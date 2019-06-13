<?php
namespace Daigou\Controller;

class UploadController extends CommonController{

 	#图片上传
 	public function image(){
  		$water = I('get.water')?I('get.water'):1;
 		$thumb = I('get.thumb')?I('get.thumb'):1;
 		$resault = $this->_saveimage("images",$water,$thumb);
 		$this->ajaxReturn($resault);
 	}

	#保存图片
	private function _saveimage($dir=NULL,$water=0,$thumb=0){
		if(!checkRequest()){
	        return array('state'=>'非法提交');
	    }

		if (empty($dir)) {
			$path = '.'.C('UPLOAD_PATH');
		}else{
			$path = '.'.C('UPLOAD_PATH').$dir.'/';
		}

		if(!is_dir($path)){
    		mkdir($path);
		}
		
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = C('image_size')*1024*1024;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
		$upload->rootPath = $path;
		$upload->autoSub = true;
		$upload->replace=true;     //如果存在同名文件是否进行覆盖
		$upload->exts= explode(',',C('image_exts'));     //准许上传的文件后缀
		$info = $upload->upload();	
		if($info){
			foreach($info as $file){
				if (empty($dir)) {
					$filepath = C('UPLOAD_PATH').$file['savepath'];
					$url = C('UPLOAD_PATH').$file['savepath'].$file['savename'];
				}else{
					$filepath = C('UPLOAD_PATH').$dir.'/'.$file['savepath'];
					$url = C('UPLOAD_PATH').$dir.'/'.$file['savepath'].$file['savename'];
				}
			}	

			//缩放处理
			// if (C('IMAGE_MAX_WIDTH')) {
			// 	$image = new \Think\Image();
			// 	$image->open('.'.$url);
			// 	$image->thumb(C('IMAGE_MAX_WIDTH'), C('IMAGE_MAX_WIDTH'))->save('.'.$filepath.$file['savename']);
			// }

			//缩略图
			if (C('THUMB_IMAGE') && $thumb==1) {
				$image = new \Think\Image();
				$image->open('.'.$url);
				$image->thumb(C('THUMB_WIDTH'), C('THUMB_HEIGHT'),\Think\Image::IMAGE_THUMB_FILLED)->save('.'.$filepath.'s_'.$file['savename']);
				$thumbUrl = $filepath.'s_'.$file['savename'];
			}

			//图片水印
			if (C('site_water') && $water==1) {
				$image = new \Think\Image(); 
				$image->open('.'.$filepath.$file['savename'])->water('.'.C('site_water'),5)->save('.'.$filepath.$file['savename']);
			}
			return json_encode(array(
	            'status'=>1,
	            'results'=>array(
	                'data'=>array(
	                    'url'=>$url,
	                    'thumb'=>$thumbUrl
	                    )
	                )
	        ));		
		}else{
			//是专门来获取上传的错误信息的	
			return array('status'=>0,'info'=>$upload->getError());
		}
	}	

	public function file(){
		if(!checkRequest()){
	        return array('state'=>'非法提交');
	    }

		$path = '.'.C('UPLOAD_PATH');
	
		if(!is_dir($path)){
    		mkdir($path);
		}
		
		$upload = new \Think\Upload();// 实例化上传类
		$upload->rootPath = $path;
		$upload->autoSub = true;
		$upload->replace=true;     //如果存在同名文件是否进行覆盖
		$upload->exts= explode(',',C('image_exts'));     //准许上传的文件后缀
		$info = $upload->upload();	
		if($info){
			foreach($info as $file){
				$filepath = C('UPLOAD_PATH').$file['savepath'];
				$url = C('UPLOAD_PATH').$file['savepath'].$file['savename'];
			}	
			echo json_encode(array(
	            'status'=>1,	            
                'data'=>array(
                    'url'=>$url
                    )
	               
	        ));		
		}else{
			//是专门来获取上传的错误信息的	
			echo json_encode(array('status'=>0,'info'=>$upload->getError()));
		}
	}	
}
?>