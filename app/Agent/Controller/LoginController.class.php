<?php
namespace Agent\Controller;
use Common\Controller\BaseController;

class LoginController extends BaseController {

    public function index(){
        $this->display();
    }

    public function loginDo(){

        if (!IS_POST) {
            E('页面不存在！');
        }

        if (!checkRequest()) {
            $this->error('未知错误');
        }

        $username = trim(I('post.username'));
        $password = trim(I('post.password'));
        $veriCode = trim(I('post.veriCode'));

        if (empty($username)) {
            $this->error('用户名不能为空！');
        }

        if (empty($password)) {
            $this->error('密码不能为空！');
        }

        /*if (empty($veriCode)) {
            $this->error('验证码不能为空！');
        } elseif (!$this->check_verify($veriCode)) {
            $this->error("验证码输入有误！");
        }*/

        $user = M('Agent');
        $rs = $user->where(array("username" => $username, 'status' => array('eq', 1)))->find();
        if ($rs) {
            if ($rs['password'] === md5($password)) {
                $data = array(
                    'memberID' => $rs['id'],
                    'loginTime' => time(),
                    'loginIP' => get_client_ip(0,1)
                );
                M('LoginLog')->add($data);

                $crypt = new \Think\Crypt;
                $cryptStr = $rs['id'].','.get_client_ip(0,1);
                $flag = $crypt->encrypt($cryptStr,C('DATA_CRYPT_KEY'),0);
                session('flag', $flag);
                $this->success('欢迎回来！', U('Index/index'));
            } else {
                $this->error('密码不正确！');
            }

        } else {
            $this->error('用户名不存在,或被禁用！');
        }
    }   

    public function signout(){
        session('flag', null);
        $this->success('退出成功!', u('login/index'));
    }
}