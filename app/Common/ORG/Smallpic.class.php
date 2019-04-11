<?php
class smallpic{
	private $src_pic;				//原图
	private $face_pic = "003.png";   //水印图
	private $ewm_pic = "003.png";   //水印图
	private $ico_text = "水印";     //水印文字
	private $is_ico_pic = true;     //是否加图片水印
	private $is_text = true;        //是否加文字水印
	private $text_x = 20;	        //文字在原图的x坐标
	private $text_y = 20;           //文字在原图的y坐标
	private $face_x = 20;           //头像在原图的y坐标
	private $face_y = 20;           //头像在原图的y坐标
	private $ewm_x = 20;            //二维码在原图的y坐标
	private $ewm_y = 20;            //二维码在原图的y坐标
	private $ut = "utf-8";//文字编码
	private $font_color = "#990000";//文字水印颜色
	private $big_pic_name = "bigpic";//大图的名称

	function __construct($src_pic){
		$this->checkfile($src_pic);
		$this->src_pic = $src_pic;
	}

	private function __get($property_name){
		return $this->$property_name;
	}

	private function __set($property_name,$value){
  		return $this->$property_name = $value;
 	}

	/**
	* 取得图片的一些基本信息，类型为array
	*/
	function getimageinfo($image){
		return @getimagesize($image);
	}
	/**
	* 把图片加载到php中
	* $image 传进来的图片
	*/
	function getimage($image){
		$image_info = $this->getimageinfo($image);
		switch($image_info[2]){
			case 1:
			$img = @imagecreatefromgif($image);
			break;
			case 2:
			$img = @imagecreatefromjpeg($image);
			break;
			case 3:
			$img = @imagecreatefrompng($image);
			break;
		}
		return $img;
	}

	function createimageforsuffix($big_pic,$face_pic){
		$image_info = $this->getimageinfo($this->src_pic);
		switch($image_info[2]){
			case 1:
			//输出大图
			@imagegif($big_pic,$this->big_pic_name.".gif");
			break;
			case 2:
			//输出大图
			@imagejpeg($big_pic,$this->big_pic_name.".jpg");
			break;
			case 3:
			//输出大图
			@imagepng($big_pic,$this->big_pic_name.".png");
			break;
		}
	}

	function checkfile($file){
		if(!file_exists($file)){
			die("图片:".$file."不存在！");
		}
	 }

	function createsmallimage(){
		$big_pic = $this->getimage($this->src_pic);
		$big_pic_info = $this->getimageinfo($this->src_pic);				
		$face_pic = $this->getimage($this->face_pic);
		$face_pic_info = $this->getimageinfo($this->face_pic);
		$ewm_pic = $this->getimage($this->ewm_pic);
		$ewm_pic_info = $this->getimageinfo($this->ewm_pic);
		$rgb = $this->convcolor();

		//判断是按宽比例缩放还是按高比例缩放

		//是否打图片水印
		if ($this->is_ico_pic){
			//打头像水印
			@imagecopy($big_pic,$face_pic,$this->face_x,$this->face_y,0,0,$face_pic_info[0],$face_pic_info[1]);
			//打二维码水印
			@imagecopy($big_pic,$ewm_pic,$this->ewm_x,$this->ewm_y,0,0,$ewm_pic_info[0],$ewm_pic_info[1]);
		}

		//是否打文字水印
		if ($this->is_text){
			//设置文字颜色
			$text_color = @imagecolorallocate($big_pic,$rgb[0],$rgb[1],$rgb[2]);
			//转换文字编码
			$text = @iconv($this->ut,"utf-8",$this->ico_text);
			//打文字水印
			@imagettftext($big_pic,30,0,$this->text_x,$this->text_y,$text_color,"simhei.ttf",$text);
		}

		//新建一个新图片的画板
		//$face_pic = @imagecreatetruecolor($small_pic_width,$small_pic_height);
		//生成缩略图
		//@imagecopyresized($face_pic,$big_pic,0,0,0,0,$small_pic_width,$small_pic_height,$big_pic_info[0],$big_pic_info[1]);
		//输出图
		$this->createimageforsuffix($big_pic,$face_pic);
	}

	/**
	* 类内部的功能函数把#000000转换成255,255,255
	*/
	private function convcolor(){
		$rgb = array();
		$color = preg_replace("/#/","",$this->font_color);
		$c = hexdec($color);
		$r = ($c >> 16) & 0xff;
		$g = ($c >> 8) & 0xff;
		$b = $c & 0xff;
		$rgb[0] = $r;
		$rgb[1] = $g;
		$rgb[2] = $b;
		return $rgb;
	}
}
?>