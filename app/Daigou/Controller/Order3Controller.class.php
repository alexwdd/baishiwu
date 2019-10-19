<?php
namespace Daigou\Controller;

class Order3Controller extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
	        $account = I('post.account');
	        $payType = I('post.payType');
	        $order_no = I('post.order_no');
	        $createDate = I('post.createDate');
	        

	        $map['payStatus'] = array('in',[2,3]);

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
                $list[$key]['baoguoNumber'] = M('DgOrderBaoguo')->where('orderID='.$value['id'])->count();
                $list[$key]['lirun'] = $value['total']-$value['inprice']-$value['wuliuInprice'];
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
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

    public function export(){
        $type = I('get.type');
        $flag = I('get.flag');
        $createDate = I('get.date');
        $ids = I('get.ids');
        if ($flag!='') {
            $map['flag'] = $flag;
        }
        if ($type!='') {
            $map['type'] = $type;
        }
        if ($ids!='') {
            $map['id'] = array('in',$ids);
        }
        if ($createDate!='') {
            $date = explode(" - ", $createDate);
            $startDate = $date[0];
            $endDate = $date[1];
            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
        }
        $map['agentID'] = $this->user['id'];
        $list = M('DgOrder')->where($map)->order('id desc')->select();
        foreach ($list as $key => $value) {
            $baoguo = M("DgOrderBaoguo")->where(array('orderID'=>$value['id']))->select();
            $kdNo = '';
            foreach ($baoguo as $k => $val) {
                if($kdNo==''){
                    $kdNo = $val['kdNo'];
                }else{
                    $kdNo .= ",".$val['kdNo'];
                }
            }            
        }
        import("Common.ORG.PHPExcel");
        $objPHPExcel = new \PHPExcel();    
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '编号')
            ->setCellValue('B1', '订单号')
            ->setCellValue('C1', '快递号')
            ->setCellValue('D1', '姓名')
            ->setCellValue('E1', '电话')
            ->setCellValue('F1', '地址')
            ->setCellValue('G1', '发件人')
            ->setCellValue('H1', '商品金额')
            ->setCellValue('I1', '运费')
            ->setCellValue('J1', '服务费')
            ->setCellValue('K1', '应支付')
            ->setCellValue('L1', '查看地址');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['id'])                
                ->setCellValue('B'.$num, $v['order_no'])                
                ->setCellValue('C'.$num, $kdNo)
                ->setCellValue('D'.$num, $baoguo[0]['name'])                 
                ->setCellValue('E'.$num, $baoguo[0]['mobile'])
                ->setCellValue('F'.$num, $baoguo[0]['province'].'/'.$baoguo[0]['city'].'/'.$baoguo[0]['area'].'/'.$baoguo[0]['address'])          
                ->setCellValue('G'.$num, $baoguo[0]['sender'].'/'.$baoguo[0]['senderMobile'])
                ->setCellValue('H'.$num, $v['inprice'])
                ->setCellValue('I'.$num, $v['payment'])
                ->setCellValue('J'.$num, $v['serverMoney'])
                ->setCellValue('K'.$num, $v['inprice']+$v['payment']+$v['serverMoney'])
                ->setCellValue('L'.$num, 'http://www.worldmedia.top/Daigou/ags/index/order_no/'.$v['order_no']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('订单');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="订单'.date("Y-m-d",time()).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }   
}
?>