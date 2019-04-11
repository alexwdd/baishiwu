<?php
namespace Store\Controller;
use Common\Controller\BaseController;

class UserController extends BaseController
{
    public $user;
    public $agent;

    public function _initialize(){

        parent::_initialize();

        $agentid = I('agentid');
        if ($agentid) {
            $map['id'] = $agentid;
            $map['status'] = 1;
            $agent = M('Agent')->field('id,name,cityID,siteLogo,notice')->where($map)->find();
            if ($agent['cityID']==39) {
                $app['css'] = 'xjp';
            }else{
                $app['css'] = '';
            }            
        }else{
            if (I('cityID')==39) {
                $app['css'] = 'xjp';
            }else{
                $app['css'] = '';
            }
        }

        $this->assign('app',$app);
        $this->assign('cityID',$cityID);
        $this->assign('agentid',$agentid);

        $token = I('token');        

        if (!$token) {
            $user = ['id'=>0];
        }else{  
            unset($map);
            $map['token'] = $token;
            $map['disable'] = 0;
            $map['outTime'] = array('gt',time());
        	$user = M('Member')->where($map)->find();    
	        if (!$user) {
	            $user = ['id'=>0];
	        }else{
                $data['outTime'] = time()+7200; 
                M('Member')->where($map)->save($data);
            }
        }
        $this->user = $user;
        if ($this->user['id']==0) {
        	echo "<script>window.location.href='app://login';</script>";
        	die;
        }
        $this->assign('user',$this->user);
        $this->assign('token',$token);
       
        $this->assign('empty','<div class="empty"><img src="/tpl/store/common/image/empty.png" /><p>空空如也~</p></div>');  
    }  
}
