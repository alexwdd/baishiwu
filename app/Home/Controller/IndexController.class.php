<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class IndexController extends BaseController {
    public function index() {   
        $httpAgent = $_SERVER['HTTP_USER_AGENT']; 
        if(strpos(strtolower($httpAgent),"micromessenger")) {
            $is_weixin = 1;
        }else{
            $is_weixin = 0;
        }
        $id = I('get.id');
        $cityID = I('get.cityID');
        $userid = I('get.userid',0);

        switch ($cityID) {
            case '9'://阿德莱德
                $appName = '阿德莱德眼';
                $aUrl = 'https://play.google.com/store/apps/details?id=com.ldw.life';
                $iUrl = 'https://itunes.apple.com/cn/app/id1386824572?l=zh&ls=1&mt=8';
                break;
            case '2'://堪培拉
                $appName = '堪城圈';
                $aUrl = 'https://play.google.com/store/apps/details?id=com.ldw.kanchengquan';
                $iUrl = 'https://itunes.apple.com/cn/app/id1413038662?l=zh&ls=1&mt=8';
                break;
            case '39'://新加坡
                $appName = '新加坡';
                $aUrl = 'https://play.google.com/store/apps/details?id=com.ldw.singapore';
                $iUrl = 'https://itunes.apple.com/cn/app/id1437373175?l=zh&ls=1&mt=8';
                break;
            default:
                $aUrl = '';
                $iUrl = '';
                break;
        }

        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        if (!is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;  
        $list = M('Activity')->where($map)->find();
        if (!$list) {
            $this->error('活动不存在','app://goback');
        }else{  
            $flag = 9;
            if ($list['status'] == 0 ) {
                $flag=0;
            }        

            if ($list['startTime'] > time()) {
                $flag=1;
            }

            if (($list['endTime']+86400) < time()) {
                $flag=2;
            }            

            $shareNumber = $this->getDayShare($userid,$list);
       
            $diffNumber = $list['totalNumber']+$shareNumber-$this->getDayNumber($userid,$list);
            $diffNumber = $diffNumber>=0?$diffNumber:0;

            if ($list['background']=='') {
                $list['background'] = RES.'/image/bg.png';
            }

            $prize = unserialize($list['prize']);
            $image = unserialize($list['image']);
            $this->assign('diffNumber',$diffNumber);
            $this->assign('userid',$userid);
            $this->assign('is_weixin',$is_weixin);
            $this->assign('list',$list);
            $this->assign('prize',$prize);
            $this->assign('image',$image);
            $this->assign('appName',$appName);
            $this->assign('iUrl',$iUrl);
            $this->assign('aUrl',$aUrl);
            $this->assign('flag',$flag);

            $history = M('ActivityLog')->where(array('activityID'=>$id))->order('id desc')->limit(20)->select();
            foreach ($history as $key => $value) {
                $nickname = M('Member')->where(array('id'=>$value['memberID']))->getField("nickname");
                if ($nickname=='') {
                    $nickname = '用户'.$value['memberID'];
                }
                $history[$key]['name'] = $nickname;
            }
            $this->assign('history',$history);
            $this->display();
        }
    }    

    public function play(){
        if ($_POST) {
            $id = I('post.aID');
            $userid = I('post.userid');

            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }

            if ($userid=='' || !is_numeric($userid)) {
                $this->error('参数错误');
            }

            $map['id'] = $id;
            $map['status'] = 1;
            $list = M('Activity')->where($map)->find();
            if (!$list) {
                $this->error('活动不存在');
            }else{
                if ($list['startTime'] > time() || ($list['endTime']+86400)<time()) {
                    $this->error('活动已结束');
                }

                $shareNumber = $this->getDayShare($userid,$list);
                //判断能否抽奖
                $diffNumber = $list['totalNumber'] + $shareNumber -$this->getDayNumber($userid,$list);

                if ($diffNumber < 1) {
                    $this->error('抽奖次数已用完');
                } 

                $goods = $this->getPrize($list);
                if ($goods['number']!=9999) {
                    $status = 1;
                }else{
                    $status = 0;
                }
                $data = array(
                    'memberID'=>$userid,    
                    'activityID'=>$list['id'],
                    'activityName'=>$list['name'],
                    'prize'=>$goods['prize'],
                    'use'=>0,
                    'code'=>createNonceStr(8),
                    'status'=>$status,
                    'createTime'=>time(),
                    );
                $r = M('ActivityLog')->add($data);
                if ($r) {
                    $this->success('操作成功',$goods);
                }else{
                    $this->error('操作失败');
                }                
            }            
        }
    }

    public function my(){
        $userid = I('get.userid');
        if ($userid=='' || !is_numeric($userid)) {
            $this->error('参数错误');
        }
        $map['del'] = 0;
        $map['memberID'] = $userid;
        $list = M('ActivityLog')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        $this->assign('userid',$userid);
        $this->display();
    }

    public function del(){
        $id = I('get.id');
        $userid = I('get.userid');

        if ($id=='' || !is_numeric($id)) {
            die;
        }
        if ($userid=='' || !is_numeric($userid)) {
            die;
        }
        $map['id'] = $id;
        $map['memberID'] = $userid;
        $map['del'] = 0;
        M('ActivityLog')->where($map)->setField('del',1);
    }

    public function share(){
        $aID = I('get.aID');
        $userid = I('get.userid');
        if ($aID!='' && is_numeric($aID)) {
            $map['id'] = $aID;
            $map['status'] = 1;
            $list = M('Activity')->where($map)->find();
            if ($list) {
                return $this->getDayShare($userid,$list);
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    public function getPrize($data){
        $prize = unserialize($data['prize']);
        $number = unserialize($data['number']);
        $image = unserialize($data['image']);
        $probability = unserialize($data['probability']);
        $key = $this->getRand($probability);

        unset($map);
        $map['activityID'] = $data['id'];
        $map['prize'] = $prize[$key];
        $prizeNumber = M('ActivityLog')->where($map)->count();
      
        //echo $key.'-'.$prizeNumber.'<br/>';
        if ($prizeNumber >= $number[$key]) {
            return $this->getPrize($data);
        }else{
            return [
                'prize'=>$prize[$key],
                'image'=>$image[$key],
                'number'=>$number[$key],
                'key'=>$key
            ];
        }
    }
    
    public function getRand($proArr) { 
        $result = '';  
        //概率数组的总概率精度 
        $proSum = array_sum($proArr);  
        //概率数组循环 
        foreach ($proArr as $key => $proCur) {            
            $randNum = mt_rand(1, $proSum); 
            if ($randNum <= $proCur) { 
                $result = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            }
        }
        unset ($proArr);
        return $result; //返回索引
    } 

    //$type 1按总次数 2按每天次数计算
    public function getDayNumber($userid,$activity){   
        if ($activity['type']==2) {
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $map['createTime'] = array('between',array($beginToday,$endToday));
        }         
        $map['memberID'] = $userid;
        $map['activityID'] = $activity['id'];
        return M('ActivityLog')->where($map)->count();   
    }

    public function saveShare(){
        $aID = I('get.aID');
        $userid = I('get.userid');
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));         
        $map['memberID'] = $userid;
        $map['activityID'] = $aID;
        $number = M('ActivityShare')->where($map)->count();
        if ($number==0) {
            $data = array(
                'activityID'=>$aID,
                'memberID'=>$userid,
                'createTime'=>time()
                );
            $r = M('ActivityShare')->add($data);
            if ($r) {
                echo 1;
            }else{
                echo 0;
            }            
        }else{
            echo 0;
        }
    }

    //获得当前是否分享过
    public function getDayShare($userid,$activity){
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));         
        $map['memberID'] = $userid;
        $map['activityID'] = $activity['id'];
        $number = M('ActivityShare')->where($map)->count();
        if ($number==0) {
            return 0;            
        }else{
            return 1;
        }
    }
}