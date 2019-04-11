<?php
namespace V3\Controller;

class MarriageController extends CommonController {

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
}