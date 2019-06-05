<?php
namespace Adminx\Model;
use Think\Model;

class MemberModel extends \Think\Model {
    protected $_auto = array(
        array('password', 'md5', 3, 'function'),
        array('payPassword', 'md5', 3, 'function'),
        array('createIP', '_ip', 3, 'callback'),
        array('createTime', 'time', 1, 'function')
    );

    protected $_validate = array(
        array('name', 'require', '请输入联系人'),
        array('company', 'require', '请输入公司名称'),        
        array('mobile', 'require', '请输入手机号'),
        array('mobile', 'checkmobile', '手机号格式输入有误', 0, 'callback', 1),
        array('mobile', '', '手机号已经存在！', 0, 'unique', 1),
        array('password', 'require', '请输入登录密码'),
        array('cpassword', 'password', '两次登录密码不同', 0, 'confirm'),
        //array('tjName', 'checktjuser', '推荐人不存在', 0, 'callback', 1) //控制器中有验证方法，这里无需浪费服务器资源
    );

    protected function _ip(){
        return get_client_ip();
    }

    protected function checkusername($username){
        $userpreg = '/^[0-9a-zA-Z]{6,12}$/';
        if (!preg_match($userpreg, $username)) {
            return false;
        }else {
            return true;
        }
    }

    protected function checktjuser($tjuser){        
        $obj = M('Member');
        if( $obj->count() > 0 ){
            $rs = $obj->where(array('username' => $tjuser))->find();
            if ($rs) {
                return true;
            }else {
                return false;
            }
        }else{          
            return true;
        }
    }

    protected function checkmobile($mobile){
        $mobilepreg = '/^1[3|4|5|7|8][0-9]{9}$/';
        if (!preg_match($mobilepreg, $mobile)) {
            return false;
        }else {
            return true;
        }
    }

    protected function checkemail($email){
        $emailpreg = '/^(?:\\w+\\.?)*\\w+@(?:\\w+\\.)+\\w+$/';
        if (!preg_match($emailpreg, $email)) {
            return false;
        }else{
            return true;
        }
    }
}

?>
