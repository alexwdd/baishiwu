<?php
namespace Store\Controller;
use Common\Controller\BaseController;

class ShareController extends BaseController
{
    public function pay(){
        $id = I('orderID');
        $map['id'] = $id;
        $map['del'] = 0;
        $list = M('Order')->where($map)->find();
        if ($list) {
            $pay = M('Card')->where('id='.$list['payType'])->find();
            $this->assign('pay',$pay);
            $this->assign('list',$list);
            $this->display();
        }else{
            $this->error("没有该订单");
        }
    } 
}
