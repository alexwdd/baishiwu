<?php
namespace Daigou\Controller;

class OrderController extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
	        $status = I('post.status');        
	        $account = I('post.account');
	        $payType = I('post.payType');
	        $order_no = I('post.order_no');
	        $createDate = I('post.createDate');
	        
	        if ($status!='') {
	            $map['payStatus'] = $status;
	        }
	        if ($payType!='') {
	            $map['payType'] = $payType;
	        }
	        if ($account!='') {
	            $map['name|mobile'] = $account;
	        }
	        if ($order_no!='') {
	            $map['order_no'] = $order_no;
	        }
	        if ($createDate!='') {
	            $date = explode(" - ", $createDate);
	            $startDate = $date[0];
	            $endDate = $date[1];
	            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
	        }
	        $map['del'] = 0;
			$map['agentID'] = $this->user['id'];
			$obj = M('DgOrder');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['pay'] = getPayType($value['payType']);
                $list[$key]['baoguoNumber'] = M('DgOrderBaoguo')->where('orderID',$value['id'])->count();
                $list[$key]['lirun'] = $value['total']-$value['inprice']-$value['wuliuInprice'];
            }
            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>$pageNum,
                    "pageSize"=>$pageSize,
                    "pages"=>$pageSize,
                    "total"=>$total
                )
            );
            echo $this->return_json($result);
		}else{
			$this->display();
		}
	}

	//订单包裹详情
	public function baoguo(){
		if(IS_POST){
            $orderID = I('id'); 
            $sendTime = I('post.sendTime');
            $chengben = I('post.chengben');
            $status = I('post.status');
	        $remark = I('post.remark');

            $order['chengben'] = $chengben;
            $order['status'] = $status;
            if ($status>1) {
            	$order['payStatus'] = 1;
            }
	        $order['remark'] = $remark;
            if ($sendTime!='') {
            	$order['sendTime'] = strtotime($sendTime);
            }else{
            	$order['sendTime'] = 0;
            }
            M('Order')->where(array('id'=>$orderID,'agentID'=>$this->user['id']))->save($order);

            $this->success('操作成功',U('order/index')); 
        }else{
			$id = I("get.id");
			if (!isset ($id)) {
				$this->error('参数错误');
			}
			$obj = M('DgOrder');
			$map['del'] = 0;
			$map['id'] = $id;
			$map['agentID'] = $this->user['id'];
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			}else{
				$goods = M("DgOrderDetail")->field('*,sum(number) as num')->where(array('orderID'=>$list['id']))->group('itemID')->select(); 
				foreach ($goods as $key => $value) {
	                $item = M('DgGoodsIndex')->where(array('id'=>$value['itemID']))->find(); 
	                if ($value['server']!='') {
	                    $serverID = explode(",",$value['server']);
	                    unset($map);
	                    $map['id'] = array('in',$serverID);
	                    $server = M("server")->field('short,price')->where($map)->select();
	                    $goods[$key]['server'] = $server;
	                }else{
	                    $goods[$key]['server'] = null;
	                }  
	            }
            	$this->assign('goods',$goods);

				$person = M("DgOrderPerson")->where(array('orderID'=>$list['id']))->select();
	            foreach ($person as $key => $value) {
	                $baoguo = M('DgOrderBaoguo')->where(array('personID'=>$value['id']))->select();
	                foreach ($baoguo as $k => $val) {
	                    $baoguo[$k]['goods'] = M('DgOrderDetail')->where(array('baoguoID'=>$val['id']))->select();
	                    if($val['image']){
	                        $baoguo[$k]['image'] = explode(",", $val['image']);
	                    }
	                    if($val['eimg']){
	                        $baoguo[$k]['eimg'] = explode(",", $val['eimg']);
	                    }
	                }
	                $person[$key]['baoguo'] = $baoguo;
	            }            
	            $this->assign('person',$person);

	            if ($list['discount']=='0' || $list['discount']=='') {
	            	$list['discount'] = '无';
	            }
	            $this->assign('list',$list);
				$this->display();
			}
		}
	}

	public function wuliu(){
		if(IS_POST){			
			$id = I("post.id");
			$data['kdNo'] = I("post.kdNo");
			$data['eimg'] = I("post.eimg");
			$data['image'] = I("post.image");
			#$data['flag'] = I("post.flag");
			$orderID = I("post.orderID");

			if ($id=='') {
	            $this->error('参数错误');
	        }
	        if ($data['kdNo']=='') {
	            $this->error('请输入运单号');
	        }else{
	        	$data['kdNo'] = str_replace("，",",",$data['kdNo']);
	        }
	        $map['id'] = $id;
	        if ($data['image']) {
	        	$data['flag'] = 1;
	        }else{
	        	$data['flag'] = 0;
	        }
	        $res = M('DgOrderBaoguo')->where($map)->save($data);
	        if ($res) {
	        	if ($data['flag']==1) {
	        		$where['orderID'] = $orderID;
		        	$where['flag'] = 0;
		        	$count = M("DgOrderBaoguo")->where($where)->count();
		        	if ($count==0) {
		        		unset($map);
		        		$map['id'] = $orderID;
		        		$map['payStatus'] = array('in',[2,3]);
		        		M("DgOrder")->where($map)->setField("payStatus",4);
		        	}
	        	}
	        }
	        $this->success("操作成功");
		}else{
			$id = I("id");
			$map['id'] = $id;
			$list = M("DgOrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			if ($list['eimg']) {
            	$list['eimg'] = explode(",", $list['eimg']);
            }
			if ($list['image']) {
            	$list['image'] = explode(",", $list['image']);
            }
			$this->assign('list',$list);
			$this->display();
		}
	}

	public function image(){
        //获取要下载的文件名
        $filename = '.'.I('img');
        //设置头信息
        header('Content-Disposition:attachment;filename=' . basename($filename));
        header('Content-Length:' . filesize($filename));
        //读取文件并写入到输出缓冲
        readfile($filename);
    }

	//创建运单
	public function create(){
		if (request()->isPost()) {
			$id = I("post.id");
			if ($id=="" || !is_numeric($id)) {
				$this->error("参数错误");
			}
			$map['id']=$id;
			$list = M("OrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			$res = $this->createSingleOrder($list);
			if ($res['code']==1) {
				M("DgOrderBaoguo")->where($map)->setField('kdNo',$res['msg']);
				$this->success("操作成功，运单号：".$res['msg']);
			}else{
				$this->error($res['msg']);
			}
		}
	}

	public function uploadPhoto(){
		if (request()->isPost()){
			$id = I("post.id");
			if ($id=="" || !is_numeric($id)) {
				$this->error("参数错误");
			}

			$map['id']=$id;
			$list = M("DgOrderBaoguo")->where($map)->find();
			if (!$list) {
				$this->error("信息不存在");
			}
			if ($list['kdNo']=='') {
				$this->error("请先生成运单");
			}
			$order = M('DgOrder')->where(array('id'=>$list['orderID']))->find();

			if ($order['front']=='' || $order['back']=='') {
				$this->error("请先完善身份证信息");
			}

			$config = C("aue");
			$token = $this->getAueToken();
			$data = [
				'OrderIds'=>[$list['kdNo']],
				'ReceiverName'=>$order['name'],
				'ReceiverPhone'=>$order['mobile'],
				'PhotoID'=>$order['sn'],
				'PhotoFront'=>base64EncodeImage('.'.$order['front']),
				'PhotoRear'=>base64EncodeImage('.'.$order['back'])
			];

			$url = 'http://aueapi.auexpress.com/api/PhotoIdUpload';
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
			$result = curl_exec($ch);
			$result = json_decode($result,true);
			if ($result['Code']==0 && $result['ReturnResult']=='Success') {
				M("OrderBaoguo")->where($map)->setField('snStatus',1);
				$this->success("上传成功");
			}else{
				$this->error("操作失败");
			}
		}
	}

	public function mprint(){
		$ids = I('get.ids');
		$ids = explode(",",$ids);

		$map['eimg'] = array('neq','');
		$map['id'] = array('in',$ids);
		M("OrderBaoguo")->where($map)->setField('print',1);

		$list = M("OrderBaoguo")->where($map)->select();
		$this->assign('list',$list);

		unset($map);
		$map['id'] = array('in',$ids);
		$map['eimg'] = array('neq','');
		$map['type'] = array('in',[1,2,3]);
		$map['sign'] = array('eq','');
		M("OrderBaoguo")->where($map)->save(['flag'=>1,'updateTime'=>time()]);


		foreach ($list as $key => $value) {
			unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['print'] = 0;
        	$printNumber = M("OrderBaoguo")->where($where)->count();//未打印总数

        	unset($where);
			$where['orderID'] = $value['orderID'];
        	$where['flag'] = 0;
        	$flagNumber = M("OrderBaoguo")->where($where)->count();//未发货总数


        	unset($map);
    		$map['id'] = $value['orderID'];
    		$map['payStatus'] = array('in',[2,3]);
        	if ($flagNumber==0 && $printNumber==0) {
        		M("Order")->where($map)->setField("payStatus",4);
        	}elseif($printNumber==0){
	        	M("Order")->where($map)->setField("payStatus",3);
        	}
		}
		$this->display();
	}

	#删除
	public function del() {
		$id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('Order');
            $list = $obj->where($map)->setField('del',1);
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
	}
}
?>