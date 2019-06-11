<?php
namespace V1\Controller;

class AccountController extends CommonController {


    public function checkMyArticle(){
        $type = I('post.type');
        $articleid = I('post.articleid');
        $userid = I('post.userid');
 
        if ($articleid=='' || !is_numeric($articleid)) {
            returnJson('-1','缺少articleid');
        }
        $arr = $this->getModel($type);
        if (!$arr) {
            returnJson('-1','type类型错误');
        }
        
        $obj = M($arr['db']);
        $map['userid'] = $userid;
        $map['articleid']=$articleid;
        $list = $obj->where($map)->find();
        if ($list) {
            returnJson(0,'');
        }else{
            returnJson('-1','');
        }
    }

    public function userinfo(){
        if (IS_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $userid = I('post.userid');

            if ($userid=='') {
                returnJson('-1','请输入userid');
            }

            $map['id'] = $userid;
            $list = M('Member')->field('id as userid,cityID,password,openid,phone,email,headimg,name,nickname,wechat')->where($map)->find();
            if ($list) {
                if ($list['disable']==1) {
                    returnJson('-1','账户已禁用，请联系客服');
                }else{
                    returnJson('0',C("SUCCESS_RETURN"),$list);
                }
            }else{
                returnJson('-1','账户或密码错误');
            }
        }
    }

    public function login(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $accout = I('post.accout');
            $password = I('post.password');

            if ($accout=='') {
                returnJson('-1','请输入手机或邮箱');
            }

            if ($password=='') {
                returnJson('-1','请输入密码');
            }

            $map['email|phone'] = $accout;
            $map['password'] = $password;
            $list = M('Member')->field('id as userid,nickname,headimg,wechat,phone,email,birthday,work,sign,gender,open')->where($map)->find();
            if ($list) {
                if ($list['disable']==1) {
                    returnJson('-1','账户已禁用，请联系客服');
                }else{
                    if($list['gender']==0) {
                        $list['gender'] = '保密';
                    }elseif($list['gender']==1){
                        $list['gender'] = '男';
                    }else{
                        $list['gender'] = '女';
                    }                    
                    if ($list['birthday']=='') {
                        $list['birthday'] = '未知';
                        $list['age'] = '未知';
                    }else{
                        $byear=date('Y',strtotime($list['birthday']));
                        $eyear=date('Y',time());
                        $list['age'] = $eyear - $byear;
                        if ($list['age']<=0) {
                            $list['age'] = '未知';
                        }
                    }
                    if ($list['open']==0) {
                        $list['wechat'] = '未知';
                        $list['phone'] = '未知';
                        $list['email'] = '未知';
                        $list['gender'] = '未知';
                        $list['birthday'] = '未知';
                        $list['work'] = '未知';
                        $list['age'] = '未知';
                    }

                    unset($map);
                    $map['memberID'] = $list['userid'];
                    $photo = M('Photo')->field('id as imageID,image')->where($map)->order('sort asc,id desc')->select();
                    foreach ($photo as $key => $value) {
                        $photo[$key]['image'] = getRealUrl($value['image']);
                    }
                    $list['photo'] = $photo;

                    //生成token
                    $str = md5(uniqid(md5(microtime(true)),true)); 
                    $token = sha1($str);
                    $userData = array(
                        'token' => $token,
                        'outTime' => time()+7200
                    );
                    M('Member')->where(array('id'=>$list['userid']))->save($userData);    
                    $list['token'] = $token;
                    returnJson('0',C("SUCCESS_RETURN"),$list);
                }
            }else{
                returnJson('-1','账户或密码错误');
            }
        }
    }

    public function wechat_login(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $openid = I('post.openid');
            $nickname = I('post.nickname');
            $headimg = I('post.headimg');
            $cityID = I('post.cityID');

            if ($cityID=='' || !is_numeric($cityID)) {
                returnJson('-1','缺少cityID');
            }

            if ($openid=='') {
                returnJson('-1','请输入openid');
            }

            $map['openid'] = $openid;
            $list = M('Member')->field('id as userid,nickname,headimg,wechat,phone,email,birthday,work,sign,gender,open')->where($map)->find();
            if ($list) {
                M('Member')->where($map)->setField('headimg',$headimg);
                $list['headimg'] = $headimg;
                if ($list['disable']==1) {
                    returnJson('-1','账户已禁用，请联系客服');
                }else{
                    if($list['gender']==0) {
                        $list['gender'] = '保密';
                    }elseif($list['gender']==1){
                        $list['gender'] = '男';
                    }else{
                        $list['gender'] = '女';
                    }
                    
                    if ($list['birthday']=='') {
                        $list['birthday'] = '未知';
                        $list['age'] = '未知';
                    }else{
                        $byear=date('Y',strtotime($list['birthday']));
                        $eyear=date('Y',time());
                        $list['age'] = $eyear - $byear;
                        if ($list['age']<=0) {
                            $list['age'] = '未知';
                        }
                    }
                    if ($list['open']==0) {
                        $list['wechat'] = '未知';
                        $list['phone'] = '未知';
                        $list['email'] = '未知';
                        $list['gender'] = '未知';
                        $list['birthday'] = '未知';
                        $list['work'] = '未知';
                        $list['age'] = '未知';
                    }

                    unset($map);
                    $map['memberID'] = $list['userid'];
                    $photo = M('Photo')->field('id as imageID,image')->where($map)->order('sort asc,id desc')->select();
                    foreach ($photo as $key => $value) {
                        $photo[$key]['image'] = getRealUrl($value['image']);
                    }
                    $list['photo'] = $photo;

                    $str = md5(uniqid(md5(microtime(true)),true)); 
                    $token = sha1($str);
                    $userData = array(
                        'token' => $token,
                        'outTime' => time()+7200
                    );
                    M('Member')->where(array('id'=>$list['userid']))->save($userData);
                    $list['token'] = $token;
                    returnJson('0',C("SUCCESS_RETURN"),$list);
                }
            }else{              
                $data['oauth'] = 2;
                $data['cityID'] = $cityID;
                $data['openid'] = $openid;
                $data['nickname'] = $nickname;
                $data['headimg'] = $headimg;
                $data['createTime'] = time();
                $data['createIP'] = get_client_ip(0,1);
                $data['open'] = 1;

                $str = md5(uniqid(md5(microtime(true)),true)); 
                $token = sha1($str);
                $data['token'] = $token;
                $data['outTime'] = time()+7200;

                if ($id = M('Member')->add($data)) {    
                    $this->autoFocus($id,$cityID);                
                    $user = M('Member')->field('id as userid,phone,email,headimg,name,nickname,wechat')->where(array('id'=>$id))->find();
                    $user['token'] = $token;
                    returnJson('0',C('SUCCESS_RETURN'),$user);
                }else{
                    returnJson('-1','微信授权注册失败');
                }
            }
        }
    }

    public function getCode(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $type = I('post.type');
            $email = I('post.email');
            $phone = I('post.phone');

            if (!in_array($type, array('0','1','2','3'))) {//0 邮箱注册；1 手机注册；2 忘记密码；3 手机认证
                returnJson('-1','注册方式错误');
            }

            switch ($type) {
                case 0:
                    $this->emailCode($email);
                    break;
                case 1:
                    $this->phoneCode($phone);
                    break;
                case 2:
                    $this->forgetCode($email);
                    break;
                case 3:
                    $this->phoneCode($phone);
                    break;
                default:
                    returnJson('-1','注册方式错误');
                    break;
            }
        }
    }

    public function forgetCode($email){ 
        $config = tpCache('email');

        if (!check_email($email)) {
            returnJson('-1','邮箱格式错误');
        }

        if (!M('Member')->where(array('email'=>$email))->find()) {
            returnJson('-1','邮箱不存在');
        }

        $info = M('MemberCode')->where(array('account'=>$email))->find();
        $count = $this->getDayNumber($mobile);

        if ($count>=$config['dayNumber']) {
            returnJson('-1','每天最多发送'.$config['dayNumber'].'条');
        }

        if ($info) {
            if (time()-$info['createTime']<=$config['diffTime']*60) {
                returnJson('-1','请在'.$config['diffTime'].'分钟后再试');
            }
        }

        $verify = rand(1000, 9999);//获取随机验证码
        $title = 'Retrieve the password From '.C('site.name');
        $content = '<pre>Dear member:';
        $content .= '<p>Your personal information is:</p>';
        $content .= '<table border="1">';
        $content .= '<tr><td>Email</td><td>'.$email.'</td></tr>';
        $content .= '<tr><td>Verification Code</td><td>'.$verify.'</td></tr>';
        $content .= '</table>';
        $content .= '<p>This is an automatically generated email, please do not reply.</p>';
        $res = think_send_mail($email, $name, $title, $content);
        if ($res) {
            $data = array(
                'account'=>$email,
                'regcode'=>$verify,
                'status'=>0,
                'createTime'=>time(),
                );
            $list = M('MemberCode')->add($data);
            if ($list) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','邮件验证码发送失败');
            }           
        }else{
            returnJson('-1','邮件验证码发送失败');
        }
    }

    //type 0注册 1找回密码
    public function emailCode($email){  
        $config = tpCache('email');
        if ($config['isEmail']==0) {
            returnJson('-1','邮箱注册关闭');
        }

        if (!check_email($email)) {
            returnJson('-1','邮箱格式错误');
        }

        if (M('Member')->where(array('email'=>$email))->find()) {
            returnJson('-1','邮箱重复');    
        }

        $info = M('MemberCode')->where(array('account'=>$email))->find();
        $count = $this->getDayNumber($email);

        if ($count>=$config['dayNumber']) {
            returnJson('-1','每天最多发送'.$config['dayNumber'].'条');
        }

        if ($info) {
            if (time()-$info['createTime']<=$config['diffTime']*60) {
                returnJson('-1','请在'.$config['diffTime'].'分钟后再试');
            }
        }

        $verify = rand(1000, 9999);//获取随机验证码
        $title = 'Registration Email Notification From '.C('site.name');
        $content = '<pre>Dear member:';
        $content .= '<p>Thank you for your registration.</p>';
        $content .= '<p>Your personal information is:</p>';
        $content .= '<table border="1">';
        $content .= '<tr><td>Email</td><td>'.$email.'</td></tr>';
        $content .= '<tr><td>Verification Code</td><td>'.$verify.'</td></tr>';
        $content .= '</table>';
        $content .= '<p>This is an automatically generated email, please do not reply.</p>';
        $res = think_send_mail($email, $name, $title, $content);
        if ($res) {
            $data = array(
                'account'=>$email,
                'regcode'=>$verify,
                'status'=>0,
                'createTime'=>time(),
                );
            $list = M('MemberCode')->add($data);
            if ($list) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','邮件验证码发送失败');
            }           
        }else{
            returnJson('-1','邮件验证码发送失败');
        }
    }

    public function getDayNumber($account){        
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $map['account'] = $account;
        $count = M('MemberCode')->where($map)->count();
        return $count;
    }

    public function phoneCode($phone){
        $config = tpCache('sms');
        if ($config['isSms']==0) {
            returnJson('-1','手机注册关闭');
        }

        /*if (!check_email($email)) {
            returnJson('-1','邮箱格式错误');
        }*/

        if (M('Member')->where(array('phone'=>$phone))->find()) {
            returnJson('-1','手机重复');    
        }

        $info = M('MemberCode')->where(array('account'=>$phone))->find();
        $count = $this->getDayNumber($phone);

        if ($count>=$config['dayNumber']) {
            returnJson('-1','每天最多发送'.$config['dayNumber'].'条');
        }

        if ($info) {
            if (time()-$info['createTime']<=$config['diffTime']*60) {
                returnJson('-1','请在'.$config['diffTime'].'分钟后再试');
            }
        }

        $verify = rand(1000, 9999);//获取随机验证码
        //$content = '【澳洲生活圈】您的验证码为'.$verify.'，在'.$config['out_time'].'分钟内有效。';
        if (strlen($phone)==10) {
            $content = '【AULife】Your verification code is '.$verify;
        }elseif(strlen($phone)==8){
            $content = '【SGLife】Your verification code is '.$verify;
        }
        $res = send_sms($phone,$content);
        if ($res==0) {
            $data = array(
                'account'=>$phone,
                'regcode'=>$verify,
                'status'=>0,
                'createTime'=>time(),
                );
            $list = M('MemberCode')->add($data);
            if ($list) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','手机验证码发送失败');
            }
        }else{
            returnJson('-1','手机验证码发送失败，错误码'.$res);
        }
    }

    public function register(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $data['oauth'] = I('post.type');
            $data['email'] = I('post.email');
            $data['phone'] = I('post.phone');
            $data['code'] = I('post.code');         
            $data['password'] = I('post.password');
            $data['cityID']= I('post.cityID');

            if ($data['cityID']=='' || !is_numeric($data['cityID'])) {
                returnJson('-1','缺少cityID');
            }
            
            if (!in_array($data['oauth'], array('0','1'))) {//0邮箱 1手机
                returnJson('-1','注册方式错误');
            }
            if ($data['oauth']==0) {
                $this->email($data);
            }else{
                $this->phone($data);
            }
        }
    }

    //邮箱注册
    public function email($data){
        $config = tpCache('email');

        if ($config['isEmail']==0) {
            returnJson('-1','邮箱注册关闭');
        }

        if (!check_email($data['email'])) {
            returnJson('-1','邮箱格式错误');
        }

        if (M('Member')->where(array('email'=>$data['email']))->find()) {
            returnJson('-1','邮箱重复');
        }

        if ($data['code']=='') {
            returnJson('-1','请输入验证码');
        }

        if ($data['password']=='') {
            returnJson('-1','请输入密码');
        }

        $code = M('MemberCode');

        $rsyz = $code->where(array(
            'account' => array('eq', $data['email']),
            'regcode' => array('eq', $data['code']),
            'status' => array('eq', 0)
        ))->order('id desc')->find();

        if (!$rsyz) {
            returnJson('-1','邮箱验证码错误');
        }

        if (time()-$rsyz['createTime'] > $config['out_time']*60) {          
            returnJson('-1','邮箱验证码超时，请在'.$config['out_time'].'分钟内容输入');
        }

        $res = $code->where(array('id' => array('eq', $rsyz['id'])))->setField(array('status' => 1));
        if ($res) {
            $data['createTime'] = time();
            $data['createIP'] = get_client_ip(0,1);
            $data['open'] = 1;

            $str = md5(uniqid(md5(microtime(true)),true)); 
            $token = sha1($str);
            $data['token'] = $token;
            $data['outTime'] = time()+7200;

            if ($id = M('Member')->add($data)) {
                $this->autoFocus($id,$data['cityID']);
                $nickname = '用户'.$id;
                M('Member')->where(array('id'=>$id))->setField('nickname',$nickname);
                $user = M('Member')->field('id as userid,nickname,headimg,wechat,phone,email,birthday,work,sign,gender,open')->where(array('id'=>$id))->find();
                if($user['gender']==0) {
                    $userid['gender'] = '保密';
                }elseif($user['gender']==1){
                    $user['gender'] = '男';
                }else{
                    $user['gender'] = '女';
                }
                
                if ($user['birthday']=='') {
                    $user['birthday'] = '未知';
                    $user['age'] = '未知';
                }else{
                    $byear=date('Y',strtotime($list['birthday']));
                    $eyear=date('Y',time());
                    $user['age'] = $eyear - $byear;
                    if ($user['age']<=0) {
                        $list['age'] = '未知';
                    }
                }
                if ($user['open']==0) {
                    $user['wechat'] = '未知';
                    $user['phone'] = '未知';
                    $user['email'] = '未知';
                    $user['gender'] = '未知';
                    $user['birthday'] = '未知';
                    $user['work'] = '未知';
                    $user['age'] = '未知';
                }

                $user['photo'] = [];
                $user['token'] = $token;
                returnJson('0',C('SUCCESS_RETURN'),$user);
            }else{
                returnJson('-1','会员注册失败');
            }
        }else{
            returnJson('-1','会员注册失败');
        }       
    }

    //手机注册
    public function phone($data){
        $config = tpCache('sms');
        if ($config['isSms']==0) {
            returnJson('-1','手机注册关闭');
        }

        /*if (!check_mobile($data['phone'])) {
            returnJson('-1','手机格式错误');
        }*/

        if ($data['code']=='') {
            returnJson('-1','请输入验证码');
        }

        if ($data['password']=='') {
            returnJson('-1','请输入密码');
        }

        if (M('Member')->where(array('phone'=>$data['phone']))->find()) {
            returnJson('-1','手机重复');
        }

        $smscode = M('MemberCode');

        $rsyz = $smscode->where(array(
            'account' => array('eq', $data['phone']),
            'regcode' => array('eq', $data['code']),
            'status' => array('eq', 0)
        ))->order('id desc')->find();

        if (!$rsyz) {
            returnJson('-1','手机验证码错误');
        }

        if (time()-$rsyz['createTime'] > $config['out_time']*60) {
            returnJson('-1','短信验证码超时，请在'.$config['out_time'].'分钟内容输入');
        }

        $res = $smscode->where(array('id' => array('eq', $rsyz['id'])))->setField(array('status' => 1));
        if ($res) {
            $data['createTime'] = time();
            $data['createIP'] = get_client_ip(0,1);
            $data['open'] = 1;

            $str = md5(uniqid(md5(microtime(true)),true)); 
            $token = sha1($str);
            $data['token'] = $token;
            $data['outTime'] = time()+7200;

            if ($id = M('Member')->add($data)) {
                $this->autoFocus($id,$data['cityID']);
                $nickname = '用户'.$id;
                M('Member')->where(array('id'=>$id))->setField('nickname',$nickname);
                $user = M('Member')->field('id as userid,nickname,headimg,wechat,phone,email,birthday,work,sign,gender,open')->where(array('id'=>$id))->find();
                if($user['gender']==0) {
                    $userid['gender'] = '保密';
                }elseif($user['gender']==1){
                    $user['gender'] = '男';
                }else{
                    $user['gender'] = '女';
                }
                
                if ($user['birthday']=='') {
                    $user['birthday'] = '未知';
                    $user['age'] = '未知';
                }else{
                    $byear=date('Y',strtotime($list['birthday']));
                    $eyear=date('Y',time());
                    $user['age'] = $eyear - $byear;
                    if ($user['age']<=0) {
                        $list['age'] = '未知';
                    }
                }
                if ($user['open']==0) {
                    $user['wechat'] = '未知';
                    $user['phone'] = '未知';
                    $user['email'] = '未知';
                    $user['gender'] = '未知';
                    $user['birthday'] = '未知';
                    $user['work'] = '未知';
                    $user['age'] = '未知';
                }
                $user['photo'] = [];
                $user['token'] = $token;
                returnJson('0',C('SUCCESS_RETURN'),$user);
            }else{
                returnJson('-1','会员注册失败');
            }
        }else{
            returnJson('-1','会员注册失败');
        }
    }

    //自动关注
    public function autoFocus($userid,$cityID){
        switch ($cityID) {
            case 9:
                $ids = [14,313,357];
                break;            
            default:
                break;
        }
        if ($ids) {
            $data = [];
            foreach ($ids as $key => $value) {
                array_push($data,['userID'=>$value,'memberID'=>$userid,'base'=>1]);
            }
            $result = M('ChatFocus')->addAll($data);
        }        
    }

    public function verify_phone(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            $config = tpCache('sms');

            $userid = I('post.account');
            $code = I('post.code');
            $phone = I('post.phone');
            
            if ($userid=='' || !is_numeric($userid)) {
                returnJson('-1','缺少account');
            }

            if ($code=='') {
                returnJson('-1','缺少code');
            }
            if ($phone=='') {
                returnJson('-1','缺少phone');
            }

            $smscode = M('MemberCode');

            $rsyz = $smscode->where(array(
                'account' => array('eq', $phone),
                'regcode' => array('eq', $code),
                'status' => array('eq', 0)
            ))->order('id desc')->find();

            if (!$rsyz) {
                returnJson('-1','手机验证码错误');
            }

            if (time()-$rsyz['createTime'] > $config['out_time']*60) {
                returnJson('-1','短信验证码超时，请在'.$config['out_time'].'分钟内容输入');
            }
            
            $res = $smscode->where(array('id' => array('eq', $rsyz['id'])))->setField(array('status' => 1));
            $data['phone'] = $phone;
            $data['authentication'] = 1;
            $list = M('Member')->where(array('id'=>$userid))->save($data);
            if ($list) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    public function edit_password(){
        $config = tpCache('email');     
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $email = I('post.email');
            $code = I('post.code');
            $oldpassword = I('post.oldpassword');
            $newpassword = I('post.newpassword');
            
            if ($email=='') {
                returnJson('-1','缺少email');
            }
            if (!check_email($email)) {
                returnJson('-1','email格式错误');
            }
            if ($newpassword=='') {
                returnJson('-1','缺少新密码');
            }

            if ($code=='') {
                if ($oldpassword=='') {
                    returnJson('-1','缺少旧密码');
                }

                $map['email'] = $email;
                $map['password'] = $oldpassword;
                $r = M('Member')->where($map)->find();
                if (!$r) {
                    returnJson('-1','旧密码错误');
                }
            }

            if ($oldpassword=='') {
                if ($code=='') {
                    returnJson('-1','请输入邮箱验证码');
                }

                $smscode = M('MemberCode');

                $rsyz = $smscode->where(array(
                    'account' => array('eq', $email),
                    'regcode' => array('eq', $code),
                    'status' => array('eq', 0)
                ))->order('id desc')->find();

                if (!$rsyz) {
                    returnJson('-1','邮箱验证码错误');
                }

                if (time()-$rsyz['createTime'] > $config['out_time']*60) {
                    returnJson('-1','邮箱验证码超时，请在'.$config['out_time'].'分钟内容输入');
                }

                $res = $smscode->where(array('id' => array('eq', $rsyz['id'])))->setField(array('status' => 1));
            }
            
            $data['password'] = $newpassword;
            $list = M('Member')->where(array('email'=>$email))->save($data);
            if ($list) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    //临时数据保存
    public function temp_upload(){
        //if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            $isThumb = I('get.isThumb');
            $path = '.'.C('UPLOAD_PATH');

            if(!is_dir($path)){
                mkdir($path);
            }
            
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = C('image_size')*1024*1024;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
            $upload->rootPath = $path;
            $upload->autoSub = true;
            $upload->replace=true;     //如果存在同名文件是否进行覆盖
            $upload->exts= explode(',',C('image_exts'));     //准许上传的文件后缀
            $info = $upload->upload();  
            if($info){              
                $data['image'] = array();               
                foreach($info as $file){
                    if ($file['ext']==strtolower('mp4')) {
                        $data['video'] = C('UPLOAD_PATH').$file['savepath'].$file['savename'];
                    }else{
                        array_push($data['image'], C('UPLOAD_PATH').$file['savepath'].$file['savename']);
                    }                   
                }                               
            }

            $data['content'] = I('post.content');
            $data['createTime'] = time();           
            if (count($data['image'])>0) {
                $thumb = $data['image'][0];
                $data['image'] = implode(";", $data['image']);
            }else{
                $data['image']='';
            }

            if ($data['image']=='' && $data['video']=='' && $data['content']=='') {
                returnJson('-1','没有提交信息');
            }

            $r = M('Temp')->add($data);
            if ($r) {
                $arr = explode("；", $data['content']);
                $pubData = array();
                foreach ($arr as $key => $value) {
                    $first = substr($value,0,1);
                    if (strtolower($first)=='t') {
                        $pubData['title'] = substr($value,1);
                    }
                    if (strtolower($first)=='a') {
                        $pubData['address'] = substr($value,1);
                    }
                    if (strtolower($first)=='p') {
                        $pubData['price'] = substr($value,1);
                    }
                    if (strtolower($first)=='d') {
                        $pubData['detail'] = substr($value,1);
                    }
                    if (strtolower($first)=='s') {
                        $pubData['type'] = substr($value,1);
                    }
                    if (strtolower($first)=='c') {
                        $pubData['cityID'] = substr($value,1);
                    }
                }
                if ($thumb!='') {
                    $pubData['thumb_s'] = $this->getThumb($thumb, 240, 180);
                    $pubData['thumb_b'] = $this->getThumb($thumb, 600, 450);
                }
                $pubData['image'] = $data['image'];
                $pubData['status'] = 1;
                $pubData['userid'] = 0;
                $pubData['createTime'] = time();
                $pubData['updateTime'] = time();
                $pubData['showTime'] = time();
                if ($pubData['type']=='zf') {
                    $pubData['houseType'] = 0;
                }
                if ($pubData['type']=='zp') {
                    $pubData['jobtype'] = 0;
                }
                $dbArr = $this->getModel($pubData['type']);
                if ($dbArr) {
                    $obj = M($dbArr['db']);
                    $obj->add($pubData);
                }
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','数据保存失败');
            }
        //}
    }

    public function getModel($type){
        foreach (C('infoArr') as $key => $value) {
            if ($value['py'] == $type) {
                return $value;
            }
        }
        return false;
    }

    //上传图片
    public function image_upload(){
        //if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }
            $isThumb = I('get.isThumb');
            $path = '.'.C('UPLOAD_PATH');

            if(!is_dir($path)){
                mkdir($path);
            }
            
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = C('image_size')*1024*1024;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
            $upload->rootPath = $path;
            $upload->autoSub = true;
            $upload->replace=true;     //如果存在同名文件是否进行覆盖
            $upload->exts= explode(',',C('image_exts'));     //准许上传的文件后缀
            $info = $upload->upload();  
            if($info){
                foreach($info as $file){        
                    $filepath = C('UPLOAD_PATH').$file['savepath'];
                    $url = C('UPLOAD_PATH').$file['savepath'].$file['savename'];            
                }
                if ($isThumb=='YES') {
                    $thumb_s = $this->getThumb($url, 240, 180);
                    $thumb_b = $this->getThumb($url, 600, 450);
                }else{
                    $thumb_s = $url;
                    $thumb_b = $url;
                }
                $array = array(
                    /*'thumb_s'=>'http://'.$_SERVER['HTTP_HOST'].$thumb_s,
                    'thumb_b'=>'http://'.$_SERVER['HTTP_HOST'].$thumb_b,
                    'url'=>'http://'.$_SERVER['HTTP_HOST'].$url*/
                    'thumb_s'=>$thumb_s,
                    'thumb_b'=>$thumb_b,
                    'url'=>$url
                );
                
                returnJson('0',C('SUCCESS_RETURN'),$array);     
            }else{
                //是专门来获取上传的错误信息的    
                returnJson('-1',$upload->getError());
            }
        //}
    }

    //图片生成缩略图
    public function getThumb($path, $width, $height) {
        if(file_exists(".".$path) && !empty($path)){
            $fileArr = pathinfo($path); 
            $dirname = $fileArr['dirname']; 
            $filename = $fileArr['filename']; 
            $extension = $fileArr['extension']; 

          if ($width > 0 && $height > 0) { 
              $image_thumb =$dirname . "/" . $filename . "_" . $width . "_" .$height. "." . $extension; 
              if (!file_exists(".".$image_thumb)) { 
                  $image = new \Think\Image(); 
                  $image->open(".".$path); 
                  $image->thumb($width, $height,3)->save(".".$image_thumb);
              } 
              $image_rs = $image_thumb; 
          } else { 
              $image_rs = $path; 
          } 
          return $image_rs;
        }else{
          return false;
        } 
    }

    //修改资料
    public function edit(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $userid = I('post.userid');
            $openid = I('post.openid');
            $password = I('post.password');
            $data['nickname'] = I('post.nickname');
            $data['headimg'] = I('post.headimg');
            $data['name'] = I('post.name');
            $data['wechat'] = I('post.wechat');
            $data['email'] = I('post.email');
            $data['birthday'] = I('post.birthday');
            $data['gender'] = I('post.gender');
            $data['work'] = I('post.work');
            $data['sign'] = I('post.sign');
            if(I('post.open')!=''){
                $data['open'] = I('post.open');
            }           

            $user = $this->userCheck($userid,$password,$openid);
        
            if ($user['oauth']!=0 && $user['email']!=$data['email'] && $data['email']) {//不是邮箱注册的用户都可以设置邮箱
                if (!check_email($data['email'])) {
                    returnJson('-1','邮箱格式错误');
                }
                if (M('Member')->where(array('email'=>$data['email']))->find()) {
                    //if ($type==0) {//新注册
                        returnJson('-1','邮箱重复');
                    //}           
                }
            }else{
                unset($data['email']);
            }            
            if(empty($data['nickname']) || $data['nickname']==''){unset($data['nickname']);};
            if(empty($data['headimg']) || $data['headimg']==''){unset($data['headimg']);};
            if(empty($data['name']) || $data['name']==''){unset($data['name']);};
            if(empty($data['wechat']) || $data['wechat']==''){unset($data['wechat']);};
            if(empty($data['email']) || $data['email']==''){unset($data['email']);};
            if(empty($data['birthday']) || $data['birthday']==''){unset($data['birthday']);};
            if($data['gender']==''){unset($data['gender']);};
            if(empty($data['work']) || $data['work']==''){unset($data['work']);};
            if(empty($data['sign']) || $data['sign']==''){unset($data['sign']);};
    
            $map['id'] = $userid;
            $res = M('Member')->where($map)->save($data);
            if ($res) {
                $user = M('Member')->field('id as userid,phone,email,headimg,name,nickname,wechat')->where($map)->find();
                returnJson('0',C('SUCCESS_RETURN'),$user);
            }else{
                returnJson('-1','您没有做任何改动');
            }
            
        }
    }

    //获取用户系统消息接口
    public function get_systemMessage(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $memberID = I('post.account');
            $page = I('post.page',1);

            if ($memberID=='' || !is_numeric($memberID)) {
                returnJson('-1','缺少account');
            }

            $pagesize = 10;
            $firstRow = $pagesize*($page-1); 

            $map['memberID'] = $memberID;
            $obj = M('Message');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $num = $obj->where(array('memberID'=>$memberID,'read'=>0))->count();

            $list = $obj->field('id,title,content,read,createTime as time')->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();

            returnJson('0',C("SUCCESS_RETURN"),array('message'=>$list,'num'=>$num,'next'=>$next));
        }
    }

    //设置系统消息状态
    public function set_systemMessage(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $memberID = I('post.account');
            $messageid = I('post.messageid');

            if ($memberID=='' || !is_numeric($memberID)) {
                returnJson('-1','缺少account');
            }

            if ($messageid=='' || !is_numeric($messageid)) {
                returnJson('-1','缺少messageid');
            }           

            $map['id'] = $messageid;
            $map['memberID'] = $memberID;
            $obj = M('Message');
            $r = $obj->where($map)->setField('read',1);
            if ($r) {
                returnJson('0',C("SUCCESS_RETURN"));
            }else{
                returnJson('-1','操作失败');
            }           
        }
    }

    public function setOpen(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }
            $open = $user['open']==1?0:1;
            M('Member')->where(array('id'=>$user['id']))->setField('open',$open);
            returnJson(0,C("SUCCESS_RETURN"),['status'=>$open]);                           
        }
    }

    public function myFocusUser(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');
            $page = I('post.page',1);

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $map['memberID'] = $user['id'];
            $page = I('post.page/d',1); 
            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('ChatFocus');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }
            $list = $obj->field('userID')->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
            foreach ($list as $key => $value) {
                $member = M('Member')->where(array('id'=>$value['userID']))->find();
                $list[$key]['headimg'] = $member['headimg'];
                $list[$key]['nickname'] = $member['nickname'];
                $list[$key]['headimg'] = $member['headimg'];
            }
            returnJson(0,'success',['next'=>$next,'data'=>$list]);                         
        }
    }

    public function photo(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            $map['memberID'] = $user['id'];
            $obj = M('Photo');
            $list = $obj->field('id as imageID,image,sort')->where($map)->order('sort asc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['image'] = getRealUrl($value['image']);
            }
            returnJson(0,'success',$list);                         
        }
    }

    public function photoAdd(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');
            $image = I('post.image');
            $sort = I('post.sort');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            if ($image=='') {
                returnJson("-1",'请上传图片');
            }

            if ($sort=='' || !is_numeric($sort)) {
                $sort=0;
            }

            $data = [
                'memberID'=>$user['id'],
                'image'=>$image,
                'sort'=>$sort
            ];

            $res = M('Photo')->add($data);
            if ($res) {
                unset($map);
                $map['memberID'] = $user['id'];
                $photo = M('Photo')->field('id as imageID,image')->where($map)->order('sort asc,id desc')->select();
                foreach ($photo as $key => $value) {
                    $photo[$key]['image'] = getRealUrl($value['image']);
                } 
                returnJson(0,'success',$photo);
            }else{
                returnJson("-1",'操作失败');
            }                       
        }
    }

    public function photoDel(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');
            $imageID = I('post.imageID');

            if (!$user = $this->checkToken($token)) {
                returnJson('999'); 
            }

            if ($imageID=='' || !is_numeric($imageID)) {
                returnJson("-1",'缺少参数imageID');
            }

            $map['memberID'] = $user['id'];
            $map['id'] = $imageID;
            $obj = M('Photo');
            $res = $obj->where($map)->delete();
            if ($res) {

                unset($map);
                $map['memberID'] = $user['id'];
                $photo = M('Photo')->field('id as imageID,image')->where($map)->order('sort asc,id desc')->select();
                foreach ($photo as $key => $value) {
                    $photo[$key]['image'] = getRealUrl($value['image']);
                } 
                returnJson(0,'success',$photo);
            }else{
                returnJson("-1",'操作失败');
            }                                     
        }
    }

    public function userDetail(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');
            $userid = I('post.userid');

            $user = $this->checkToken($token);            
            if ($userid=='' || !is_numeric($userid)) {
                returnJson("-1",'缺少参数userid');
            }

            $map['id'] = $userid;
            $obj = M('Member');
            $list = $obj->field('id as userid,nickname,headimg,wechat,phone,email,birthday,work,sign,gender,open')->where($map)->find();
            if ($list) {
                $list['headimg'] = getRealUrl($list['headimg']);
                
                if($list['gender']==0) {
                    $list['gender'] = '保密';
                }elseif($list['gender']==1){
                    $list['gender'] = '男';
                }else{
                    $list['gender'] = '女';
                }
                
                if ($list['birthday']=='') {
                    $list['birthday'] = '未知';
                    $list['age'] = '未知';
                }else{
                    $byear=date('Y',strtotime($list['birthday']));
                    $eyear=date('Y',time());
                    $list['age'] = $eyear - $byear;
                    if ($list['age']<=0) {
                        $list['age'] = '未知';
                    }
                }
                if ($userid!=$user['id'] && $list['open']==0) {
                    $list['wechat'] = '未知';
                    $list['phone'] = '未知';
                    $list['email'] = '未知';
                    $list['gender'] = '未知';
                    $list['birthday'] = '未知';
                    $list['work'] = '未知';
                    $list['age'] = '未知';
                }
                unset($map);
                $map['memberID'] = $userid;
                $photo = M('Photo')->field('id as imageID,image')->where($map)->order('sort asc,id desc')->select();
                foreach ($photo as $key => $value) {
                    $photo[$key]['image'] = getRealUrl($value['image']);
                }
                $list['photo'] = $photo;

                unset($map);
                $map['memberID'] = $userid;
                $ids = M('MemberVisitor')->where($map)->order("updateTime desc")->limit(10)->getField("visitorID",true);   
                $visitor = [];
                if ($ids) {
                    foreach ($ids as $key => $value) {
                        $vis = M('Member')->where(array('id'=>$value))->field('id as userid,nickname,headimg')->find();
                        if ($vis) {
                            $vis['headimg'] = getRealUrl($vis['headimg']);
                            array_push($visitor,$vis);
                        }
                    }
                }
                $list['visitor'] = $visitor;
                $list['focus'] = 0;

                //更新访客
                if ($user) {
                    if ($user['id']!=$userid) {
                        unset($map);
                        $map['memberID'] = $userid;
                        $map['visitorID'] = $user['id'];
                        $res = M("MemberVisitor")->where($map)->find();   
                        if ($res) {
                            M("MemberVisitor")->where($map)->setField('updateTime',time());
                        }else{    
                            $data['memberID'] = $userid;
                            $data['visitorID'] = $user['id'];
                            $data['createTime'] = time();
                            $data['updateTime'] = time();
                            M("MemberVisitor")->add($data);
                        }

                        unset($map);
                        $map['userID'] = $userid;
                        $map['memberID'] = $user['id'];
                        $list['focus'] = M('ChatFocus')->where($map)->count();
                    }
                }

                if ($userid==$user['id']){
                    $list['edit'] = true;
                }else{
                    $list['edit'] = false;
                }
                returnJson(0,'success',$list);
            }else{
                returnJson("-1",'用户不存在');
            }                                     
        }
    }

    public function album(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}    
            $token = I('post.token');
            $userid = I('post.userid');

            $user = $this->checkToken($token);

            if ($userid=='' || !is_numeric($userid)) {
                returnJson("-1",'缺少参数userid');
            }

            $map['memberID'] = $userid;
            $map['images'] = array('neq','');
            $obj = M('Chat');
            $list = $obj->field('id,content,images')->where($map)->order('id desc')->limit(10)->select();
            foreach ($list as $key => $value) {
                $list[$key]['content'] = $this->cutstr_html($value['content'],50);
                /*if ($value['thumb']!='') {
                    $thumb = explode("|",$value['thumb']);
                    foreach ($thumb as $k => $val) {
                        $thumb[$k] = getRealUrl($val);
                    }
                    $list[$key]['thumb'] = $thumb;
                }*/
                
                if ($value['images']!='') {
                    $images = explode("|",$value['images']);
                    foreach ($images as $k => $val) {
                        $val = str_replace('http://'.$_SERVER['HTTP_HOST'],'',$val); 
                        $imgInfo = getimagesize('.'.$val);           
                        $img['width'] = $imgInfo[0];
                        $img['height'] = $imgInfo[1];
                        $img['url'] = getRealUrl($val);
                        $images[$k] = $img;
                    }
                    $list[$key]['images'] = $images;
                }
            }

            $focus = 0;
            //是否关注，更新访客
            if ($user) {
                if ($user['id']!=$userid) {
                    unset($map);
                    $map['memberID'] = $userid;
                    $map['visitorID'] = $user['id'];
                    $res = M("MemberVisitor")->where($map)->find();   
                    if ($res) {
                        M("MemberVisitor")->where($map)->setField('updateTime',time());
                    }else{    
                        $data['memberID'] = $userid;
                        $data['visitorID'] = $user['id'];
                        $data['createTime'] = time();
                        $data['updateTime'] = time();
                        M("MemberVisitor")->add($data);
                    }

                    unset($map);
                    $map['userID'] = $userid;
                    $map['memberID'] = $user['id'];
                    $focus = M('ChatFocus')->where($map)->count();
                }
            }
            returnJson(0,'success',['chat'=>$list,'focus'=>$focus]);                                              
        }
    }

    public function cutstr_html($string, $sublen){
        $string = strip_tags($string);
        $string = preg_replace ('/\n/is', '', $string);
        $string = preg_replace ('/ |　/is', '', $string);
        $string = preg_replace ('/&nbsp;/is', '', $string);   
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);   
        if(count($t_string[0]) - 0 > $sublen) $string = join('', array_slice($t_string[0], 0, $sublen))."…";   
        else $string = join('', array_slice($t_string[0], 0, $sublen));   
        return $string;
    }
}