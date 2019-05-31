<?php
namespace V1\Controller;

class StoreController extends CommonController {

    public function addressPub(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $token = I('post.token');

            if (!$user = $this->checkToken($token)) {
                returnJson('999','登录超时'); 
            }

            $obj = D('Address');
            if ($data = $obj->create()) {
                if ($list = $obj->add($data)) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error($obj->getError());
            }
        }
    }
}