<?php
namespace Adminx\Controller;

class SettingController extends AdminController {

    public function index(){
		/*配置列表*/
		$group_list = array('basic'=>'基本设置','sms'=>'短信设置','email'=>'邮箱设置','kuaidi'=>'快递设置');		
		$this->assign('group_list',$group_list);
		$inc_type =  I('get.inc_type','basic');
		$this->assign('inc_type',$inc_type);
		$config = tpCache($inc_type);
		$this->assign('config',$config);//当前配置项
        C('TOKEN_ON',false);
		$this->display($inc_type);
	}
	
    #保存配置
    public function insert(){
    	$param = I('post.');
		$inc_type = $param['inc_type'];
		unset($param['inc_type']);
		tpCache($inc_type,$param);
		$this->success("操作成功",U('Setting/index',array('inc_type'=>$inc_type)));
    }
}