<?php
namespace V2\Controller;
use Common\Controller\BaseController;

class CommonController extends BaseController {

	public $d;

	public function _initialize() {
		parent::_initialize();		

		//数据缓存
        if (S('C_DATA')) {
        	$this->d = S('C_DATA');
        }else{
            $arr = array();
            $data = M('OptionCate')->select();
            foreach ($data as $key => $value) {
                $arr[$value['value']] = M("OptionItem")->field('id,name,value')->where(array('cate'=>$value['id']))->order('sort asc,id asc')->select();
            }
            S('C_DATA',$arr);
            $this->d = S('C_DATA');
        }   
	}  

    public function userCheck($userid,$password,$openid){
        if ($userid=='') {
            returnJson('-1','缺少userid');
        }

        $map['id'] = $userid;           
        if ($openid!='') {
            $map['openid'] = $openid;
        }else{
            if ($password=='') {
                returnJson('-1','缺少password');
            }
            $map['password'] = $password;
        }
        $user = M('Member')->where($map)->find();
        if ($user) {
            if ($user['disable']==1) {
                returnJson('-1','账户已禁用，请联系客服');
            }
            return $user;
        }else{
            returnJson('-1','账户不存在');
        }
    }    
}