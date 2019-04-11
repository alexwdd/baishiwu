<?php
namespace V1\Controller;

class NewsController extends CommonController {

	//获取帖子留言
	public function getlists(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$cityID = I('post.cityID');
			$cateID = I('post.cateID');			
			$page = I('post.page',1);

			if ($cityID!='' && is_numeric($cityID)) {
				$map['cityID']=$cityID;
			}
			if ($cateID!='' && is_numeric($cateID)) {
				$map['cid']=$cateID;
			}

        	$pagesize = 10;
        	$firstRow = $pagesize*($page-1); 

        	$map['del'] = 0;
			$map['status'] = 1;
        	$obj = M('Article');
        	$count = $obj->where($map)->count();
        	$totalPage = ceil($count/$pagesize);
        	if ($page < $totalPage) {
        		$next = 1;
        	}else{
        		$next = 0;
        	}

        	$list = $obj->field('id,picname as thumb,title,createTime as time,hit,from,url')->where($map)->limit($firstRow.','.$pagesize)->order('top desc,id desc')->select();
        	foreach ($list as $key => $value) {
        		if ($value['thumb']!='') {
        			$list[$key]['thumb'] = C('site.domain').$value['thumb'];
         		}
         		$list[$key]['time'] = date("Y-m-d",$value['time']);
        		$list[$key]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$value['time']).'/'.$value['id'].'.html';
        	}

        	returnJson('0',C("SUCCESS_RETURN"),array('news'=>$list,'next'=>$next));
		}
	}

	public function detail(){  
		$id = I('get.id');
		if ($id=='' || !is_numeric($id)) {
			echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
		}
		$map['id']=$id;
		$map['del'] = 0;
		$map['status'] = 1;
        $list = M('Article')->where($map)->find();
        M('Article')->where($map)->setInc('hit');
        if (!$list) {
            echo json_encode(array('code'=>0,'msg'=>'信息不存在'));die;
        } else {
        	$list['createTime'] = date("Y-m-d",$list['createTime']);
        	$list['content'] = htmlspecialchars_decode($list['content']);
            echo json_encode(array('code'=>1,'data'=>$list));die;
        }
	}

    public function hit(){  
        $id = I('get.id');
        if ($id=='' || !is_numeric($id)) {
            echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
        }
        $map['id']=$id;
        $map['del'] = 0;
        $map['status'] = 1;
        M('Article')->where($map)->setInc('hit');
    }    

    public function getUserInfo(){
        if ($_POST) {
            echo json_encode($this->checkLogin());
        }        
    }

    public function saveComment(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $res = $this->checkLogin();
            if ($res['code']==0) {
                returnJson(0,'登录状态过期');
            }
            $user = $res['user'];
            $content = I('post.content');
            $articleid = I('post.articleid');
            if ($articleid=='' || !is_numeric($articleid)) {
                returnJson(0,'参数错误');
            }

            if ($content=='') {
                returnJson(0,'请输入留言内容');
            }

            $obj = M('ArticleComment');
            $data = [
                'articleID' => $articleid,
                'memberID' => $user['id'],
                'nickname' => $user['nickname'],
                'headimg' => $user['headimg'],
                'content' => $content,
                'status' => 1,
                'createTime' => time(),
                'digg'=>0
            ];
            $r = $obj->add($data);
            if ($r) {
                returnJson(1,C("SUCCESS_RETURN"));
            } else {
                returnJson(0,'操作失败');
            }                
        }
    }

    public function digg(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $res = $this->checkLogin();
            if ($res['code']==0) {
                returnJson(0,'登录状态过期');
            }
            $user = $res['user'];
            $commentID = I('post.commentID');
            if ($commentID=='' || !is_numeric($commentID)) {
                returnJson(0,'参数错误');
            }
            $map['commentID'] = $commentID;
            $map['memberID'] = $user['id'];
            $obj = M('ArticleDigg');
            $list = $obj->where($map)->find();
            if (!$list) {
                $data = [
                    'commentID' => $commentID,
                    'memberID' => $user['id'],
                    'createTime' => time()
                ];
                $r = $obj->add($data);
                if ($r) {
                    M('ArticleComment')->where(array('id'=>$commentID))->setInc('digg');
                    returnJson(1,C("SUCCESS_RETURN"));
                } else {
                    returnJson(0,'操作失败');
                } 
            }else{
                returnJson(0,'操作失败1');
            }                           
        }
    }

    public function getComment(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $articleid = I('post.articleid');
            $type = I('post.type');         
            $page = I('post.page',1);

            if ($articleid=='' || !is_numeric($articleid)) {
                returnJson(0,'缺少articleid');
            }

            $pagesize = 10;
            $firstRow = $pagesize*($page-1); 
            $map['articleID'] = $articleid;
            $map['status'] = 1;
            $obj = M('ArticleComment');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }
            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id asc')->select();
            returnJson(1,C("SUCCESS_RETURN"),array('comments'=>$list,'next'=>$next));
        }
    }
}