<?php
namespace Store\Controller;

class MemberController extends UserController
{
	//用户信息
	public function index()
	{
        /*$sql = "(front='' or back='' or sn='') and del=0 and memberID=".$this->user['id'];
        $noauth = M("Order")->where($sql)->count();*/

        $map['payStatus'] = 0;
        $map['del'] = 0;
        $map['memberID'] = $this->user['id'];
        $number1 = M("Order")->where($map)->count();

        $map['payStatus'] = 1;
        $map['memberID'] = $this->user['id'];
        $number2 = M("Order")->where($map)->count();

        $map['payStatus'] = 2;
        $map['memberID'] = $this->user['id'];
        $number3 = M("Order")->where($map)->count();

        $map['payStatus'] = 3;
        $map['memberID'] = $this->user['id'];
        $number4 = M("Order")->where($map)->count();

        $this->assign('number1',$number1);
        $this->assign('number2',$number2);
        $this->assign('number3',$number3);
        $this->assign('number4',$number4);
		$this->display();
	}

    public function setting(){
        $this->display();
    }

    //个人资料
    public function info(){
        if (request()->isPost()) { 
            if(!checkFormDate()){$this->error('未知错误');}
            $qq = input('post.qq');
            $weixin = input('post.weixin');
            $face = input('post.face');
            $data = [
                'face'=>$face,
                'qq'=>$qq,
                'weixin'=>$weixin
            ];
            $map['id'] = $this->user['id'];
            $r = M('Member')->where($map)->update($data);
            if ($r) {
                $this->success("操作成功",url('index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->display();
        }
    }

	//用户认证
	public function auth(){
		if (request()->isPost()) {            
            if(!checkFormDate()){$this->error('未知错误');}

            if ($this->user['auth']==1) {
                $this->error('您已通过认证');
            }

            $name = input('post.name');
            $sn = input('post.sn');
            $front = input('post.front');
            $back = input('post.back');

            unset($map);
            $map['memberID'] = $this->user['id'];
            $auth = M("Auth")->where($map)->find();
            if ($auth) {
                $this->error('不要重复提交');
            }
            
            $data = [
            	'memberID'=>$this->user['id'],
            	'name'=>$name,
            	'sn'=>$sn,
            	'front'=>$front,
            	'back'=>$back,
            	'createTime' => time()
            ];
            $r = M('Auth')->insert($data);
            if ($r) {
                $this->success('提交成功，等待管理员审核',url('index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $map['memberID'] = $this->user['id'];
            $auth = M("Auth")->where($map)->find();    
            $this->assign('auth',$auth);
            $this->display();
        }
	}

    //银行卡
    public function bank(){
        $list = M('Bankcard')->where(array('memberID'=>$this->user['id']))->select();
        foreach ($list as $key => $value) {
            switch ($value['type']) {
                case 1://微信
                    $list[$key]['typeName'] = $value['bank'];
                    break;
                case 2://银行卡
                    $list[$key]['typeName'] = '微信';
                    break;
                case 3://支付宝
                    $list[$key]['typeName'] = '支付宝';
                    break;
                default:
                    break;
            }
        } 
        $this->assign('list',$list);
        $this->display();
    }

    //添加银行卡
    public function addBank(){
        if (request()->isPost()) {
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $type = input('post.type');
            if (!in_array($type, array(1,2,3))) {
                $this->error('参数错误');
            }

            $count = M('Bankcard')->where(array('memberID'=>$this->user['id']))->count();
            if ($count>=3) {
                $this->error('收款账户不能超过3个');
            }
            $data = input("post.");
            if ($data['type']==2) {
                if ($data['weixin']=='') {   
                    $this->error('请输入微信账号'); 
                }     
                if ($data['name']=='') {     
                    $this->error('请输入姓名'); 
                }
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }elseif($data['type']==1){
                if ($data['bank']=='') {
                    $this->error('请选择银行');           
                }
                if ($data['account']=='') { 
                    $this->error('请输入开户行');  
                }  
                if ($data['cardNo']=='' || !is_numeric($data['cardNo'])) {
                    $this->error('请输入银行卡号'); 
                }
                if ($data['name']=='') {     
                    $this->error('请输入姓名'); 
                }
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }elseif($data['type']==3){
                if ($data['alipay']=='') {
                    $this->error('请输入支付宝账号'); 
                }    
                if ($data['name']=='') {
                    $this->error('请输入姓名'); 
                }     
                if (!check_mobile($data['mobile'])) {
                    $this->error('手机号码格式错误'); 
                }
            }
            $data['updateTime'] = time();
            $data['createTime'] = time();
            $data['memberID'] = $this->user['id'];
            $result = M('Bankcard')->insert($data);
            if ($result) {      
                $this->success('操作成功',url('member/bank'));               
            }else{
                $this->error('操作失败');
            }
        }else{ 
            $type = input('param.type',1);
            $this->assign('type',$type);
            $this->display();
        }        
    }

    //删除银行卡
    public function delbank(){
        if (request()->isPost()) {
            $id = input('param.id');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }else{
                $map['id'] = $id;
                $map['memberID'] = $this->user['id'];
                if (M('Bankcard')->where($map)->delete()) {
                    $this->success('删除成功','reload');
                }else{
                    $this->error('操作失败');
                }
            }
        }        
    }  

    public function pwd(){
        $this->display();
    }

    public function password(){
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $oldpassword = trim(input('post.oldpassword'));
            $password = trim(input('post.password'));
            $cpassword = trim(input('post.cpassword'));
            $id = $this->user['id'];
            $oldpwd = $this->user['password'];

            if($oldpwd!=md5($oldpassword)){
                $this->error('原登录密码错误！');
            }

            if($password!=$cpassword){
                $this->error('两次密码不一致！');  
            }

            $user=M('Member');
            $rsuser=$user->where(array('id'=>$id))->find();
            if(!$rsuser){
                $this->error('该用户不存在！');
            }
            $data['password']=md5($password);
            $rs = $user->where(array('id'=>$id))->update($data);
            if ($rs) {
                $this->success('修改成功！',url('Member/index'));
            }
        }else{
            $this->display();
        }
    }

    public function paypassword(){
        if(request()->isPost()){
            if (!checkRequest()) {
                $this->error('未知错误');
            }

            $oldpassword = trim(input('post.oldpassword'));
            $password = trim(input('post.password'));
            $cpassword = trim(input('post.cpassword'));
            $id = $this->user['id'];
            $oldpwd = $this->user['payPassword'];

            if($oldpwd!=md5($oldpassword)){
                $this->error('原安全密码错误！');
            }

            if($password!=$cpassword){
                $this->error('两次密码不一致！');  
            }

            $user=M('Member');
            $rsuser=$user->where(array('id'=>$id))->find();
            if(!$rsuser){
                $this->error('该用户不存在！');
            }
            $data['payPassword']=md5($password);
            $rs = $user->where(array('id'=>$id))->update($data);
            if ($rs) {
                $this->success('修改成功！',url('Member/index'));
            }
        }else{
            $this->display();
        }
    }

    //我的二维码  
    public function qr(){        
        $sncode=$this->user['sncode'];
        $turePath = '/face/'.$sncode."/";
        if (!is_dir('.'.$turePath)) {                
             mkdir('.'.$turePath);
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . url('Login/qrreg', array('sncode' => $sncode));

        if(file_exists('.'.$turePath.'qrcodes.jpg')){
            $filePath = $turePath.'qrcodes.jpg';
        }else{
            $filePath = '.'.$turePath.'qrcodes.jpg';
            //生成二维码
            $qrCode = new QrCode();
            $qrCode->setText($url)
                ->setSize(300)//大小
                ->setErrorCorrectionLevel('high')
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
            $qrCode->writeFile($filePath);
        }  
        $this->assign("filePath",$turePath.'qrcodes.jpg');
        $this->assign("url",$url);
        $this->display();
    }

    //单页面
    public function about(){
        $list = M("Agent")->where(['id'=>$this->agent['id']])->find();
        $this->assign('list',$list);
        $this->display();
    }

    //帮助
    public function help(){
        $map['agentID'] = $this->agent['id'];
        $list = M('AgentArticle')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function detail(){
        $id = I('get.id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;
        $map['agentID'] = $this->agent['id'];       
        $list = M('AgentArticle')->where($map)->find();
        if (!$list) {
            $this->error('文章不存在');
        }else{
            M('Article')->where($map)->setInc('hit');
            $this->assign('list',$list);
            $this->display();
        }
    }

    //帮助
    public function notice(){
        $map['del'] = 0;
        $map['status'] = 1;
        $map['cid'] = 2;
        $list = M('Article')->where($map)->order('id desc')->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function view(){
        $id = input('id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;
        $map['status'] = 1;
        $map['del'] = 0;        
        $list = M('Article')->where($map)->find();
        if (!$list) {
            $this->error('文章不存在');
        }else{
            M('Article')->where($map)->setInc('hit');
            $this->assign('list',$list);
            $this->display();
        }
    }

    public function feedback(){
        $map['memberID'] = $this->user['id'];
        $obj = M('Feedback');
        $list = $obj->where($map)->order('id desc')->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function write(){
        if (request()->isPost()) {
            if (!checkRequest()) {die;}
            
            $data = input('post.');
            $data['memberID'] = $this->user['id'];
            $res = model('Feedback')->saveData( $data );
            if ($res) {
                $this->success('操作成功',url('Member/feedback'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->display();
        }
    }

    public function message(){    
        $map['memberID'] = $this->user['id'];
        $list = M('Msg')->where($map)->order('id desc')->paginate(20,true);
        $this->assign('list',$list);
        $this->display();
    }

    public function read(){    
        $id = input('id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }

        $map['id'] = $id;
        $map['memberID'] = $this->user['id']; 
        $list = M('Msg')->where($map)->find();
        if (!$list) {
            $this->error('信息不存在');
        }else{
            M('Msg')->where($map)->setField('read','1');
            $this->assign('list',$list);
            $this->display();
        }
    }
}
