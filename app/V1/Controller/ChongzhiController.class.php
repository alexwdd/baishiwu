<?php
namespace V1\Controller;

class ChongzhiController extends CommonController {

	public function _initialize(){
        parent::_initialize();
        header('Access-Control-Allow-Origin:*');
    } 

    public function index(){
    	if (IS_POST) {
			if (!checkFormDate()){returnJson(0,'未知错误');}

			$map['cid']=126;
			$ad = M('Ad')->field('name as title,articleid,type,image,url')->where($map)->order('sort asc , id desc')->limit(5)->select();
			foreach ($ad as $key => $value) {
				$ad[$key]['image'] = 'http://' . $_SERVER['HTTP_HOST'] .$value['image'];
              	if($value['url']!=''){
					$ad[$key]['url'] = 'app://html?url='.$value['url'].'&title='.$value['title'];
				}else{
					$ad[$key]['url'] = 'app://articledetail?articleid='.$value["articleid"].'&type='.$value['type'];
				}
			}

			unset($map);
	    	$list = C('phoneType');
	    	foreach ($list as $key => $value) {
	    		$list[$key]['img'] = 'http://' . $_SERVER['HTTP_HOST'] .$value['img'];
	    		$list[$key]['aimg'] = 'http://' . $_SERVER['HTTP_HOST'] .$value['aimg'];

	    		unset($map);
	    		$map['type'] = $value['id'];
	    		$map['show'] = 1;
	    		$goods = M('PhoneCard')->field('id,cate,name,money,price,rmb,picname')->where($map)->order('sort asc, id desc')->select();
	    		$huafei = [];
	    		$liuliang = [];
	    		$taocan = [];
	    		$config = tpCache("xjp");
	    		foreach ($goods as $k => $val) {
	    			$goods[$k]['picname'] = 'http://' . $_SERVER['HTTP_HOST'] .$val['picname'];
	    			$goods[$k]['typeName'] = $value['name'];
	    			$goods[$k]['rmb'] = sprintf("%.2f",$config['rate']*$val['price']);
	    			if ($val['cate']==1) {
	    				array_push($huafei, $goods[$k]);
	    			}
	    			if ($val['cate']==2) {
	    				array_push($liuliang, $goods[$k]);
	    			}
	    			if ($val['cate']==3) {
	    				array_push($taocan, $goods[$k]);
	    			}
	    		}
	    		$list[$key]['huafei'] = $huafei;
	    		$list[$key]['liuliang'] = $liuliang;
	    		$list[$key]['taocan'] = $taocan;
	    	}
	    	returnJson(1,'success',['ad'=>$ad,'type'=>$list]);
    	}
    }

    public function createOrder(){
    	if (IS_POST) {
			if (!checkFormDate()){returnJson('-1','未知错误');}
			$mobile = I('post.mobile');
			$payType = I('post.payType');
			$goodsID = I('post.goodsID');

			if ($mobile == '') {
				returnJson('-1','请输入手机号码');
			}

			if (!in_array($payType, [1,2])) {
				returnJson('-1','支付方式错误');
			}

			if ($goodsID == '' || !is_numeric($goodsID)) {
				returnJson('-1','参数错误');
			}

			$map['id'] = $goodsID;
			$map['show'] = 1;
			$list = M('PhoneCard')->where($map)->find();
			if (!$list) {
				returnJson('-1','商品不存在');
			}

	    	foreach (C('phoneType') as $key => $value) {
	    		if($value['id']==$list['type']){
	    			$list['typeName'] = $value['name'];
	    		}
	    	}
	    	$config = tpCache("xjp");
	    	$rmb = sprintf("%.2f",$config['rate']*$list['price']);
	    	$data = [
	    		'memberID'=>0,
	    		'order_no'=>getOrderNo("PH"),
	    		'mobile'=>$mobile,
	    		'type'=>$list['type'],
	    		'typeName'=>$list['typeName'],
	    		'goodsID'=>$list['id'],
	    		'goodsName'=>$list['name'],
	    		'money'=>$list['money'],
	    		'price'=>$list['price'],
	    		'rmb'=>$rmb,
	    		'productID'=>$list['productID'],
	    		'apiOrderNo'=>'',
	    		'payType'=>$payType,
	    		'status'=>0,
	    		'createTime'=>time(),
	    		'updateTime'=>time(),
	    	];
	    	$res = M('PhoneOrder')->add($data);
	    	if ($res) {   		
	    		returnJson(1,'success',['url'=>'http://www.angelbuy-au.com/mobile/chongzhi.html?order_no='.$data['order_no']]);
	    	}else{
	    		returnJson('0','操作失败');
	    	}
    	}
    }

    public function getOrderInfo(){
    	$map['order_no'] = I('get.order_no');
    	$list = M('PhoneOrder')->where($map)->find();
    	if ($list) {
    		echo json_encode(['code'=>1,'data'=>$list]);
    	}else{
    		echo json_encode(['code'=>0]);
    	}
    }

    public function submit(){
    	if (IS_POST) {
			if (!checkFormDate()){returnJson('-1','未知错误');}
			$mobile = I('post.mobile');
			$payType = I('post.payType');
			$goodsID = I('post.goodsID');

			if ($mobile == '') {
				returnJson('-1','请输入手机号码');
			}

			if (!in_array($payType, [1,2])) {
				returnJson('-1','支付方式错误');
			}

			if ($goodsID == '' || !is_numeric($goodsID)) {
				returnJson('-1','参数错误');
			}

			$map['id'] = $goodsID;
			$map['show'] = 1;
			$list = M('PhoneCard')->where($map)->find();
			if (!$list) {
				returnJson('-1','商品不存在');
			}

	    	foreach (C('phoneType') as $key => $value) {
	    		if($value['id']==$list['type']){
	    			$list['typeName'] = $value['name'];
	    		}
	    	}
	    	$config = tpCache("xjp");
	    	$rmb = sprintf("%.2f",$config['rate']*$list['price']);
	    	$data = [
	    		'memberID'=>0,
	    		'order_no'=>getOrderNo("PH"),
	    		'mobile'=>$mobile,
	    		'type'=>$list['type'],
	    		'typeName'=>$list['typeName'],
	    		'goodsID'=>$list['id'],
	    		'goodsName'=>$list['name'],
	    		'money'=>$list['money'],
	    		'price'=>$list['price'],
	    		'rmb'=>$rmb,
	    		'productID'=>$list['productID'],
	    		'apiOrderNo'=>'',
	    		'payType'=>$payType,
	    		'status'=>0,
	    		'createTime'=>time(),
	    		'updateTime'=>time(),
	    	];
	    	$res = M('PhoneOrder')->add($data);
	    	if ($res) {
	    		if ($data['payType']==2) {
	    			$result = $this->wxPub($data);
	    		}else{
	    			$result = $this->aliPub($data);
	    		}    		
	    		returnJson(0,'success',$result);
	    	}else{
	    		returnJson('-1','操作失败');
	    	}
    	}
    }

    //支付宝统一下单
    public function aliPub($order){
    	$config = C("XINJIAPO_PAY");

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
		$content['subject'] = '手机充值';//商品的标题/交易标题/订单标题/订单关键字等
		$content['out_trade_no'] = $order['order_no'];//商户网站唯一订单号
		$content['timeout_express'] = '1d';//该笔订单允许的最晚付款时间
		$content['total_amount'] = floatval($order['rmb']);//订单总金额(必须定义成浮点型)
		#$content['total_amount'] = 0.01;//订单总金额(必须定义成浮点型)
		$content['product_code'] = 'QUICK_MSECURITY_PAY';//

		$bizcontent = json_encode($content);
		$request->setNotifyUrl(C('site.domain').'/v1/chongzhi/alinotify/');
		$request->setBizContent($bizcontent);
		//这里和普通的接口调用不同，使用的是sdkExecute
		$response = $aop->sdkExecute($request);
		//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
		$str = $response;//就是orderString 可以直接给客户端请求，无需再做处理。
		return array('url'=>$str,'order_no'=>$order['order_no']);
    }

    //微信下单
    public function wxPub($order){ 
    	$config = C("XINJIAPO_PAY");
        import('Common.ORG.Weixinpay.WxPayPubHelper');
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub($config['APP_ID'],$config['MCH_ID'],$config['MCH_KEY']);
        $jsApi = new \JsApi_pub($config['APP_ID'],$config['MCH_ID'],$config['MCH_KEY']); 

        $unifiedOrder->setParameter("body",'手机充值');//商品描述
        //自定义订单号，此处仅作举例
        $unifiedOrder->setParameter("out_trade_no",$order['order_no']);//商户订单号 
        $unifiedOrder->setParameter("total_fee",$order['rmb']*100);//总金额
        //$unifiedOrder->setParameter("total_fee",1);//总金额
        $unifiedOrder->setParameter("notify_url",C('site.domain').'/v1/chongzhi/wxnotify/');//通知地址 
        $unifiedOrder->setParameter("trade_type","APP");//交易类型
        $unifiedOrder->setParameter("spbill_create_ip",get_client_ip());//客户端IP

        $prepay_id = $unifiedOrder->getPrepayId();

        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getAppParameters();
        $jsApiParameters['order_no'] = $order['order_no'];
        return $jsApiParameters;
    }

    public function alinotify(){
    	$config = C("XINJIAPO_PAY");
    	vendor('appalipay.AopSdk');// 加载类库
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $config['ALI_PUB_KEY'];

        //file_put_contents("log".date("Y-m-d",time()).".txt", date ( "Y-m-d H:i:s" ) . "  "."订单" .$_POST['out_trade_no']. "\r\n", FILE_APPEND);

        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if($flag){
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            //订单的实际金额
            $total_amount = $_POST['total_amount'];
            //appid
            $appid = $_POST['app_id'];
            $seller_id = $_POST['seller_id'];
            //验证app_id是否为商户本身
            if($appid != $config['ALI_APP_ID']){
                exit('fail');
            }
            //判断交易通知状态是否为TRADE_SUCCESS或TRADE_FINISH
            if($trade_status!='TRADE_FINISH' && $trade_status !='TRADE_SUCCESS'){
                exit('fail');
            }            

            //验证订单的准确性
            if(!empty($out_trade_no)){
                $data['order_no'] = $out_trade_no;
    			$url = C('site.domain').'/v1/chongzhi/changeOrderStatus/';
    			$res = $this->https_post($url,$data);
    			//file_put_contents("log".date("Y-m-d",time()).".txt", date ( "Y-m-d H:i:s" ) . "  "."订单" .$out_trade_no. "，".$url."\r\n", FILE_APPEND);
            }
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    public function wxnotify(){    	
    	$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?  $GLOBALS["HTTP_RAW_POST_DATA"]  : "" ;
		if (!empty($postStr)){	
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			if ($postObj->result_code == 'SUCCESS') {
				$data['order_no'] = $postObj->out_trade_no;
    			$url = C('site.domain').'/v1/chongzhi/changeOrderStatus/';
    			$res = $this->https_post($url,$data);			    
		        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
			}
		}
    }

    public function changeOrderStatus(){
    	$map['order_no'] = I('post.order_no');
        $list = M('PhoneOrder')->where($map)->find();
        if ($list) {
            if ($list['status'] > 0) {
                exit('该订单已经支付完成，请不要重复操作');  
            }else{
                //更新订单状态
                $data['status'] = 1;
                $data['updateTime'] = time();
                M('PhoneOrder')->where($map)->save($data);
                $this->doChongzhi($list);              
            }
        }else{
            exit('订单不存在');  
        }
    }

    public function doChongzhi($order){
    	$config = C("XINJIAPO_PAY");
    	$url = 'http://www.topupcard.sg/api20/topup.asmx/SendEasyTopup?userName='.$config['PHONE_NAME'].'&password='.$config['PHONE_PASSWORD'].'&productID='.$order['productID'].'&quantity='.$order['money'].'&MobileNo='.$order['mobile'].'&CircleCode=proNO1&PaymentMethod=9&Originator=SGLIFECIRCLE';
    	$con = file_get_contents($url);
		$p = xml_parser_create();
		xml_parse_into_struct($p, $con, $vals, $index);
		xml_parser_free($p);

		$result = $vals[0]['value'];
		if (strlen($result)==7) {
			$data['apiOrderNo'] = $result;
			$data['orderStatus'] = 1;			
		}else{
			$data['errorCode'] = $result;
			$data['orderStatus'] = 0;
		}
      	$data['result'] = $con;
		M("PhoneOrder")->where(array('id'=>$order['id']))->save($data);
    }

    public function info(){
    	if(IS_POST){
    		if (!checkFormDate()){returnJson(0,'未知错误');}
    		//获取订单信息  
	        $order_no = I('post.order_no');
	        if ($order_no=='') {
	        	returnJson(0,'参数错误');
	        }
	        $map['order_no'] = $order_no;
	        $list = M('PhoneOrder')->where($map)->find();
	        if (!$list) {
	            returnJson(0,'订单不存在');
	        }
	        $list['time'] = date("Y-m-d H:i:s",$list['createTime']);
	        if ($list['payType']==1) {
	        	$list['pay'] = '支付宝支付';
	        }else{
	        	$list['pay'] = '微信支付';
	        }
	        returnJson(1,'success',$list);
    	}
    }
}