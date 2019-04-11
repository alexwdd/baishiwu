<?php
namespace Adminx\Controller;

class TixianController extends AdminController {

	#列表
	public function index() {

		$username = I('username');

		if($username!=''){
			$map['memberID'] = $username;
		}		

		$startDate  = I('startDate');
		$endDate  = I('endDate');
		if($startDate!='' && $endDate!=''){
			$map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
		}

		$obj = M('Tixian');
		$count = $obj->where($map)->count();
		import("common.ORG.Page");
		$page = new \Page($count, 15);
		$show = $page->show();
		$list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('username', $username);
		$this->display();
	}

	public function txDo(){
		if ($_POST) {
			$txid=I("post.id");
			$bankID=I('post.bankID');
			if ($txid=='' && !is_numeric($txid)) {
				$this->error('参数错误');
			}

			/*if ($bankID=='' && !is_numeric($bankID)) {
				$this->error('参数错误');
			}*/

			$obj=M('Tixian');
			$txinfo=$obj->where(array('id'=>$txid))->find();
			if($txinfo){
				/*$bank = M('Bankcard')->where(array('id'=>$bankID,'memberID'=>$txinfo['memberID']))->find();
				if (!$bank) {
					$this->error("收款账户不存在");
				}else{
					switch ($bank['type']) {
						case '1':
							$bankInfo = '微信钱包，微信号：'.$bank['bank'].'，手机：'.$bank['mobile'];
							break;
						case '2':
							$bankInfo = '银行卡，开户行：'.$bank['bank'].'，姓名：'.$bank['name'].'，卡号：'.$bank['cardNo'];
							break;
						case '3':
							$bankInfo = '支付宝，账号：'.$bank['bank'].'，姓名：'.$bank['name'].'，手机：'.$bank['mobile'];
							break;
						default:
							$this->error("收款账户不存在");
							break;
					}
				}*/

				$obj->where(array('id'=>$txid))->save(array('status'=>1,'updateTime'=>time()));
				$this->success('已确认打款');
			}else{
				$this->error('提现错误');
			}
	
		}else{
			$txid=I("get.id");
			if($txid){
				$map['id'] = $txid;
				$map['status'] = 0;
				$obj=M('Tixian');
				$list=$obj->where($map)->find();
				if($list){
					//$bankcard = M('Bankcard')->where(array('memberID'=>$list['memberID']))->select();
        			//$this->assign('bankcard',$bankcard);

					$this->assign('list',$list);
					$this->display();
				}else{
					$this->error('信息不存在');
				}
			}else{
				$this->error('参数错误');
			}			
		}		
	}

	public function export(){
        $list = M('Tixian')->select();
        import("Common.ORG.PHPExcel");
        $objPHPExcel = new \PHPExcel();       
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '会员')                 
            ->setCellValue('B1', '提现金额')
            ->setCellValue('C1', '实际金额')
            ->setCellValue('D1', '收款账户')
            ->setCellValue('E1', '日期');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['username'])                
                ->setCellValue('B'.$num, $v['money'])
                ->setCellValue('C'.$num, $v['realMoney'])                 
                ->setCellValue('D'.$num, $v['bankInfo'])
                ->setCellValue('E'.$num, date("Y-m-d H:i:s",$v['createTime']));
        }

        $objPHPExcel->getActiveSheet()->setTitle('提现');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="提现.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }
}
?>