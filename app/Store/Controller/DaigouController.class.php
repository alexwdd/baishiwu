<?php
namespace Store\Controller;
use Common\Controller\BaseController;

class DaigouController extends BaseController
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
            $list = M('DgOrder')->where($map)->find();
            if (!$list) {
                returnJson('-1','订单不存在');
            }           
            $agent = M("Agent")->where("id=".$list['agentID'])->find();

            $shouxufei = C('site.shouxufei');
            $huilv = $agent['huilv'];
            $sxf = ($list['total']*$shouxufei)/100;
            $data['shouxufei'] = $sxf;
            $data['rmb'] = ($list['total'] + $sxf)*$huilv;
            M('DgOrder')->where($map)->save($data);
            $list['rmb'] = round($data['rmb'],2);           
            if ($payType==2) {
                $result = $this->wxPub($list,$agent['cityID']);
            }else{
                $result = $this->aliPub($list,$agent['cityID']);
            }    
            returnJson(0,'success',$result);            
        }
    }

    //支付宝下单
    public function aliPub($order,$cityID){
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

        vendor('appalipay.AopSdk');// 加载类库

        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $config['ALI_APP_ID'];
        $aop->rsaPrivateKey = $config['ALI_PRI_KEY'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $config['ALI_PUB_KEY'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $content = array();
        $content['body'] = '在线支付';
        $content['subject'] = '购买商品';//商品的标题/交易标题/订单标题/订单关键字等  
        $content['out_trade_no'] = $order['order_no'];//商户网站唯一订单号
        $content['timeout_express'] = '1d';//该笔订单允许的最晚付款时间

        $content['total_amount'] = floatval($order['rmb']);//订单总金额(必须定义成浮点型) 
        //$content['total_amount'] = 0.01;//订单总金额(必须定义成浮点型)
        $content['product_code'] = 'QUICK_MSECURITY_PAY';//
        
        $bizcontent = json_encode($content);
        $request->setNotifyUrl(C('site.domain').'/store/daigou/alinotify/');
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        $str = $response;//就是orderString 可以直接给客户端请求，无需再做处理。         
        return array('url'=>$str,'order_no'=>$order['order_no']);  
    }

    //微信下单
    public function wxPub($order,$cityID){         
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
        $unifiedOrder->setParameter("body",'购买商品');//商品描述
        //自定义订单号，此处仅作举例
        $unifiedOrder->setParameter("out_trade_no",$order['order_no']);//商户订单号 
        $unifiedOrder->setParameter("total_fee",(int)($order['rmb']*100));//总金额
        //$unifiedOrder->setParameter("total_fee",1);//总金额

        //通知地址 
        $unifiedOrder->setParameter("notify_url",C('site.domain').'/store/daigou/wxnotify/');
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
                $url = C('site.domain').'/store/daigou/changeOrderStatus/';
                $res = $this->https_post($url,$data);               
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }
        }
    }

    //支付宝异步通知
    public function alinotify(){
        //商户订单号
        $out_trade_no = $_POST['out_trade_no'];
        //支付宝交易号
        $trade_no = $_POST['trade_no'];
        //交易状态
        $trade_status = $_POST['trade_status'];
        //订单的实际金额
        $total_amount = $_POST['total_amount'];
        //判断交易通知状态是否为TRADE_SUCCESS或TRADE_FINISH
        if($trade_status!='TRADE_FINISH' && $trade_status !='TRADE_SUCCESS'){
            exit('fail');
        }
        //验证订单的准确性
        if(!empty($out_trade_no)){
            $data['order_no'] = $out_trade_no;
            $data['payType'] = 1;
            $url = C('site.domain').'/store/daigou/changeOrderStatus/';
            $res = $this->https_post($url,$data);
        }
        echo 'success';
    }

    //修改商城订单状态
    public function changeOrderStatus(){
        $map['order_no'] = I('post.order_no');
        $list = M('DgOrder')->where($map)->find();
        if ($list) {
            if ($list['payStatus'] > 0) {
                exit('该订单已经支付完成，请不要重复操作');  
            }else{
                //更新订单状态
                $data['payStatus'] = 2;
                $data['payType'] = I('post.payType');
                $data['updateTime'] = time();
                M('DgOrder')->where($map)->save($data);
                M('DgOrderBaoguo')->where(array('orderID'=>$list['id']))->setField('status',1);
                M('DgOrderPerson')->where(array('orderID'=>$list['id']))->setField('status',1);

                //发送短信
                $notifyMobile = M("Agent")->where(array('id'=>$list['agentID']))->getField('notifyMobile');
                if($notifyMobile!=''){
                    $msg = '【澳洲生活圈】您有新的订单，订单号'.$list['order_no'].'，请及时处理。';
                    send_cn_sms($notifyMobile,$msg);
                }
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
        $list = M('DgOrder')->where($map)->find();
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
}
