<?php
namespace V1\Controller;

class ArticleController extends CommonController {

	//获取该城市选择的模块
	public function getmodels(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$cityID = I('post.cityID');
			if ($cityID=='' || !is_numeric($cityID)) {
				returnJson('-1','缺少cityID');
			}
        	$map['cityID'] = $cityID;   
        	$map['fid'] = 0;     	
        	$list = M('CityCate')->field('cid,name')->where($map)->order('sort asc')->select();
        	foreach ($list as $key => $value) {
        		unset($r);
        		$r = $this->getPy($value['cid']);
        		if ($r) {
        			$list[$key]['type'] = $r['py'];
        		}else{
        			$list[$key]['type'] = '';
        		}
        		unset($list[$key]['cid']);

        		if ($list[$key]['type']!='') {
        			$db = $this->getModel($list[$key]['type']);
        			$map['status'] = 1;
        			$map['cityID'] = $cityID;
	        		$number = M($db['db'])->where($map)->count();
	        		$list[$key]['num'] = $number;
        		}
        		/*unset($map);
        		*/
        	}
        	return returnJson('0',C('SUCCESS_RETURN'),$list);        	
		}		
	}

	public function getPy($cid){
		foreach (C('infoArr') as $key => $value) {
			if ($value['fid'] == $cid) {
	            return $value;
	        }
		}
		return false;
	}

	public function getModel($type){
		foreach (C('infoArr') as $key => $value) {
			if ($value['py'] == $type) {
	            return $value;
	        }
		}
		return false;		
	}

	//统一发帖
	public function publish(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$type = I('post.type');
			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');

			$user = $this->userCheck($userid,$password,$openid);

        	$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}
        	$obj = D($arr['db']);
        	$fid = $arr['fid'];
        	if ($data = $obj->create()) {
        		$cityArr = S('C_DATA')['city'];		
        		$flag = false;
				for ($i=0; $i < count($cityArr); $i++) {
					if ($data['cityID'] == $cityArr[$i]['id']) {
						$flag=true;
						break;
					}
				}
				if (!$flag) {
					returnJson('-1','cityID不正确');
				}

				if (!M('Category')->where(array('fid'=>$fid,'id'=>$data['sort']))->find()) {
					returnJson('-1','sort不正确');
				}
				if ($data['cityID']==39) {
					$data['status'] = 0;
				}else{
					$data['status'] = 1;
				}
	        	
	        	$data['userid'] = $userid;
	        	$data['image'] = I('post.imgpath');
	            if ($list = $obj->add($data)) {
	            	returnJson('0',C("SUCCESS_RETURN"),array('articleid'=>$list));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }	
		}
	}

	//修改发帖
	public function edit(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$type = I('post.type');
			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');
			$articleid = I('post.articleid');
			
			if ($articleid=='' || !is_numeric($articleid)) {
	    		returnJson('-1','缺少articleid');
	    	}

			$user = $this->userCheck($userid,$password,$openid);

        	$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}
        	$obj = D($arr['db']);
        	$fid = $arr['fid'];
        	
        	if ($data = $obj->create()) {
        		$r = $obj->where(array('articleid'=>$articleid,'userid'=>$userid))->find();
	        	if (!$r) {
	        		returnJson('-1','信息不存在');
	        	}
	        	/*if ($data['cityID']) {
	        		$cityArr = S('C_DATA')['city'];		
	        		$flag = false;
					for ($i=0; $i < count($cityArr); $i++) {
						if ($data['cityID'] == $cityArr[$i]['id']) {
							$flag=true;
							break;
						}
					}
					if (!$flag) {
						returnJson('-1','cityID不正确');
					}
	        	}        		
	        	if ($data['cityID']) {
	        		if (!M('Category')->where(array('fid'=>$fid,'id'=>$data['sort']))->find()) {
						returnJson('-1','sort不正确');
					}
	        	}*/
	        	unset($data['cityID']);				
	        	unset($data['sort']);					

	        	$data['status'] = 1;
	        	$data['image'] = I('post.imgpath');
	        	unset($map);
	        	$map['articleid']=$articleid;
	        	$map['userid']=$userid;
	            if ($list = $obj->save($data)) {
	            	returnJson('0',C("SUCCESS_RETURN"),array('articleid'=>$data['articleid']));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }
		}
	}

	//修改发帖
	public function updatetime(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$type = I('post.type');
			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');
			$articleid = I('post.articleid');
			
			if ($articleid=='' || !is_numeric($articleid)) {
	    		returnJson('-1','缺少articleid');
	    	}

			$user = $this->userCheck($userid,$password,$openid);

        	$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}
        	$obj = M($arr['db']);
        	$fid = $arr['fid'];        	
        	
    		$r = $obj->where(array('articleid'=>$articleid,'userid'=>$userid))->find();
        	if (!$r) {
        		returnJson('-1','信息不存在');
        	}        
        
        	unset($map);
        	$map['articleid']=$articleid;
        	$map['userid']=$userid;
            if ($list = $obj->where($map)->setField('updateTime',time())) {
            	returnJson('0',C("SUCCESS_RETURN"),array('articleid'=>$articleid));
            } else {
            	returnJson('-1','操作失败');
            }	        
		}
	}

	//获取分类
	public function get_sort(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$type = I('post.type');

			$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}

			$map['model'] = 2;
			$map['fid'] = $arr['fid'];
			$list = M("Category")->field("id,name as title")->where($map)->order('sort asc,id asc')->select();
			return returnJson('0',C('SUCCESS_RETURN'),$list);
		}
	}

	//获取分类
	public function getSortName($cid){
		$map['id'] = $cid;
		return M('Category')->where($map)->getField('name');die;
	}

	//获取帖子信息
	public function getinfo(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$articleid = I('post.articleid');
			$type = I('post.type');

			$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}
        	$map['articleid'] = $articleid;        	
        	$list = $obj = M($arr['db'])->where($map)->find();
        	if ($list){
        		M($arr['db'])->where($map)->setInc('hit');
        		/*if ($list['status']==0) {
        			returnJson('-1','信息审核中');
        		}
        		unset($list['status']);*/
        		$list['thumb_s'] = getRealUrl($list['thumb_s']);
        		$list['thumb_b'] = getRealUrl($list['thumb_b']);
        		if ($list['image']!='') {        			
        			$list['images'] = explode(";", $list['image']);
        			for ($i=0; $i < count($list['images']); $i++) { 
        				$list['images'][$i] = getRealUrl($list['images'][$i]);
        			}
        		}else{
        			$list['images'] = array();
        		}
        		$list['sortName'] = $this->getSortName($list['sort']);        		
        		unset($map);
        		$map['type'] = $type;
        		$map['articleid'] = $articleid;
        		$obj = M('Comment');
        		$list['comments'] = $obj->field('id as commentid,nickname,headimg,comments as content,createTime as time')->where($map)->limit(3)->order('id desc')->select();
        		return returnJson('0',C('SUCCESS_RETURN'),$list);
        	}else{
        		returnJson('-1','不存在的信息');
        	}
		}
	}

	public function delete(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');
			$articleid = I('post.articleid');
			$type = I('post.type');

			$user = $this->userCheck($userid,$password,$openid);

			$arr = $this->getModel($type);
        	if (!$arr) {
        		returnJson('-1','type类型错误');
        	}
        	$obj = M($arr['db']);
			$map['userid'] = $userid;
			$map['articleid'] = $articleid;
	       	$r = $obj->where($map)->delete();
	       	if ($r) {
	       		returnJson('0',C("SUCCESS_RETURN"));
	       	}else{
	       		returnJson('-1','操作失败');
	       	}		
		}
	}

	//对帖子留言
	public function html_send_comment(){
		if ($_POST) {
			if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            if (I('post.weixin')==0) {
	            $res = $this->checkLogin();
	            if ($res['code']==0) {
	                returnJson('-1','登录状态过期');
	            }
	            $user = $res['user'];
            }else{
            	switch (I('post.cityID')) {
		            case '9'://阿德莱德
		                $cityName = '阿德莱德眼';
		                $user = ['id'=>0,'nickname'=>$cityName.'App游客','headimg'=>'http://www.worldmedia.top/tpl/static/image/adld.jpg'];
		                break;
		            case '2'://堪培拉
		                $cityName = '堪城圈';
		                $user = ['id'=>0,'nickname'=>$cityName.'App游客','headimg'=>'http://www.worldmedia.top/tpl/static/image/kc.jpg'];
		                break; 
		            case '39'://堪培拉
		                $cityName = '新加坡生活圈';
		                $user = ['id'=>0,'nickname'=>$cityName.'App游客','headimg'=>'http://www.worldmedia.top/tpl/static/image/xjp.jpg'];
		                break;
		            default:
		            	$cityName = '';
		                $user = ['id'=>0,'nickname'=>'游客','headimg'=>'http://www.worldmedia.top/tpl/static/image/face.jpg'];
		                break;
		        }            	
            }
            $content = I('post.content');
            $articleid = I('post.articleid');
            if ($articleid=='' || !is_numeric($articleid)) {
                returnJson('-1','参数错误');
            }        

        	$obj = D('Comment');
        	if ($data = $obj->create()) {
        		if (!$data['type']=='article') {
        			$arr = $this->getModel($data['type']);
		        	if (!$arr) {
		        		returnJson('-1','type类型错误');
		        	}
        		} 
	        	//$data['status'] = 1;
	        	$data['userid'] = $user['id'];
	        	$data['headimg'] = $user['headimg'];
	        	$data['nickname'] = $user['nickname'];
	            if ($list = $obj->add($data)) {
	            	returnJson('0','评论将由'.$cityName.'App管理员筛选后显示',array('commentid'=>$list));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }		
		}
	}

	public function digg(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            $commentID = I('post.commentID');       
            M('Comment')->where(array('id'=>$commentID))->setInc('digg');
            returnJson(0,C("SUCCESS_RETURN"));
        }
    }

	//对帖子留言
	public function send_comment(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');

			$user = $this->userCheck($userid,$password,$openid);

        	$obj = D('Comment');
        	if ($data = $obj->create()) {
        		$arr = $this->getModel($data['type']);

	        	if (!$arr) {
	        		returnJson('-1','type类型错误');
	        	}

	        	//$data['status'] = 1;
	        	$data['userid'] = $userid;
	        	$data['headimg'] = $user['headimg'];
	        	$data['nickname'] = $user['nickname'];
	            if ($list = $obj->add($data)) {
	            	returnJson('0',C("SUCCESS_RETURN"),array('commentid'=>$list));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }		
		}
	}

	//获取帖子留言
	public function get_comment(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$articleid = I('post.articleid');
			$type = I('post.type');			
			$page = I('post.page',1);

			if ($articleid=='' || !is_numeric($articleid)) {
	    		returnJson('-1','缺少articleid');
	    	}

        	if (!$type=='article') {
    			$arr = $this->getModel($type);
	        	if (!$arr) {
	        		returnJson('-1','type类型错误');
	        	}
    		}

        	$pagesize = 10;
        	$firstRow = $pagesize*($page-1); 

        	$map['type'] = $type;
        	$map['articleid'] = $articleid;
        	$obj = M('Comment');
        	$count = $obj->where($map)->count();
        	$totalPage = ceil($count/$pagesize);
        	if ($page < $totalPage) {
        		$next = 1;
        	}else{
        		$next = 0;
        	}

        	$list = $obj->field('id as commentid,nickname,headimg,comments as content,digg,createTime as time')->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();

        	returnJson('0',C("SUCCESS_RETURN"),array('comments'=>$list,'next'=>$next));
		}
	}

	public function usergetlist(){		
		if (IS_POST) {
			$sort = I('post.sort');
			$cityID = I('post.cityID');
			$visa = I('post.visa');
			$lng = I('post.lng');
			$lat = I('post.lat');
			$type = I('post.type');
			$userid = I('post.userid');
			$keyword = I('post.keyword');
			$page = I('post.page',1);

			$parm = array(
				'userid'=>$userid,
				'visa'=>$visa,
				'lng'=>$lng,
				'lat'=>$lat,
				'sort'=>$sort,
				'cityID'=>$cityID,
				'page'=>$page
				);

			if ($type=='') {
				$list = $this->getInfoCount($parm);
				returnJson('0',C("SUCCESS_RETURN"),array('articles'=>$list,'next'=>0));
			}else{
				$arr = $this->getModel($type);
	        	if (!$arr) {
	        		returnJson('-1','type类型错误');
	        	}

	        	$map = $this->getMap();

	        	if ($keyword!='') {
	        		$map['title'] = array('like','%'.$keyword.'%');
	        	}

	        	if (I('post.subway')!='') {
	        		$subway = explode(",",I('post.subway'));
	        		unset($map['subway']);
	        		$str = '';
	        		foreach ($subway as $key => $value) {
	        			if ($key == 0) {
	        				$str = 'subway like "%'.$value.'%"';
	        			}else{
	        				$str .= ' or subway like "%'.$value.'%"';
	        			}	        			
	        		}	       
	        		$map['_string'] = $str;
	        	}

	        	if ($parm['lng']!='' && $parm['lat']!='') {
	        		switch ($cityID) {
			            case '9'://阿德莱德
			                $map['latitude'] = array('between',array(-38,-25));
	        				$map['longitude'] = array('between',array(128,141));
	        				break;
			            case '2'://堪培拉
			            	$map['latitude'] = array('between',array(-35.5,-35));
	        				$map['longitude'] = array('between',array(148.5,150));
	        				break;			                
			            case '39'://新加坡
			            	$map['latitude'] = array('between',array(1.2,1.5));
	        				$map['longitude'] = array('between',array(103.5,104.2));
	        				break;			                
			            default:
			                $map['longitude'] = array('neq','');
	        				$map['latitude'] = array('neq','');
			                break;
			        }	        		
	        		$day = date('Ymd', strtotime("-60 day"));
	        		$map['createTime'] = array('gt',strtotime($day));
					$field='*,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(('.$lat.'*PI()/180-latitude*PI()/180)/2),2)+COS('.$lat.'*PI()/180)*COS(latitude*PI()/180)*POW(SIN(('.$lng.'*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance';
					$order = 'articleid desc';
					$pagesize = 100;
				}else{
					$field = '*';
					$order = 'isTop desc , updateTime desc , articleid desc';
					$pagesize = 10;
				}
				
	        	$firstRow = $pagesize*($page-1); 
	        	
	        	$obj = M($arr['db']);
	        	$count = $obj->where($map)->count();
	        	$totalPage = ceil($count/$pagesize);
	        	if ($page < $totalPage) {
	        		$next = 1;
	        	}else{
	        		$next = 0;
	        	}
	        	$list = $obj->field($field)->where($map)->limit($firstRow.','.$pagesize)->order($order)->select();
	    
	        	foreach ($list as $key => $value) {
	        		unset($list[$key]['content']);
                  	unset($list[$key]['detail']);
	        		$list[$key]['sortName'] = $this->getSortName($value['sort']);
	        	}

	        	//if ($cityID==39) {
	        		$type = I('type');
					$arr = $this->getModel($type);
			    	if (!$arr) {
			    		returnJson('-1','type类型错误');
			    	}
                  	unset($map);
			    	$map['fid'] = $arr['fid'];
			    	$map['cityID'] = $cityID;
					$cate = M("CityCate")->where($map)->field("cid as id  ,name as title")->order('sort asc,id asc')->select();
	        	//}

	        	returnJson('0',C("SUCCESS_RETURN"),array('articles'=>$list,'next'=>$next,'cate'=>$cate));
			}
		}
	}

	public function getInfoCount($parm){	
		unset($parm['type']);
		$map['cityID'] = $parm['cityID'];
		$map['fid'] = 0;
		$map['cid'] = array('not in',[94,113]);
		$list = M('CityCate')->where($map)->order('sort asc')->select();

		unset($map);
		if ($parm['userid']!='' && is_numeric($parm['userid'])) {
			$map['userid'] = $parm['userid'];
		}
		//$map['status']=1;
		$arr = array();
		foreach ($list as $key => $value) {
			$r = $this->getPy($value['cid']);
			$db = $r['db'];
			$number = M($db)->where($map)->count();
			array_push($arr, array('type'=>$r['name'],'py'=>$r['py'],'num'=>$number));
		}
		return $arr;
	}

	public function getmain(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}
			$cityID = I('post.cityID');
			$map['cityID'] = $cityID;
			$map['fid'] = 0;
			$map['cid'] = array('neq',94);
			$list = M('CityCate')->where($map)->order('sort asc')->select();

			$result = array();
			foreach ($list as $key => $value) {
				unset($map);
				if ($cityID!='' && is_numeric($cityID)) {
					$map['cityID']=$cityID;
				}
				$r = $this->getPy($value['cid']);		
				$obj = M($r['db']);
				$map['status'] = 1;
				$list = $obj->where($map)->order('isTop desc,articleid desc')->limit(6)->select();
				foreach ($list as $key => $val) {
					unset($list[$key]['detail']);
					unset($list[$key]['content']);
	        		$list[$key]['sortName'] = $this->getSortName($val['sort']);
	        	}
				array_push($result,array($r['py']=>$list));					
			}
        	returnJson('0',C("SUCCESS_RETURN"),$result);
		}
	}

	public function getads(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}
			$cityID = I('post.cityID');
			$adID = I('post.adID');
			if ($cityID!='' && is_numeric($cityID)) {
				$map['cityID']=$cityID;
			}
			if ($adID!='' && is_numeric($adID)) {
				$map['cid']=$adID;
			}

			$list = M('Ad')->field('name as title,articleid,type,createTime as time,image,url')->where($map)->order('sort asc , id desc')->select();
			foreach ($list as $key => $value) {
				$list[$key]['time'] = date("Y-m-d",$value['time']);
				$list[$key]['image'] = C('site.domain').$value['image'];
			}
			returnJson('0',C("SUCCESS_RETURN"),array('ads'=>$list));        	
		}
	}

	public function getMap(){
		$field = array('userid','houseType','sort','cityID','phone','isTop','page','status');
		$parm = I('post.');
		unset($parm['type']);
		foreach ($parm as $key => $value) {
			if ($value!='不限') {
				$value = urldecode($value);
				if (in_array($key, $field)) {
					$map[$key] = $value;
				}else{
					$map[$key] = array('like','%'.$value.'%');
				}
			}			
		}
		return $map;
	}

	public function share(){
		$articleid = I('get.articleid');
		$type = I('get.type');

		$arr = $this->getModel($type);
    	if (!$arr) {
    		returnJson('-1','type类型错误');
    	}
    	$map['articleid'] = $articleid;        	
    	$list = $obj = M($arr['db'])->where($map)->find();
    	if ($list){
    		$list['thumb_s'] = getRealUrl($list['thumb_s']);
    		$list['thumb_b'] = getRealUrl($list['thumb_b']);
    		if ($list['image']!='') {        			
    			$list['images'] = explode(";", $list['image']);
    			for ($i=0; $i < count($list['images']); $i++) { 
    				$list['images'][$i] = getRealUrl($list['images'][$i]);
    			}
    		}else{
    			$list['images'] = array();
    		}
    		unset($list['image']);
    		$list['updateTime'] = date("Y-m-d",$list['updateTime']);
    		return returnJson('0',C('SUCCESS_RETURN'),$list);
    	}else{
    		returnJson('-1','不存在的信息');
    	}
	}
  
  	public function hit(){  
        $type = I('get.type');
      	$id = I('get.articleid');
      	$db = $this->getModel($type);
        if ($id=='' || !is_numeric($id)) {
            echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
        }
        $map['articleid']=$id;
        M($db['db'])->where($map)->setInc('hit');
    }
}