<?php
namespace V4\Controller;

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
            $map['cid'] = array('neq',152);
            $list = M('CityCate')->field('cid,name')->where($map)->order('sort asc')->select();
            foreach ($list as $key => $value) {
                unset($r);
                $r = $this->getPy($value['cid']);
                if ($r) {
                    $list[$key]['type'] = $r['py'];
                }else{
                    $list[$key]['type'] = '';
                }

                if ($list[$key]['type']!='') {
                    $db = $this->getModel($list[$key]['type']);
                    $map['status'] = 1;
                    $map['cityID'] = $cityID;
                    $number = M($db['db'])->where($map)->count();
                    $list[$key]['num'] = $number;
                }else{
                    $map['status'] = 1;
                    $map['del'] = 0;
                    $map['path'] = array('like','0-'.$list[$key]['cid'].'-');
                    $map['cityID'] = $cityID;
                    $number = M('Article')->where($map)->count();
                    $list[$key]['num'] = $number;
                    if($value['cid']==94){
                        $list[$key]['type'] = 'news';
                    }
                    if($value['cid']==142 || $value['cid']==148){
                        $list[$key]['type'] = 'gl';
                    }
                    if($value['cid']==113){
                        $list[$key]['type'] = 'marriage';
                    }
                }
                //unset($list[$key]['cid']);
            }
            unset($map);
            $map['cityID'] = $cityID;
            $map['show'] = 1;
            $agent = M('Agent')->field('id,logo,name')->where($map)->order('sort asc,id desc')->select();
            foreach ($agent as $key => $value) {
                $temp = [
                    'type'=>'mall',
                    'name'=>$value['name'],
                    'image'=>'http://' . $_SERVER['HTTP_HOST'] . $value['logo'],
                    //'url'  =>'http://' . $_SERVER['HTTP_HOST'] .'/store/?agentid='.$value['id']
                    'url'  =>'http://wx.worldmedia.top/adelaide/store?agentid='.$value['id']
                ];
                array_push($list, $temp);
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

    public function getmain(){
        if (IS_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            $cityID = I('post.cityID');
            $map['cityID'] = $cityID;
            $map['fid'] = 0;
            //$map['cid'] = array('neq',94);
            $list = M('CityCate')->where($map)->order('sort asc')->select(); 
            $result = array();
            foreach ($list as $key => $value) {                
                if ($value['cid']==94) {
                    $obj = M('Article');
                    unset($map);
                    if ($cityID!='' && is_numeric($cityID)) {
                        $map['cityID']=$cityID;
                    }
                    $map['del'] = 0;
                    $map['status'] = 1;
                    $map['cid'] = 94;
                    $list = $obj->field('id,picname as thumb,title,createTime as time,hit,from,url')->where($map)->limit(6)->order('top desc,id desc')->select();
                    foreach ($list as $k => $val) {
                        if ($val['thumb']!='') {
                            $list[$k]['thumb'] = C('site.domain').$val['thumb'];
                        }
                        $list[$k]['time'] = date("Y-m-d",$val['time']);
                        $list[$k]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$val['time']).'/'.$val['id'].'.html';
                        //$list[$key]['sortName'] = $this->getSortName($val['sort']);
                    }          
                    array_push($result,array('news'=>$list));
                }elseif($value['cid']==113){
                    $obj = M('Article');
                    unset($map);
                    unset($map);
                    if ($cityID!='' && is_numeric($cityID)) {
                        $map['cityID']=$cityID;
                    }
                    $map['del'] = 0;
                    $map['status'] = 1;
                    $map['cid'] = 113;
                    $list = $obj->field('id,picname as thumb,title,createTime as time,hit,from,url')->where($map)->limit(5)->order('top desc,id desc')->select();
                    foreach ($list as $k => $val) {
                        if ($val['thumb']!='') {
                            $list[$k]['thumb'] = C('site.domain').$val['thumb'];
                        }
                        $list[$k]['time'] = date("Y-m-d",$val['time']);
                        $list[$k]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$val['time']).'/'.$val['id'].'.html';
                        //$list[$key]['sortName'] = $this->getSortName($val['sort']);
                    }          
                    array_push($result,array('marriage'=>$list));
                }elseif($value['cid']==142 || $value['cid']==148){
                    $obj = M('Article');
                    unset($map);
                    unset($map);
                    if ($cityID!='' && is_numeric($cityID)) {
                        $map['cityID']=$cityID;
                    }
                    $map['del'] = 0;
                    $map['status'] = 1;
                    $map['cid'] = 113;
                    $list = $obj->field('id,picname as thumb,title,createTime as time,hit,from,url')->where($map)->limit(5)->order('top desc,id desc')->select();
                    foreach ($list as $k => $val) {
                        if ($val['thumb']!='') {
                            $list[$k]['thumb'] = C('site.domain').$val['thumb'];
                        }
                        $list[$k]['time'] = date("Y-m-d",$val['time']);
                        $list[$k]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$val['time']).'/'.$val['id'].'.html';
                    }          
                    array_push($result,array('gl'=>$list));
                }else{
                    $r = $this->getPy($value['cid']);                    
                    $obj = M($r['db']);
                    unset($map);
                    if ($cityID!='' && is_numeric($cityID)) {
                        $map['cityID']=$cityID;
                    }
                    $map['status'] = 1;                    
                    if ($r['py']=='chat') {                        
                        $list = $obj->where($map)->order('isTop desc,id desc')->limit(6)->select();
                    }else{
                        $list = $obj->where($map)->order('isTop desc,articleid desc')->limit(6)->select();
                    }                    
                    foreach ($list as $k => $val) {
                        unset($list[$k]['detail']);
                        unset($list[$k]['content']);
                        $list[$k]['sortName'] = $this->getSortName($val['sort']);
                        $list[$k]['thumb_b'] = getRealUrl($val['thumb_b']);
                    }
                    array_push($result,array($r['py']=>$list));
                }
            }

            returnJson('0',C("SUCCESS_RETURN"),$result);
        }
    }

    //获取分类
    public function getSortName($cid){
        $map['id'] = $cid;
        return M('Category')->where($map)->getField('name');die;
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

            unset($map);
            $map['cityID'] = $cityID;
            $obj = M('Commend');
            $commend = $obj->where($map)->limit(20)->order('sort asc , articleid desc')->select();
            foreach ($commend as $key => $value) {
                if ($value['type']=='article') {
                    $db = ['db'=>'Article'];
                    $where['id'] = $value['articleid'];
                    $res = M($db['db'])->field('url,hit,createTime')->where($where)->find(); 
                    $commend[$key]['hit'] = $res['hit'];
                    $commend[$key]['url'] = $res['url'];
                    $commend[$key]['html'] = C('site.domain').'/HTML/Article/'.date("ym",$res['createTime']).'/'.$value['articleid'].'.html';
                }else{
                    $db = $this->getModel($value['type']);
                    $where['articleid'] = $value['articleid']; 
                    $res = M($db['db'])->field('html,hit')->where($where)->find(); 
                    $commend[$key]['hit'] = $res['hit'];
                    $commend[$key]['url'] = '';
                    $commend[$key]['html'] = $res['html'];
                }

                $imgInfo = getimagesize('.'.$value['image']);
                $commend[$key]['width'] = $imgInfo[0];
                $commend[$key]['height'] = $imgInfo[1];
                
                $commend[$key]['image'] = getRealUrl($value['image']);
                if ($value['userid']==0) {
                    $commend[$key]['user'] = array('nickname'=>'','headimg'=>'');
                }else{
                    $commend[$key]['user'] = M('Member')->where(array('id'=>$value['userid']))->field('nickname,headimg')->find();
                }                
            }
            returnJson(0,'success',['cateName'=>$cateName,'cate'=>$list,'commend'=>$commend,]);
        }
    }
    
}