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

        $item = M("VoteItem")->where(['voteId'=>$list['id']])->select();
        $this->assign('item',$item);
    	$this->assign('list',$list);
        $this->display();
    }
}