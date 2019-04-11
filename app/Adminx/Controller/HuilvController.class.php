<?php
namespace Adminx\Controller;

class HuilvController extends AdminController {

    public function index(){
    	if (IS_POST) {
    		$param = I('post.');
			tpCache('xjp',$param);
			$this->success("操作成功",U('Huilv/index'));
    	}else{
    		/*配置列表*/
			$config = tpCache('xjp');
			$this->assign('config',$config);//当前配置项
	        C('TOKEN_ON',false);
			$this->display($inc_type);
    	}
		
	}
}