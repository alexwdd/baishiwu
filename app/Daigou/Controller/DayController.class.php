<?php
namespace Daigou\Controller;

class DayController extends CommonController {
	#列表
	public function index() {
		$date = I('post.date');
        if ($date=='') {
            $beginDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }else{
            $date = explode(" - ", $date);
            $beginDate = $date[0];
            $endDate = $date[1];
        }       

        $map['createTime'] = array('between',array(strtotime($beginDate),strtotime($endDate)+86399));
        $map['payStatus'] = array('in',[2,3,4]);
        $map['agentID'] = $this->user['id'];
        $list = M("DgOrder")->where($map)->select();
        $orderIds = M("DgOrder")->where($map)->getField('id',true);
        if(!$orderIds){
            $orderIds=[0];
        }
        $type = [
            'money'=>0,
            'pay1'=>0,
            'pay2'=>0,
            'pay3'=>0,
            'wuliu'=>0,
            'goodsMoney'=>0
        ];
        //1支付宝 2微信 3银行卡支付
        foreach ($list as $k => $val) {
            if ($val['payType'] == '1') {
                $type['pay1'] += $val['total'];
            }
            if ($val['payType'] == '2') {
                $type['pay2'] += $val['total'];
            }
            if ($val['payType'] == '3') {
                $type['pay3'] += $val['total'];
            }
            $type['money'] += $val['total'];
            $type['wuliu'] += $val['wuliuInprice'];
        }
        unset($map);
        $map['orderID'] = array('in',$orderIds);
        $list = M('DgOrderDetail')->field('goodsID,number')->where($map)->select();
        foreach ($list as $key => $value) {
            $inprice = M("DgGoods")->where("id",$value['goodsID'])->getField("inprice");
            $type['goodsMoney'] += $inprice * $value['number'];
        }

        $this->assign('type',$type);
        $this->assign('beginDate',$beginDate);
        $this->assign('endDate',$endDate);
		$this->display();
	}
}
?>