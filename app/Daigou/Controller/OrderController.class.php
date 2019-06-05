<?php
namespace Daigou\Controller;

class OrderController extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
			$status = I('post.status');
	        $account = I('post.account');
	        $order_no = I('post.order_no');
	        $createDate = I('post.createDate');
	        
	        if ($status!='') {
	            $map['payStatus'] = $status;
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
			$obj = M('Order');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);

                if ($value['card']>0) {
	                $list[$key]['pay'] = M("Card")->where('id='.$value['card'])->getField("name");
	            }else{
	                if ($value['payType']==1) {
	                    $list[$key]['pay'] = '支付宝';
	                }else{
	                    $list[$key]['pay'] = '微信支付';
	                }
	            }
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
			$obj = M('Order');
			$map['del'] = 0;
			$map['id'] = $id;
			$map['agentID'] = $this->user['id'];
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			}else{
				$list['baoguo'] = M('OrderBaoguo')->where(array('orderID'=>$list['id']))->select();
	            foreach ($list['baoguo'] as $key => $value) {
	                $goods = M('OrderDetail')->where(array('baoguoID'=>$value['id']))->select();
	                foreach ($goods as $k => $v) {
	                    if ($v['server']!='') {
	                        $serverID = explode(",",$v['server']);
	                        unset($map);
	                        $map['id'] = array('in',$serverID);
	                        $server = M("server")->where($map)->select();           
	                        $goods[$k]['serverArr'] = $server;
	                    }
	                }
	                $list['baoguo'][$key]['goods'] = $goods;
	                $wuliu = M('OrderWuliu')->where(array('baoguoID'=>$value['id']))->select();
	                foreach ($wuliu as $k => $v) {
	                	if ($v['image']!='') {
	                		$wuliu[$k]['image'] = explode(",", $v['image']);
	                	}
	                }
	                $list['baoguo'][$key]['wuliu'] = $wuliu;
	            }
				$this->assign('list', $list);
				$this->display();
			}
		}
	}

	public function addwuliu(){
		if (IS_POST) {
			$data = I('post.');
            $this->all_add("OrderWuliu",U('order/baoguo',['id'=>$data['orderID']]));
		}else{
			$id = I('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map['agentID'] = $this->user['id'];
			$wuliu = M('Wuliu')->where($map)->select();
			$this->assign('wuliu', $wuliu);
			$this->assign('id', $id);
			$this->assign('orderID', I('param.orderID'));
			$this->display();
		}
	}

	public function editwuliu(){
		if (IS_POST) {
			$data = I('post.');
            $this->all_save("OrderWuliu",U('order/baoguo',['id'=>$data['orderID']]));
		}else{
			$id = I('get.id');
			if ($id=='' || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$list = M("OrderWuliu")->where(array('id'=>$id))->find();

			$map['agentID'] = $this->user['id'];
			$wuliu = M('Wuliu')->where($map)->select();
			$this->assign('wuliu', $wuliu);

			if ($list['image']) {
                $image = explode(",", $list['image']);
            }else{
            	$image = [];
            }
			$this->assign('image', $image);
			$this->assign('list', $list);
			$this->assign('orderID', I('param.orderID'));
			$this->display();
		}
	}

	public function delwuliu(){
		$id = I('get.id');
        $map['id'] = $id;
        M('OrderWuliu')->where($map)->delete();
        header('Location:'.$_SERVER['HTTP_REFERER']);
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