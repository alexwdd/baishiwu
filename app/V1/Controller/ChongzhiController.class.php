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
	    			returnJson('-1','暂不⽀持⽀付宝支付');
	    		}    		
	    		returnJson(0,'success',$result);
	    	}else{
	    		returnJson('-1','操作失败');
	    	}
    	}
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