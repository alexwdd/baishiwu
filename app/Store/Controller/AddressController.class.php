<?php
namespace Store\Controller;

class AddressController extends UserController
{
    public function index(){ 
        $map['memberID'] = $this->user['id'];
        $list = M('Address')->where($map)->select();
        $this->assign('list',$list);
        $this->display();      
    }

    public function add(){
        $content = $this->fetch();
        echo $content;
    }

    public function save(){
        if (!checkRequest()) {die;}      
        $agentid = I('agentid');      
        $this->all_add("Address",U('Address/index',array('agentid'=>$agentid,'token'=>I('token'))));
    }   

    public function setDefault(){
        $map['memberID'] = $this->user['id'];
        M('Address')->where($map)->setField('def',0);
        $id = I('id');
        $map['id']=$id;
        M('Address')->where($map)->setField('def',1);
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }   

    public function edit(){
        if (IS_POST) {
            if (!checkRequest()) {die;}
            $agentid = I('agentid'); 

            $obj = D("Address");
            if ($data = $obj->create()) {
                $obj->save($data);              
                $back = U('Address/index',array('agentid'=>$agentid,'token'=>I('token')));
                $this->success('操作成功',$back);          
            } else {
                $this->error($obj->getError());
            }
        }else{
            $id = I('get.id');
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $address = M('Address')->where($map)->find();
            if (!$address) {
                $this->error('信息不存在');
            }
            $this->assign('address',$address);
            $content = $this->fetch();
            echo $content;
        }
        
    }

    public function del(){
        $id = I('id');
        $map['id']=$id;
        $map['memberID'] = $this->user['id'];
        M('Address')->where($map)->delete();
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }
}
