<?php
namespace Daigou\Controller;
use Common\Controller\BaseController;

class AgsController extends BaseController {

    public function index(){        
    	echo '1';
        $order_no = I('get.order_no');
        if($order_no==''){die;}
        $map['order_no'] = $order_no;
        $list = M('DgOrderBaoguo')->where($map)->select();
        $this->assign('list',$list);
        $this->display();
    }

}