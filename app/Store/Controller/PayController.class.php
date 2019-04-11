<?php
namespace Store\Controller;
use Common\Controller\BaseController;

class PayController extends BaseController
{
    public function _initialize(){
        parent::_initialize();
    }

    public function submit(){
    	if (IS_POST) {
			if (!checkFormDate()){returnJson('-1','未知错误');}
			$order_no = I('post.order_no');		
			$payType = I('post.payType');	

			if(!in_array($payType,[1,2])){
				returnJson('-1','支付方式错误');
			}	

			$map['order_no'] = $order_no;
			$map['payStatus'] = 0;
			$list = M('Order')->where($map)->find();
			if (!$list) {
				returnJson('-1','订单不存在');
			}
	        $cityID = M("Agent")->where("id=".$list['agentID'])->getField("cityID");
    		if ($payType==2) {
    			$result = $this->wxPub($list,$cityID,'store');
    		}else{
                returnJson('-1','暂不支持支付宝支付');
            }    
    		returnJson(0,'success',$result);	    	
    	}
    }

    //微信下单
    public function wxPub($order,$cityID,$type){         
        switch ($cityID) {
            case '9':
                $config = C("AOZHOU_PAY");
                break;
            case '39':
                $config = C("XINJIAPO_PAY");
                break;
            default:
                return '当前城市未开通在线支付';
                break;
        }
    	
        import('Common.ORG.Weixinpay.WxPayPubHelper');
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub($config['APP_ID'],$config['MCH_ID'],$config['MCH_KEY']);
        $jsApi = new \JsApi_pub($config['APP_ID'],$config['MCH_ID'],$config['MCH_KEY']); 

        if ($type=='store') {
            $unifiedOrder->setParameter("body",'购买商品');//商品描述
        }elseif($type=='tuan'){
            $unifiedOrder->setParameter("body",'拼邮支付');//商品描述
        }
        //自定义订单号，此处仅作举例
        $unifiedOrder->setParameter("out_trade_no",$order['order_no']);//商户订单号 
        //$unifiedOrder->setParameter("total_fee",$order['rmb']*100);//总金额
        $unifiedOrder->setParameter("total_fee",1);//总金额

        //通知地址 
        if ($type=='store') {
            $unifiedOrder->setParameter("notify_url",C('site.domain').'/store/pay/wxnotify/');
        }elseif($type=='tuan'){
            $unifiedOrder->setParameter("notify_url",C('site.domain').'/store/pay/wxTuanNotify/');
        }
        
        $unifiedOrder->setParameter("trade_type","APP");//交易类型
        $unifiedOrder->setParameter("spbill_create_ip",get_client_ip());//客户端IP

        $prepay_id = $unifiedOrder->getPrepayId();

        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getAppParameters();
        $jsApiParameters['order_no'] = $order['order_no'];
        return $jsApiParameters;
    }

    //商城微信异步通知
    public function wxnotify(){    	
    	$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?  $GLOBALS["HTTP_RAW_POST_DATA"]  : "" ;
		if (!empty($postStr)){	
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			if ($postObj->result_code == 'SUCCESS') {
				$data['order_no'] = $postObj->out_trade_no;
				$data['payType'] = 2;
    			$url = C('site.domain').'/store/pay/changeOrderStatus/';
    			$res = $this->https_post($url,$data);			    
		        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
			}
		}
    }

    //修改商城订单状态
    public function changeOrderStatus(){
    	$map['order_no'] = I('post.order_no');
        $list = M('Order')->where($map)->find();
        if ($list) {
            if ($list['payStatus'] > 0) {
                exit('该订单已经支付完成，请不要重复操作');  
            }else{
                //更新订单状态
                $data['payStatus'] = 1;
              	$data['status'] = 2;
                $data['updateTime'] = time();
                $data['payType'] = I('post.payType');
                M('Order')->where($map)->save($data);
            }
        }else{
            exit('订单不存在');  
        }
    }

    public function ok(){
        //获取订单信息  
        $order_no = I('get.order_no');
        if ($order_no=='') {die;}
        $map['order_no'] = $order_no;
        $list = M('Order')->where($map)->find();
        if (!$list) {
            $this->error('订单不存在，或已支付');
        }
        $this->assign('list',$list);

        unset($map);
        $map['id'] = $list['memberID'];
        $member = M('Member')->where($map)->find();
        $this->assign('member',$member);
        $this->display();
    }


    public function tuanPay(){
        if (IS_POST) {
            if (!checkFormDate()){returnJson('-1','未知错误');}
            $order_no = I('post.order_no');     
            $payType = I('post.payType');   

            if(!in_array($payType,[1,2])){
                returnJson('-1','支付方式错误');
            }   

            $map['order_no'] = $order_no;
            $map['status'] = 0;
            $list = M('TuanBill')->where($map)->find();
            if (!$list) {
                returnJson('-1','订单不存在');
            }

            $cityID = M("Tuan")->where("articleid=".$list['articleid'])->getField("cityID");
            if ($payType==2) {
                $list['rmb'] = $list['money'];
                $result = $this->wxPub($list,$cityID,'tuan');
            }else{
                returnJson('-1','暂不支持支付宝支付');
            } 
            returnJson(0,'success',$result);
        }
    }

    //拼团微信异步通知
    public function wxTuanNotify(){     
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?  $GLOBALS["HTTP_RAW_POST_DATA"]  : "" ;
        if (!empty($postStr)){  
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($postObj->result_code == 'SUCCESS') {
                $data['order_no'] = $postObj->out_trade_no;
                $data['payType'] = 2;
                $url = C('site.domain').'/store/pay/changeTuanStatus/';
                $res = $this->https_post($url,$data);               
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }
        }
    }

    //修改拼团订单状态
    public function changeTuanStatus(){
        $map['order_no'] = I('post.order_no');
        $list = M('TuanBill')->where($map)->find();
        if ($list) {
            if ($list['status'] > 0) {
                exit('该订单已经支付完成，请不要重复操作');  
            }else{
                //更新订单状态
                $data['status'] = 2;
                $data['payType'] = I('post.payType');
                M('TuanBill')->where($map)->save($data);
            }
        }else{
            exit('订单不存在');  
        }
    }
}
