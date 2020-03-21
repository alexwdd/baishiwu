<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class VoteController extends BaseController {

	public function _initialize() {
        $httpAgent = $_SERVER['HTTP_USER_AGENT']; 
        if(strpos(strtolower($httpAgent),"micromessenger")) {
            $is_weixin = 1;
        }else{
            $is_weixin = 0;
        }

        $app['appName'] = '阿德莱德眼';
        $app['aUrl'] = 'https://play.google.com/store/apps/details?id=com.ldw.life';
        $app['iUrl'] = 'https://itunes.apple.com/cn/app/id1386824572?l=zh&ls=1&mt=8';
        $app['download'] = 'http://www.worldmedia.top/download/adelaide.apk';

        $this->assign('app',$app);
        $this->assign('is_weixin',$is_weixin);
		parent::_initialize();
	}    

    public function index() {
        $voteID = I('get.voteID',0);
    	$keyword = I('get.keyword','');
    	$map['id'] = $voteID;
    	$map['show'] = 1;
    	$list = M('VoteSubject')->where($map)->find();
    	if(!$list){die;}

        if($list['startTime']>time()){            
            $list['msg'] = '活动尚未开始，开始时间'.date("Y-m-d",$list['startTime']); 
            $list['flag'] = 1;
        }elseif(($list['endTime']+86399)<time()){
            $list['flag'] = 1;
            $list['msg'] = '活动已结束，结束时间'.date("Y-m-d",$list['endTime']); 
        }else{
            $list['flag'] = 0;
            $list['msg'] = '活动已开始，结束时间'.date("Y-m-d",$list['endTime']); 
        }

        if($keyword!=''){
            $where['name'] = ['like','%'.$keyword.'%'];
        }
        $where['voteId'] = $list['id'];

        $item = M("VoteItem")->where($where)->limit(10)->order('id asc')->select();
        $this->assign('item',$item);
    	$this->assign('list',$list);

        $total['number'] = M("VoteItem")->where(['voteId'=>$list['id']])->count();
        $total['person'] = M("VoteLog")->where(['voteId'=>$list['id']])->count();

        $this->assign('total',$total);
        $this->display();
    }

    public function ajax(){
        if (IS_POST) {
            if (!checkFormDate()){returnJson('-1','未知错误');}

            $voteId = I('post.voteId');
            $page = I('post.page');
            $keyword = I('post.keyword','');

            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $map['voteId'] = $voteId;
            if($keyword!=''){
                $where['name'] = ['like','%'.$keyword.'%'];
            }
            $obj = M('VoteItem');

            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id asc')->select();  
            foreach ($list as $key => $value) {
                
            }
            returnJson(1,'success',['next'=>$next,'data'=>$list]);
        }
    }

    public function checkToken($token){
        $map['token'] = $token;
        $map['disable'] = 0;
        //$map['outTime'] = array('gt',time());
        $user = M('Member')->where($map)->find();    
        if (!$user) {
            return false;
        }else{
            //$data['outTime'] = time()+2592000; 
            //M('Member')->where($map)->save($data);   
            return $user;
        }
    }

    public function submit(){
        if (IS_POST) {
            $token = I('post.token');
            $ids = I('post.ids');
            $voteId = I('post.voteId');

            if($token==""){
                returnJson(0,"请先下载APP");
            }

            $user = $this->checkToken($token);
            if(!$user){
                returnJson(0,"请先登录");
            }
            if($ids==""){
                returnJson(0,"请选择投票项");
            }

            $ids = explode("-", $ids);
            
            $map['id'] = $voteId;
            $map['show'] = 1;
            $vote = M("VoteSubject")->where($map)->find();
            if(!$vote){
                returnJson(0,"投票主题不存在");
            }

            if ($vote['startTime'] > time()) {
                returnJson(0,"投票未开始");
            }

            if (time() > ($vote['endTime'] + 86399)) {
                returnJson(0,"投票已结束");
            }            

            if($vote['selectType']==1 && count($ids)>1){
                returnJson(0,"只能选择一个投票项");
            }

            $todayNumber = $this->getTodayDiggNumber($user);

            if ($todayNumber >= $vote['dayNumber']) {
                returnJson(0,"每天只能投票".$vote['dayNumber']."次");
            }

            $getTotalNumber = $this->getTotalNumber($user);
            if ($getTotalNumber >= $vote['personNumber']) {
                returnJson(0,"本次活动每人只能投票".$vote['personNumber']."次");
            }

            $temp['itemID'] = $ids;
            $temp['voteId'] = $voteId;
            $temp['memberID'] = $user['id'];
            $temp['createTime'] = time();

            $res = M('VoteLog')->add($temp);
            if($res){
                foreach ($ids as $key => $value) {
                     M('VoteItem')->where(array('id'=>$value))->setInc('poll');
                }
                returnJson(1,"感谢您的参与");
            }else{
                returnJson(0,"投票失败");
            }           
        }
    }

    public function getTodayDiggNumber($user){
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $map['memberID'] = $user['id'];
        $todayNumber = M('VoteLog')->where($map)->count();  
        return $todayNumber;      
    }

    public function getTotalNumber($user){
        $map['memberID'] = $user['id'];
        $totalNumber = M('VoteLog')->where($map)->count();  
        return $totalNumber;      
    }

    public function phb(){
        $voteID = I('get.voteID',0);
        $map['id'] = $voteID;
        $map['show'] = 1;
        $list = M('VoteSubject')->where($map)->find();
        if(!$list){
            $this->error("活动主题不存在");
        }

        if(($list['endTime']+86399) > time()){
            $this->error("活动尚未结束，不能查看"); 
        }

        $item = M("VoteItem")->where(['voteId'=>$list['id']])->order('poll desc')->select();
        $this->assign('item',$item);
        $this->assign('list',$list);
        $this->display();
    }
}