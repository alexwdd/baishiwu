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

    //话题
	public function getmain(){
		if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $cid = I('post.cid');
            $cityID = I('post.cityID');
            $token = I('post.token');
            $page = I('post.page',1);

            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','缺少cityID');
            }
            
            $where['fid'] = 152;
            $where['cityID'] = $cityID;
            $cate = M("CityCate")->where($where)->field("cid as id,name as title,icon")->order('sort asc,id asc')->select();
            $quick = [];
            $q = [];
            $i = 1;
            foreach ($cate as $key => $value) {
                $cate[$key]['icon'] = getRealUrl($value['icon']);
                $cate[$key]['index'] = $key+1;
                array_push($q,$cate[$key]);
                if ($i%15==0) {
                    array_push($quick,$q);
                    $q = [];
                }
                $i++;               
            }
            if (count($q)>0) {
                array_push($quick,$q);
            }

            $map['cityID'] = $cityID;
            if ($cid!=0 && is_numeric($cid)) {
            	$map['cid'] = $cid;
            }
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('Chat');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            if ($token!='') {
                $user = $this->checkToken($token);
            } 
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = getLastTime($value['createTime']);
                if($value['images']!=''){
                	$img = explode("|", $value['images']);
                	$thumb = explode("|", $value['thumb']);
                	foreach ($img as $k => $val) {
                		$img[$k] = getRealUrl($val);
                	}
                	foreach ($thumb as $k => $val) {
                		$thumb[$k] = getRealUrl($val);
                	}
                	$list[$key]['images'] = $img;
                	$list[$key]['thumb'] = $thumb;
                	$list[$key]['num'] = count($img);
                }

                $list[$key]['like'] = M('ChatLike')->where(array('chatID'=>$value['id']))->count();
                $list[$key]['comment'] = M('ChatComment')->where(array('chatID'=>$value['id']))->count();

                if ($user) {
                    $count = M('ChatFocus')->where(array('userID'=>$value['memberID'],'memberID'=>$user['id']))->count();
                    if ($count>0) {
                        $list[$key]['focus'] = true; 
                    }else{
                        $list[$key]['focus'] = false; 
                    }
                }else{
                    $list[$key]['focus'] = false; 
                }
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list,'cate'=>$cate,'quick'=>$quick]);
        }
	}

    //我的关注
    public function getFocus(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $page = I('post.page',1);

            $ids = M('ChatFocus')->where(array('memberID'=>$user['id']))->getField('userID',true);
            if (!$ids) {
                $ids = [0];
            }
            $map['memberID'] = array('in',$ids);
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('Chat');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            if ($token!='') {
                $user = $this->checkToken($token);
            } 
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = getLastTime($value['createTime']);
                if($value['images']!=''){
                    $img = explode("|", $value['images']);
                    $thumb = explode("|", $value['thumb']);
                    foreach ($img as $k => $val) {
                        $img[$k] = getRealUrl($val);
                    }
                    foreach ($thumb as $k => $val) {
                        $thumb[$k] = getRealUrl($val);
                    }
                    $list[$key]['images'] = $img;
                    $list[$key]['thumb'] = $thumb;
                    $list[$key]['num'] = count($img);
                }

                $list[$key]['like'] = M('ChatLike')->where(array('chatID'=>$value['id']))->count();
                $list[$key]['comment'] = M('ChatComment')->where(array('chatID'=>$value['id']))->count();

                if ($user) {
                    $count = M('ChatFocus')->where(array('userID'=>$value['memberID'],'memberID'=>$user['id']))->count();
                    if ($count>0) {
                        $list[$key]['focus'] = true; 
                    }else{
                        $list[$key]['focus'] = false; 
                    }
                }else{
                    $list[$key]['focus'] = false; 
                }
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    //我的话题
    public function myChat(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $page = I('post.page',1);
            $map['memberID'] = $user['id'];
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('Chat');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = getLastTime($value['createTime']);
                if($value['images']!=''){
                    $img = explode("|", $value['images']);
                    $thumb = explode("|", $value['thumb']);
                    foreach ($img as $k => $val) {
                        $img[$k] = getRealUrl($val);
                    }
                    foreach ($thumb as $k => $val) {
                        $thumb[$k] = getRealUrl($val);
                    }
                    $list[$key]['images'] = $img;
                    $list[$key]['thumb'] = $thumb;
                    $list[$key]['num'] = count($img);
                }

                $list[$key]['like'] = M('ChatLike')->where(array('chatID'=>$value['id']))->count();
                $list[$key]['comment'] = M('ChatComment')->where(array('chatID'=>$value['id']))->count();
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    //搜索话题
    public function search(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $token = I('post.token');
            $keyword = I('post.keyword');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $page = I('post.page',1);
            $map['content'] = array('like','%'.$keyword.'%');
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('Chat');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            if ($token!='') {
                $user = $this->checkToken($token);
            } 
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = getLastTime($value['createTime']);
                if($value['images']!=''){
                    $img = explode("|", $value['images']);
                    $thumb = explode("|", $value['thumb']);
                    foreach ($img as $k => $val) {
                        $img[$k] = getRealUrl($val);
                    }
                    foreach ($thumb as $k => $val) {
                        $thumb[$k] = getRealUrl($val);
                    }
                    $list[$key]['images'] = $img;
                    $list[$key]['thumb'] = $thumb;
                    $list[$key]['num'] = count($img);
                }

                $list[$key]['like'] = M('ChatLike')->where(array('chatID'=>$value['id']))->count();
                $list[$key]['comment'] = M('ChatComment')->where(array('chatID'=>$value['id']))->count();

                if ($user) {
                    $count = M('ChatFocus')->where(array('userID'=>$value['memberID'],'memberID'=>$user['id']))->count();
                    if ($count>0) {
                        $list[$key]['focus'] = true; 
                    }else{
                        $list[$key]['focus'] = false; 
                    }
                }else{
                    $list[$key]['focus'] = false; 
                }
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    //删除话题
    public function del(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');
            $token = I('post.token');

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $map['id'] = $id;
            $map['memberID'] = $user['id'];
            $res = M('Chat')->where($map)->delete();
            if ($res) {
                M('ChatComment')->where(array('chatID'=>$id))->delete();
                M('ChatComment')->where(array('chatID'=>$id))->delete();
                returnJson(0,'success');
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    //话题分类
	public function cate(){
		if (IS_POST) {
			if(!checkFormDate()){returnJson('-1','ERROR');}
            $cityID = I('post.cityID');
            if ($cityID==0) {
                returnJson('-1','缺少cityID');
            }
            $map['cityID'] = $cityID;
            $map['fid'] = 152;
            $cate = M('CityCate')->field('name,cid,icon')->where($map)->select();
            foreach ($cate as $key => $value) {
            	$cate[$key]['icon'] = getRealUrl($value['icon']);
            }

            $tag = M('OptionItem')->field('name')->where(array('cate'=>8))->select();

            returnJson('0',C("SUCCESS_RETURN"),array('cate'=>$cate,'tag'=>$tag)); 
		}
	}

	//发布话题
	public function submit(){
		if (IS_POST) {
			if(!checkFormDate()){returnJson('-1','ERROR');}
			$token = I('post.token');
			$cityID = I('post.cityID');
			$content = I('post.content');
			$cid = I('post.cid');
            $tag = I('post.tag');
			$open = I('post.open');
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
				$thumb = '';
				foreach ($imgArr as $key => $value) {
					$result = $this->base64_upload($value);

					$image = new \Think\Image();
					$image->open('.'.$result['url']);
					$thumbUrl = $result['path']."s_".$result['name'];
					$image->thumb(100,100,3)->save('.'.$thumbUrl);			
					if ($key==0) {
						$images = $result['url'];
						$thumb = $thumbUrl;
					}else{
						$images .= '|'.$result['url'];
						$thumb .= '|'.$thumbUrl;
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
				'thumb'=>$thumb,
				'like'=>0,
                'status'=>1,
				'open'=>$open,
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
        $path = '.'.C('UPLOAD_PATH').'chat/'.date("Ymd",time())."/";
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
                $path = C('UPLOAD_PATH').'chat/'.date("Ymd",time())."/";
                return ['path'=>$path,'name'=>$image_name,'url'=>$path.$image_name];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //点赞
    public function like(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $token = I('post.token');
            $chatID = I('post.chatID');
            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }
            if ($chatID=='' && !is_numeric($chatID)) {
                returnJson('-1','参数错误');
            }
            $map['chatID'] = $chatID;
            $map['memberID'] = $user['id'];
            $res = M('ChatLike')->where($map)->find();
            if ($res) {
                $result = M('ChatLike')->where($map)->delete();
                if ($result) {
                    returnJson('0','取消点赞');
                }else{
                    returnJson('-1','操作失败');
                }
            }else{
                $data = ['chatID'=>$chatID,'memberID'=>$user['id']];
                $result = M('ChatLike')->add($data);
                if ($result) {
                    returnJson('0','已点赞');
                }else{
                    returnJson('-1','操作失败');
                }                
            }
        }
    }

    //do关注
    public function focus(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $token = I('post.token');
            $userID = I('post.userID');
            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }
            if ($userID=='' && !is_numeric($userID)) {
                returnJson('-1','参数错误');
            }

            if ($userID==$user['id']) {
                returnJson('-1','不能关注自己~~');
            }
            $map['userID'] = $userID;
            $map['memberID'] = $user['id'];
            $res = M('ChatFocus')->where($map)->find();
            if ($res) {
                $result = M('ChatFocus')->where($map)->delete();
                if ($result) {
                    returnJson('0','取消关注');
                }else{
                    returnJson('-1','操作失败');
                }
            }else{
                $data = ['userID'=>$userID,'memberID'=>$user['id']];
                $result = M('ChatFocus')->add($data);
                if ($result) {
                    returnJson('0','已关注');
                }else{
                    returnJson('-1','操作失败');
                }                
            }
        }
    }

    //话题详情
    public function getinfo(){  
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $id = I('post.id');
            $token = I('post.token');
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }

            $map['id']=$id;
            $map['status'] = 1;
            $list = M('Chat')->where($map)->find();
            M('Chat')->where($map)->setInc('hit');
            if (!$list) {
                returnJson('-1','信息不存在');
            } else {
                $list['createTime'] = getLastTime($list['createTime']);
                if($list['images']!=''){
                    $img = explode("|", $list['images']);
                    $thumb = explode("|", $list['thumb']);
                    foreach ($img as $k => $val) {
                        $img[$k] = getRealUrl($val);
                    }
                    foreach ($thumb as $k => $val) {
                        $thumb[$k] = getRealUrl($val);
                    }
                    $list['images'] = $img;
                    $list['thumb'] = $thumb;
                    $list['num'] = count($img);
                }

                if ($token!='') {
                    $user = $this->checkToken($token);
                }

                if ($user) {
                    $count = M('ChatFocus')->where(array('userID'=>$list['memberID'],'memberID'=>$user['id']))->count();
                    if ($count>0) {
                        $list['focus'] = true; 
                    }else{
                        $list['focus'] = false; 
                    }
                }else{
                    $list['focus'] = false; 
                }
                $list['like'] = M('ChatLike')->where(array('chatID'=>$list['id']))->count();
                returnJson('0',C('SUCCESS_RETURN'),['data'=>$list]);
            }
        }
    }

    //获取话题留言
    public function getComment(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $id = I('post.id');
            $token = I('post.token');
            $page = I('post.page',1);

            if ($token!='') {
                $user = $this->checkToken($token);
            } 

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }            

            $map['chatID'] = $id;

            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('ChatComment');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            foreach ($list as $key => $value) {
                if ($value['open']==0) {
                    if (!in_array($user['id'],[$value['memberID'],$value['userID']])) {
                        $list[$key]['content'] = '评论内容仅作者可见';
                    }
                }
                $list[$key]['createTime'] = getLastTime($value['createTime']);
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    //发布留言
    public function comment(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $id = I('post.id');
            $token = I('post.token');
            $cityID = I('post.cityID');
            $content = I('post.content');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }
  
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','参数错误');
            }
            if ($content=='') {
                returnJson('-1','请输入评论内容');
            }

            $map['id'] = $id;
            $obj = M('Chat');
            $list = $obj->where($map)->find();
            if (!$list) {
                returnJson('-1','话题不存在');
            }
            
            $data = [
                'chatID'=>$list['id'],
                'userID'=>$list['memberID'],
                'memberID'=>$user['id'],
                'nickname'=>$user['nickname'],
                'headimg'=>$user['headimg'],
                'content'=>$content,
                'open'=>$list['open'],
                'status'=>1,
                'createTime'=>time(),
            ];

            $res = M("ChatComment")->add($data);
            if ($res) {
                $list = M("ChatComment")->where(array('id'=>$res))->order('id desc')->limit(1)->select();
                foreach ($list as $key => $value) {
                    $list[$key]['createTime'] = getLastTime($value['createTime']);
                }
                returnJson(0,'success',$list);
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    //留言点赞
    public function digg(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $commentID = I('post.commentID');
            if ($commentID=='' || !is_numeric($commentID)) {
                returnJson('-1','参数错误');
            }
            $map['commentID'] = $commentID;
            $map['memberID'] = $user['id'];
            $obj = M('ChatDigg');
            $list = $obj->where($map)->find();
            if (!$list) {
                $data = [
                    'commentID' => $commentID,
                    'memberID' => $user['id'],
                    'createTime' => time()
                ];
                $r = $obj->add($data);
                if ($r) {
                    M('ChatComment')->where(array('id'=>$commentID))->setInc('digg');
                    returnJson(0,C("SUCCESS_RETURN"));
                } else {
                    returnJson('-1','操作失败');
                } 
            }else{
                returnJson('-1','操作失败');
            }                           
        }
    }
}