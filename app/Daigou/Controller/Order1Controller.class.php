<?php
namespace Daigou\Controller;

class Order1Controller extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
	        $account = I('post.account');
	        $payType = I('post.payType');
	        $order_no = I('post.order_no');
	        $createDate = I('post.createDate');
	        

	        $map['payStatus'] = 1;

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

	#删除
    public function confirm() {
        $id = explode(",",I('post.id'));
        if (count($id)==0) {
            $this->error('请选择要审核的订单');
        }else{
        	$map['id'] = array('in',$id);
        	$res = M('DgOrder')->where($map)->setField('payStatus',2);
            if($res){
                unset($map);
                $map['orderID'] = array('in',$id);
                M("DgOrderBaoguo")->where($map)->setField('status',1);
                $detail = M("DgOrderDetail")->where($map)->select();
                foreach ($detail as $key => $value) {
                    M("DgGoods")->where(array('id'=>$value['goodsID']))->setDec("stock",$value['trueNumber']);
                }
                $this->success("操作成功");
            }else{
                $this->error('操作失败');
            }
        }
    }


	#删除
	public function del() {
		$id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $map['agentID'] = $this->user['id'];
            $obj = M('DgOrder');
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