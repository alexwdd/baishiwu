<?php 
namespace Adminx\Controller;
use Common\Controller\BaseController;

class LoginController extends BaseController {
    public function index(){     
        $this->display();
    }

    public function checkLogin(){
        $username = I('post.username');
        $password = I('post.password');
        $checkcode = I('post.checkcode');
        $safecode = I('post.safecode');



        if ($username=='' || empty($username)) {
            $this->error('请输入用户名');
        }
        elseif ($password=='' || empty($password)) {
            $this->error('请输入密码');
        }
        elseif ($checkcode=='' || empty($checkcode)) {
            $this->error('请输入验证码');
        }
        elseif ($safecode=='' || empty($safecode)) {
            $this->error('请输入安全码');
        }

        if ($safecode!=C('site.safecode').date("md")) {
            $this->error('安全码不正确');
        }

        if (!$this->check_verify($checkcode)) {
            $this->error('验证码不正确');
        }    

        //生成认证条件
        $map = array();
        // 支持使用绑定帐号登录
        $map['username'] = $username;
        $authInfo = \Org\Util\Rbac::authenticate($map);
        //使用用户名、密码和状态的方式进行认证
        if (!$authInfo) {
            $this->error('帐号不存在或已禁用');
        } else {
            if ($authInfo['password'] != md5($_POST['password'])) {
                $this->error('密码错误');
            }
            $_SESSION[C('USER_AUTH_KEY')] = $authInfo['id'];
            if ($authInfo['username'] == C('site.admin')) {                
                $_SESSION['administrator'] = true;
            }
            //保存登录信息
            $log = M('UserLog');
            $date['uid'] = $authInfo['id'];
            $date['loginTime'] = time();
            $date['loginIP'] = get_client_ip();
            $list = $log->add($date);

            $_SESSION['adminID'] = $authInfo['id'];
            $_SESSION['adminName'] = $authInfo['username'];


            // 缓存访问权限
            \Org\Util\Rbac::saveAccessList();
            $url = U('Index/index');
            $this->success('登录成功',$url);
        }   
    }

    function signout(){
        session_destroy();
        $this->success('成功退出',U('Login/index'));        
    }
}
?>