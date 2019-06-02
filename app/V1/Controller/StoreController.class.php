<?php
namespace V1\Controller;

class StoreController extends CommonController {

    public $user;

    public function _initialize() {
        parent::_initialize();
        $token = I('post.token');
        if (!$user = $this->checkToken($token)) {
            returnJson('999','登录超时'); 
        }else{
            $this->user = $user;
        }
    }

    public function address(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $map['memberID'] = $this->user['id']; 
            $list = M('Address')->where($map)->select();
            returnJson(0,'success',$list);                 
        }          
    }

    public function addressPub(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $obj = D('Address');
            if ($data = $obj->create()) {
                $data['memberID'] = $this->user['id'];
                if ($data['def']==1) {
                    $list = M('Address')->where(array('memberID'=>$this->user['id']))->setField('def',0);
                }
                if($data['front']!='' && strstr($data['front'], 'base64')){
                    $result = $this->base64_upload($data['front']);
                    if ($result) {
                        $data['front'] = $result['url'];
                    }else{
                        $data['front'] = '';
                    }
                }
                if($data['back']!='' && strstr($data['back'], 'base64')){
                    $result = $this->base64_upload($data['back']);
                    if ($result) {
                        $data['back'] = $result['url'];
                    }else{
                        $data['back'] = '';
                    }
                }
                if($data['id']!=''){
                    if ($list = $obj->save($data)) {
                        returnJson(0,'操作成功'); 
                    } else {
                        returnJson('-1','操作失败'); 
                    }
                }else{
                    if ($list = $obj->add($data)) {
                        returnJson(0,'操作成功'); 
                    } else {
                        returnJson('-1','操作失败'); 
                    }
                }
                
            } else {
                returnJson('-1','操作失败');
            }
        }
    }

    public function addressInfo(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }

            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $list = M('Address')->where($map)->find();
            if ($list) {
                $list['front'] = getRealUrl($list['front']);
                $list['back'] = getRealUrl($list['back']);
                returnJson(0,'success',$list);
            }else{
                returnJson('-1','信息不存在');
            }
        }
    }

    public function addressDel(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }

            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $res = M('Address')->where($map)->delete();
            if ($res) {
                returnJson(0,'success');
            }else{
                returnJson('-1','操作失败');
            }
        }
    }
}