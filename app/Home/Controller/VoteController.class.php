<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class VoteController extends BaseController {

	public function _initialize() {
		parent::_initialize();
	}    

    public function index() {
    	$voteID = I('get.voteID',0);
    	$map['id'] = $voteID;
    	$map['show'] = 1;
    	$list = M('VoteSubject')->where($map)->find();
    	if(!$list){die;}

        if($list['startTime']>time()){            
            $list['msg'] = '活动尚未开始，开始时间'.date("Y-m-d",$list['startTime']); 
        }elseif(($list['endTime']+86399)<time()){
            $list['flag'] = 1;
            $list['msg'] = '活动已结束，结束时间'.date("Y-m-d",$list['endTime']); 
        }else{
            $list['msg'] = '活动已开始，结束时间'.date("Y-m-d",$list['endTime']); 
        }

        $item = M("VoteItem")->where(['voteId'=>$list['id']])->select();
        $this->assign('item',$item);
    	$this->assign('list',$list);
        $this->display();
    }
}