<?php
namespace V1\Controller;

class ChatController extends CommonController {

	public function checkToken($token){
        $map['token'] = $token;
        $map['disable'] = 0;
        $map['outTime'] = array('gt',time());
    	$user = M('Member')->where($map)->find();    
        if (!$user) {
            return false;
        }else{
            $data['outTime'] = time()+2592000; 
            M('Member')->where($map)->save($data);   
            return $user;
        }
	}

	public function getinfo(){
		
	}

	public function cate(){
		if (IS_POST) {
			if(!checkFormDate()){returnJson('-1','ERROR');}
            $cityID = I('post.cityID');
            if ($cityID==0) {
                returnJson('-1','缺少cityID');
            }
            $map['cityID'] = $cityID;
            $map['fid'] = 138;
            $cate = M('CityCate')->field('name,cid,icon')->where($map)->select();
            foreach ($cate as $key => $value) {
            	$cate[$key]['icon'] = getRealUrl($value['icon']);
            }

            $tag = M('OptionItem')->field('name')->where(array('cate'=>8))->select();

            returnJson('0',C("SUCCESS_RETURN"),array('cate'=>$cate,'tag'=>$tag)); 
		}
	}

	//获取帖子留言
	public function submit(){
		if (IS_POST) {
			if(!checkFormDate()){returnJson('-1','ERROR');}
			$token = I('post.token');
			$cityID = I('post.cityID');
			$content = I('post.content');
			$cid = I('post.cid');
			$tag = I('post.tag');
			$images = I('post.images');
			if (!$user = $this->checkToken($token)) {
				returnJson('999'); 
			}
			if ($cityID==0) {
                returnJson('-1','缺少cityID');
            }
			if ($cid=='' && !is_numeric($cid)) {
				returnJson('-1','请选择版块');
			}
			if ($tag=='') {
				returnJson('-1','请选择标签');
			}
			if ($content=='') {
				returnJson('-1','请输入内容');
			}
			if ($images!='') {
				$imgArr = explode("###",$images);
				$images = '';
				foreach ($imgArr as $key => $value) {
					$result = $this->base64_upload($value);
					if ($key==0) {
						$images = $result;
					}else{
						$images .= '|'.$result;
					}
				}
			}
			$title = $this->cutstr_html($content,50);
			$data = [
				'cid'=>$cid,
				'cityID'=>$cityID,
				'memberID' => $user['id'],
				'nickname' => $user['nickname'],
				'face' => $user['headimg'],
				'content'=>$content,
				'title'=>$title,
				'tag'=>$tag,
				'images'=>$images,
				'like'=>0,
				'status'=>1,
				'createTime'=>time()
			];
			$res = M('Chat')->add($data);
			if ($res) {
				returnJson('0','发布成功');
			}else{
				returnJson('-1','操作失败');
			}
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

    /**
     * 封装base64位图片上传
     */
    function base64_upload($base64) {
        $path = '.'.C('UPLOAD_PATH').date("Ymd",time())."/";
        if(!is_dir($path)){
            mkdir($path);
        }
        $base64_image = str_replace(' ', '+', $base64);
        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
        	$type = $result[2];
        	if(!in_array($type,array("jpg","png","bmp","jpeg","gif"))){
				return false;
            }
            $image_name = uniqid().'.'.$type;
            $image_file = $path."/{$image_name}";
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                return 'http://'.$_SERVER['HTTP_HOST'].C('UPLOAD_PATH').date("Ymd",time())."/".$image_name;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}