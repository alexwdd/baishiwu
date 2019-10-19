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

    public function mprint(){
		$ids = I('get.ids');
		$ids = explode(",",$ids);

		$map['eimg'] = array('neq','');
		$map['id'] = array('in',$ids);
		M("DgOrderBaoguo")->where($map)->setField('print',1);

		$list = M("DgOrderBaoguo")->where($map)->select();
		$this->assign('list',$list);

		unset($map);
		$map['id'] = array('in',$ids);
		$map['eimg'] = array('neq','');
		$map['type'] = array('in',[1,2,3]);
		$map['sign'] = array('eq','');
		M("DgOrderBaoguo")->where($map)->save(['flag'=>1,'updateTime'=>time()]);


		foreach ($list as $key => $value) {
			unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['print'] = 0;
        	$printNumber = M("DgOrderBaoguo")->where($where)->count();//未打印总数

        	unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['flag'] = 0;
        	$flagNumber = M("DgOrderBaoguo")->where($where)->count();//未发货总数


        	unset($map);
    		$map['id'] = $value['orderID'];
    		$map['payStatus'] = array('in',[2,3]);
        	if ($flagNumber==0 && $printNumber==0) {
        		M("DgOrder")->where($map)->setField("payStatus",4);
        	}elseif($printNumber==0){
	        	M("DgOrder")->where($map)->setField("payStatus",3);
        	}
		}
		$this->display();
	}
}