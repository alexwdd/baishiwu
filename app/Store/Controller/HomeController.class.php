<?php
namespace Store\Controller;
use Common\Controller\BaseController;

class HomeController extends BaseController
{
    public $user;
    public $agent;

    public function _initialize(){

        parent::_initialize();

        $httpAgent = $_SERVER['HTTP_USER_AGENT']; 
        if(strpos(strtolower($httpAgent),"micromessenger")) {
            $is_weixin = 1;
        }else{
            $is_weixin = 0;
        }

        $token = I('token');
        $agentID = I('agentid');
        if ($agentID=='' || !is_numeric($agentID)) {die;}
        $map['id'] = $agentID;
        $map['show'] = 1;
        $agent = M('Agent')->where($map)->find();
        if (!$agent) {die;}
        $this->agent = $agent;
        $this->assign('agent',$this->agent);

        switch ($agent['cityID']) {
            case '9'://阿德莱德
                $app['appName'] = '阿德莱德眼';
                $app['aUrl'] = 'https://play.google.com/store/apps/details?id=com.ldw.life';
                $app['iUrl'] = 'https://itunes.apple.com/cn/app/id1386824572?l=zh&ls=1&mt=8';
                $app['css'] = '';
                break;
            case '2'://堪培拉
                $app['appName'] = '堪城圈';
                $app['aUrl'] = 'https://play.google.com/store/apps/details?id=com.ldw.kanchengquan';
                $app['iUrl'] = 'https://itunes.apple.com/cn/app/id1413038662?l=zh&ls=1&mt=8';
                $app['css'] = '';
                break; 
            case '39'://堪培拉
                $app['appName'] = '新加坡圈';
                $app['aUrl'] = 'https://play.google.com/store/apps/details?id=com.ldw.singapore';
                $app['iUrl'] = 'https://itunes.apple.com/cn/app/id1437373175?l=zh&ls=1&mt=8';
                $app['css'] = 'xjp';
                break;
            default:
                $app['aUrl'] = '';
                $app['iUrl'] = '';
                break;
        }
        $this->assign('app',$app);
        $this->assign('is_weixin',$is_weixin);
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
                $data['outTime'] = time()+2592000; 
                M('Member')->where($map)->save($data);
            }
        }
        $this->user = $user;
        $this->assign('user',$this->user);
        $this->assign('token',$token);

        $shareUrl = 'http://' . $_SERVER['HTTP_HOST'] . U('store/index/index', array('agentid' => $agentID));
        $this->assign('shareUrl',$shareUrl);
        $this->assign('empty','<div class="empty"><img src="/tpl/store/common/image/empty.png" /><p>空空如也~</p></div>');  
    }  

    
}
