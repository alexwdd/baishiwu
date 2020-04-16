<?php
namespace V3\Controller;

class WeixinController extends CommonController {

    public $weixin = ['appID'=>'wxd4334941c3c1c498','appsecret'=>'cbbec6f8b6900e35c595064e1b5f5362'];
    public $wxapp = ['appID'=>'wxe4d659c83d5ec533','appsecret'=>'3aa678d6e1b3e0bb5dc0b19d9dfb524d'];

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

    //获取分类
    public function getSortName($cid){
        $map['id'] = $cid;
        return M('Category')->where($map)->getField('name');die;
    }

    public function wxconfig(){
        if ($_POST) {
            if (!checkFormDate()){returnJson('-1','未知错误');}
            returnJson('0',C("SUCCESS_RETURN"),array('msg'=>C('site.wxapp'))); 
        }
    }

    public function appShow(){
        if ($_POST) {
            if (!checkFormDate()){returnJson('-1','未知错误');}
            returnJson('0',C("SUCCESS_RETURN"),array('show'=>1)); 
        }
    }

    //获取该城市选择的模块
    public function getmain(){
        if ($_POST) {
            if (!checkFormDate()){returnJson('-1','未知错误');}

            $cityID = I('post.cityID');
            $adID = I('post.adID');
            if ($cityID!='' && is_numeric($cityID)) {
                $map['cityID']=$cityID;
            }
            if ($adID!='' && is_numeric($adID)) {
                $map['cid']=$adID;
            }
            $map['id'] = array('neq',38);
            $ads = M('Ad')->field('name as title,articleid,type,createTime as time,image,url,wxurl')->where($map)->order('sort asc , id desc')->select();
            foreach ($ads as $key => $value) {
                $ads[$key]['time'] = date("Y-m-d",$value['time']);
                $ads[$key]['image'] = C('site.domain').$value['image'];
            }

            unset($map);
            $map['cityID'] = $cityID;   
            $map['fid'] = 0;
            if($cityID==9){
                //$map['cid'] = array('not in',[1,4,7,94,142,152]);
                $map['type'] = array('not eq','article');
            }
            $list = M('CityCate')->field('cid,name,icon')->where($map)->order('sort asc')->select();
            foreach ($list as $key => $value) {
                unset($r);
                $r = $this->getPy($value['cid']);
                if ($r) {
                    $list[$key]['type'] = $r['py'];
                    $list[$key]['flag'] = 0;
                }else{
                    $list[$key]['type'] = 'article';
                    $res = M('Category')->where(array('fid'=>$value['cid']))->count();
                    if ($res>0) {
                        $list[$key]['flag'] = 1;
                    }else{
                        $list[$key]['flag'] = 0;
                    }
                }

                if ($list[$key]['type']=='article') {
                    unset($map);
                    $map['status'] = 1;
                    $map['del'] = 0;
                    $map['path'] = array('like','0-'.$list[$key]['cid'].'-%');
                    $map['cityID'] = $cityID;
                    $child = M('Article')->field('id,picname as thumb,title,createTime as time,hit,from,url')->where($map)->limit(5)->order('top desc,id desc')->select();
                    foreach ($child as $k => $val) {
                        if ($val['thumb']!='') {
                            $child[$k]['thumb'] = C('site.domain').$val['thumb'];
                        }
                        $child[$k]['time'] = date("Y-m-d",$val['time']);
                        $child[$k]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$val['time']).'/'.$val['id'].'.html';
                    }
                }elseif($list[$key]['type']=='chat'){
                    
                }else{
                    $db = $this->getModel($list[$key]['type']);
                    unset($map);
                    $map['status'] = 1;
                    $map['cityID'] = $cityID;   
                    $child = M($db['db'])->where($map)->order('isTop desc,articleid desc')->limit(6)->select();
                    foreach ($child as $k => $val) {
                        unset($child[$k]['detail']);
                        unset($child[$k]['content']);
                        $child[$k]['thumb_b'] = getRealUrl($val['thumb_b']);
                        $child[$k]['sortName'] = $this->getSortName($val['sort']);
                    }                    
                }
                foreach ($child as $k => $val) {
                    $child[$k] = $this->setTagPrice($val,$list[$key]['type']);
                }
                $list[$key]['child'] = $child;
                $list[$key]['icon'] = getRealUrl($value['icon']);
            }

            unset($map);
            $map['cityID'] = $cityID;
            $map['show'] = 1;
            $agent = M('Agent')->field('id,logo,name')->where($map)->order('sort asc,id desc')->select();
            foreach ($agent as $key => $value) {
                $temp = [
                    'type'=>'mall',
                    'name'=>$value['name'],
                    'icon'=>'http://' . $_SERVER['HTTP_HOST'] . $value['logo'],
                    'url'  =>'http://wx.worldmedia.top/adelaide/store?agentid='.$value['id']
                ];
                array_push($list, $temp);
            }
            returnJson('0',C("SUCCESS_RETURN"),array('ads'=>$ads,'cate'=>$list));       
        }       
    }

    public function newscate(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $cityID = I('post.cityID');
            $cid = I('post.cid');
            if ($cityID==0) {
                returnJson('-1','缺少cityID');
            }
            if ($cid=='' || !is_numeric($cid)) {
                returnJson('-1','缺少参数');
            }

            $cateName = M('Category')->where(array('model'=>1,'id'=>$cid))->getField('name');
            if (!$cateName) {
                returnJson('-1','栏目不存在');
            }
            $list = M('Category')->field('id,picname,name')->where(array('fid'=>$cid))->select();       
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getRealUrl($value['picname']); 
            }
            returnJson(0,'success',['data'=>$list,'cateName'=>$cateName]);
        }
    }

    public function news()
    {    
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $cityID = I('post.cityID');
            $cid = I('post.cid');
            if ($cityID==0) {
                returnJson('-1','缺少cityID');
            }
            if ($cid=='' || !is_numeric($cid)) {
                returnJson('-1','缺少参数');
            }

            $cateName = M('Category')->where(array('model'=>1,'id'=>$cid))->getField('name');
            if (!$cateName) {
                returnJson('-1','栏目不存在');
            }
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('Article');
            $map['cid'] = $cid;
            $map['cityID'] = $cityID;
            $map['status'] = 1;
            $map['del'] = 0;
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }
            $list = $obj->field('id,title,picname,url,createTime,hit')->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();            
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getRealUrl($value['picname']);               
                $list[$key]['createTime'] = date("Y-m-d",$value['createTime']);
                $list[$key]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$value['createTime']).'/'.$value['id'].'.html';
            }
            returnJson(0,'success',['next'=>$next,'data'=>$list,'cateName'=>$cateName]);
        }
    }

    public function view(){  
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $id = I('post.id');
            $cityID = I('post.cityID');
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','参数错误');
            }

            $map['id']=$id;
            $map['del'] = 0;
            $map['status'] = 1;
            $list = M('Article')->where($map)->find();
            M('Article')->where($map)->setInc('hit');
            $this->setHit($cityID);
            if (!$list) {
                returnJson('-1','信息不存在');
            } else {
                $list['createTime'] = date("Y-m-d",$list['createTime']);
                $list['content'] = htmlspecialchars_decode($list['content']);

                //相关帖子
                unset($map);
                $map['id'] = array('neq',$id);
                $map['cityID'] = $cityID;
                $map['cid'] = $list['cid'];
                $map['status'] = 1;
                $map['del'] = 0;
                $about = M('Article')->field('id,title,picname,createTime')->where($map)->limit(6)->order('id desc')->select();
                foreach ($about as $key => $value) {
                    $about[$key]['picname'] = getRealUrl($value['picname']);
                    $about[$key]['createTime'] = date("Y/m/d",$value['createTime']);             
                }

                //广告
                unset($map);
                $map['cityID'] = $cityID;
                $map['cid'] = 127;
                $ad1 = M('Ad')->field('name,image,type,articleid,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                if ($ad1) {
                    foreach ($ad1 as $key => $value) {
                         $ad1[$key]['image'] = getRealUrl($value['image']);
                    }                   
                }else{
                    $ad1 = [];
                }
                $map['cid'] = 128;
                $ad2 = M('Ad')->field('name,image,type,articleid,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                if ($ad2) {
                    foreach ($ad2 as $key => $value) {
                         $ad2[$key]['image'] = getRealUrl($value['image']);
                    }                   
                }else{
                    $ad2 = [];
                } 
                $map['cid'] = 129;
                $ad3 = M('Ad')->field('name,image,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                $quick = [];
                $q = [];
                $i = 1;
                foreach ($ad3 as $key => $value) {
                    $ad3[$key]['image'] = getRealUrl($value['image']);
                    array_push($q,$ad3[$key]);
                    if ($i%10==0) {
                        array_push($quick,$q);
                        $q = [];
                    }
                    $i++;               
                }
                if (count($q)>0) {
                    array_push($quick,$q);
                }
                returnJson('0',C('SUCCESS_RETURN'),['data'=>$list,'ad1'=>$ad1,'ad2'=>$ad2,'quick'=>$quick,'about'=>$about]);
            }
        }
    }

    public function setTagPrice($info,$type){           
        $info['tag1'] = '';
        $info['tag2'] = '';
        $info['tag3'] = '';
        $info['tag4'] = '';
        $info['price'] = strtr($info['price'], '$', '');
        switch ($type) {
            case 'tc':    
                $info['tag1'] = $info['sortName'];
                if ($info['begin'] !='' ) {
                    $info['tag2'] = '开始：'.$info['begin']; 
                }                    
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }elseif($info['price']==0.1) {
                    $info['price'] = '自助';
                }else{
                    $info['price'] = '$'.$info['price'];
                }  
                break;
            case 'zf':
                $info['tag1'] = $info['sortName'];
                if ($info['houseType'] ==0 ) {
                    $info['tag2'] = $info['singleType']; 
                    if ($info['Into'] !='' ) {
                        $info['tag3'] = '入住：'.$info['Into']; 
                    }
                }else{
                    if ($info['bedrooms'] >0){
                        $info['tag2'] .= $info['bedrooms']+'卧';
                    }
                    if ($info['toilets'] >0){
                        $info['tag2'] .= $info['toilets']+'卫';
                    }
                    if ($info['showers'] >0){
                        $info['tag2'] .= $info['showers']+'浴';
                    }
                    if ($info['parks'] >0){
                        $info['tag2'] .= $info['parks']+'车位';
                    }       
                }
                $info['tag4'] = $info['remark'];
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;  
            case 'sp':
                $info['tag1'] = $info['sortName'];
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;  
            case 'zp':
                $info['tag1'] = $info['sortName'];
                if ($info['price']==0) {
                    $info['price'] = '面议';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;
            case 'esc':
                $info['tag1'] = $info['sortName'];
                $info['tag2'] = $info['trans'];
                $info['tag3'] = $info['year'];
                if($info['mileage']>0){
                    $info['tag4'] = $info['mileage'].'万公里';
                }
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;
            case 'ms':
                $info['tag1'] = $info['sortName'];
                $info['tag2'] = $info['feature'];
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;
            case 'sh':
                $info['tag1'] = $info['sortName'];
                $info['tag2'] = $info['feature'];
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break; 
            case 'xp':
                $info['tag1'] = $info['sortName'];
                $info['tag2'] = $info['feature'];
                if ($info['price']==0) {
                    $info['price'] = '详情请咨询';
                }else{
                    $info['price'] = '$'.$info['price'];
                }
                break;              
        }
        return $info;
    }

    public function infolist(){      
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
           
            $sort = I('post.sort');
            $cityID = I('post.cityID');
            $type = I('post.type');    
            $jobtype = I('post.jobType');    
            $houseType = I('post.houseType');    
            $keyword = I('post.keyword');
            $singleType = I('post.singleType');
            $subway = I('post.subway');
            $page = I('post.page',1);

            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','缺少cityID');
            }

            $arr = $this->getModel($type);
            if (!$arr) {
                returnJson('-1','type类型错误');
            }

            $obj = M($arr['db']);

            $where['fid'] = $arr['fid'];
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

            if ($jobtype!='' && is_numeric($jobtype)) {
                $map['jobtype'] = $jobtype;
            }

            if ($houseType!='' && is_numeric($houseType)) {
                $map['houseType'] = $houseType;
            }

            if ($keyword!='') {
                $map['title'] = array('like','%'.$keyword.'%');
            }

            if ($singleType!='') {
                $map['singleType'] = $singleType;
            }

            if ($sort>0) {
                $map['sort'] = $sort;
            }

            $map['cityID'] = $cityID;
            if ($subway!='') {
                $subway = explode(",",$subway);
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
            

            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 
            $map['status'] = 1;
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('isTop desc , updateTime desc , articleid desc')->select();            
            foreach ($list as $key => $value) {
                $list[$key]['sortName'] = $this->getSortName($value['sort']);                
                unset($list[$key]['content']);
                unset($list[$key]['detail']); 
                $list[$key]['thumb_b'] = getRealUrl($value['thumb_b']);
                $list[$key]['thumb_s'] = getRealUrl($value['thumb_s']);
                $list[$key]['createTime'] = date("Y/m/d",$value['createTime']);
                $list[$key] = $this->setTagPrice($list[$key],$type);
            }
            
            returnJson(0,'success',['next'=>$next,'data'=>$list,'cateName'=>$arr['name'],'cate'=>$cate,'quick'=>$quick]);
        }
    }

    //获取帖子信息
    public function getinfo(){
        if (IS_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $articleid = I('post.articleid');
            $cityID = I('post.cityID');
            $type = I('post.type');

            $arr = $this->getModel($type);
            if (!$arr) {
                returnJson('-1','type类型错误');
            }
            $map['articleid'] = $articleid;         
            $list = $obj = M($arr['db'])->where($map)->find();
            if ($list){
                $this->setHit($cityID);
                M($arr['db'])->where($map)->setInc('hit');
                $list['thumb_s'] = getRealUrl($list['thumb_s']);
                $list['thumb_b'] = getRealUrl($list['thumb_b']);
                $list['detail'] = str_replace("\n","<br/>",$list['detail']);
                $list['content'] = htmlspecialchars_decode($list['content']);

                if ($list['image']!='') {                   
                    $list['images'] = explode(";", $list['image']);
                    for ($i=0; $i < count($list['images']); $i++) { 
                        $list['images'][$i] = getRealUrl($list['images'][$i]);
                    }
                }else{
                    $list['images'] = array();
                }
                $list['sortName'] = $this->getSortName($list['sort']);

                $list = $this->setTagPrice($list,$type);

                //相关帖子
                unset($map);
                $map['id'] = array('neq',$articleid);
                $map['cityID'] = $cityID;
                $map['status'] = 1;
                $about = M($arr['db'])->where($map)->limit(6)->order('articleid desc')->select();
                foreach ($about as $key => $value) {
                    $about[$key] = $this->setTagPrice($value,$type);

                    unset($about[$key]['content']);
                    unset($about[$key]['detail']);
                    unset($about[$key]['thumb_b']);
                    $about[$key]['sortName'] = $this->getSortName($value['sort']);
                    $about[$key]['thumb_s'] = getRealUrl($value['thumb_s']);
                    $about[$key]['createTime'] = date("Y/m/d",$value['createTime']); 

                }

                //评论
                unset($map);
                $map['type'] = $type;
                $map['articleid'] = $articleid;
                $obj = M('Comment');
                $list['comments'] = $obj->field('id as commentid,nickname,headimg,comments as content,createTime')->where($map)->limit(3)->order('id desc')->select();
                foreach ($list['comments'] as $key => $value) {
                    $list['comments'][$key]['createTime'] = date("Y-m-d",$value['createTime']);
                }

                //广告
                unset($map);
                $map['cityID'] = $cityID;
                $map['cid'] = 127;
                $ad1 = M('Ad')->field('name,image,type,articleid,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                if ($ad1) {
                    foreach ($ad1 as $key => $value) {
                         $ad1[$key]['image'] = getRealUrl($value['image']);
                    }                   
                }else{
                    $ad1 = [];
                }
                $map['cid'] = 128;
                $ad2 = M('Ad')->field('name,image,type,articleid,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                if ($ad2) {
                    foreach ($ad2 as $key => $value) {
                         $ad2[$key]['image'] = getRealUrl($value['image']);
                    }                   
                }else{
                    $ad2 = [];
                } 
                $map['cid'] = 129;
                $ad3 = M('Ad')->field('name,image,url,wxurl')->where($map)->order('sort asc,id desc')->select();
                $quick = [];
                $q = [];
                $i = 1;
                foreach ($ad3 as $key => $value) {
                    $ad3[$key]['image'] = getRealUrl($value['image']);
                    array_push($q,$ad3[$key]);
                    if ($i%10==0) {
                        array_push($quick,$q);
                        $q = [];
                    }
                    $i++;               
                }
                if (count($q)>0) {
                    array_push($quick,$q);
                }

                //设施
                $sheshi = M("OptionItem")->where(array('cate'=>7))->field("id as itemid,name,value")->select();
                return returnJson('0',C('SUCCESS_RETURN'),['data'=>$list,'ad1'=>$ad1,'ad2'=>$ad2,'quick'=>$quick,'about'=>$about,'sheshi'=>$sheshi]);
            }else{
                returnJson('-1','不存在的信息');
            }
        }
    }
    
    public function topcomm(){      
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $cityID = I('post.cityID');
            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','缺少cityID');
            }
            $map['cityID'] = $cityID;
            $obj = M('Commend');
            $list = $obj->where($map)->limit(20)->order('sort asc , articleid desc')->select();
            foreach ($list as $key => $value) {
                if ($value['type']=='article') {
                    $db = ['db'=>'Article'];
                    $where['id'] = $value['articleid'];
                    $res = M($db['db'])->field('url,hit,createTime')->where($where)->find(); 
                    $list[$key]['hit'] = $res['hit'];
                    $list[$key]['url'] = $res['url'];
                    $list[$key]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$res['createTime']).'/'.$value['articleid'].'.html';
                }else{
                    $db = $this->getModel($value['type']);
                    $where['articleid'] = $value['articleid']; 
                    $res = M($db['db'])->field('html,hit')->where($where)->find(); 
                    $list[$key]['hit'] = $res['hit'];
                    $list[$key]['url'] = '';
                    $list[$key]['html'] = $res['html'];
                }                
                
                $list[$key]['image'] = getRealUrl($value['image']);
                if ($value['userid']==0) {
                    $list[$key]['user'] = array('nickname'=>'','headimg'=>'');
                }else{
                    $list[$key]['user'] = M('Member')->where(array('id'=>$value['userid']))->field('nickname,headimg')->find();
                }                
            }
            returnJson(0,'success',$list);
        }
    }

    public function getcomm(){      
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $cityID = I('post.cityID');
            $page = I('post.page',1);

            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','缺少cityID');
            }
            $map['cityID'] = $cityID;
            
            $obj = M('Commend');
            $page = I('post.page/d',1); 
            $pagesize =20;
            $firstRow = $pagesize*($page-1); 

            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('sort asc , articleid desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['image'] = getRealUrl($value['image']);
                if ($value['userid']==0) {
                    $list[$key]['user'] = array('nickname'=>'','headimg'=>'');
                }else{
                    $list[$key]['user'] = M('Member')->where(array('id'=>$value['userid']))->field('nickname,headimg')->find();
                }                
            }
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    public function wxsdk(){
        if (IS_POST) {
            $jsapi_ticket = $this->get_jsapi_ticket(); 
            $noncestr = createNonceStr();
            $timestamp = time();
            $localUrl = I('post.reqUrl');
            $string = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$localUrl;
            $signature = sha1($string);
            $data = [
                'appID'=>$this->weixin['appID'],
                'noncestr'=>$noncestr,
                'timestamp'=>$timestamp,
                'signature'=>$signature
            ];
            echo json_encode($data);
        }        
    }

    public function getToken(){
        if (S('AccessToken')) {
            return S('AccessToken');
        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->weixin['appID'].'&secret='.$this->weixin['appsecret'];
            $result = $this->https_post($url);
            $result = json_decode($result,true);
            S('AccessToken',$result['access_token'],1200);
            return S('AccessToken');
        }
    }

    //获得jsTicket
    public function get_jsapi_ticket(){
        if (S('JsTicket')) {
            return S('JsTicket');
        }else{  
            $access_token = $this->getToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $con = $this->https_post($url);
            $con = json_decode($con);
            $jsapi_ticket = $con->ticket;
            S('JsTicket',$jsapi_ticket,1200);
            return S('JsTicket');
        }
    }
  
    public function kefu(){
        /*$echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }*/
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = json_decode($postStr,true);
           
        $keyword = trim($postObj['Content']);
        $msgType = $postObj['MsgType'];
        $toUserName = $postObj['FromUserName'];
        $fromUserName = $postObj['ToUserName'];

        /*$str = '关注事件'.$postObj.'--'.$keyword;
        $handle = fopen ("1.txt","w");
        if(!fwrite ($handle,$str)){
            die ("生成文件失败！");
        }*/
        
        if($msgType=='text' && $keyword=='2'){
            $content = '<a href="http://www.baidu.com">点击下载「阿德莱德眼」APP，查看更多同城资讯～</a>';
            $textTpl = [
                'touser'=>$toUserName,
                'msgtype'=>'text',
                'text'=>['content'=>$content]
            ];
            $accessToken = $this->get_app_accessToken();
            $json = json_encode($textTpl, JSON_UNESCAPED_UNICODE);
            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $accessToken;
            
            //以'json'格式发送post的https请求
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            if (!empty($json)){
                curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
            $output = curl_exec($curl);
            if (curl_errno($curl)) {
                echo 'Errno'.curl_error($curl);//捕抓异常
            }
            curl_close($curl);
            if($output == 0){
                echo 'success';exit;
            }
        }
    }
  
    public function get_app_accessToken(){
        /* 在有效期，直接返回access_token */
        if(S('wxapp_access_token')){
            return S('wxapp_access_token');
        }
        /* 不在有效期，重新发送请求，获取access_token */
        else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->wxapp['appID'].'&secret='.$this->wxapp['appsecret'];
            $result = $this->https_post($url);
            $res = json_decode($result,true);   //json字符串转数组    
            if($res){
                S('wxapp_access_token',$res['access_token'],3600);
                return S('wxapp_access_token');
            }else{
                return 'api return error';
            }
        }
    }

    //检测签名是否正确返回bool值
    private function checkSignature(){
       
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
                
        $token = '4d659c83d5ec5331';
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}