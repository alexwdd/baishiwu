<?php
namespace V1\Controller;
use Common\Controller\BaseController;

class IndexController extends BaseController {

	public function index() {
        $this->display();
	} 

	public function download(){
		$httpAgent = $_SERVER['HTTP_USER_AGENT']; 
        if(strpos(strtolower($httpAgent),"micromessenger")) {
            $is_weixin = 1;
        }else{
            $is_weixin = 0;
        }
        $this->assign('is_weixin',$is_weixin);
		$this->display();
	}
}