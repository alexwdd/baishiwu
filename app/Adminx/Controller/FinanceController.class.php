<?php
namespace Adminx\Controller;

class FinanceController extends AdminController {

	public function index() {
		if (IS_POST) {
			$keyword = I('keyword');
			$status  = I('status');
			$type  = I('type');
			$cityID  = I('cityID');
			$createDate  = I('createDate');

			if($status!=''){
				$map['status'] = $status;
			}

			if($type!=''){
				$map['type'] = $type;
			}

			if($cityID!=''){
				$map['cityID'] = $cityID;
			}

			if($keyword!=''){
				$map['msg'] = array('like','%'.$keyword.'%');
			}		

			if ($createDate!='') {
				$date = explode(" - ", $createDate);
				$startDate = $date[0];
				$endDate = $date[1];
				$map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
			}

			if (!$_SESSION['administrator']) {
				$map['cityID'] = $this->user['cityID'];
			}

			$obj = M('Finance');
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
            $obj = M('Finance');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
	}

	public function export(){
		$status  = I('status');
		$type  = I('type');
		$cityID  = I('cityID');
		$createDate  = I('createDate');

		if($status!=''){
			$map['status'] = $status;
		}

		if($type!=''){
			$map['type'] = $type;
		}

		if($cityID!=''){
			$map['cityID'] = $cityID;
		}

		if ($createDate!='') {
			$date = explode(" - ", $createDate);
			$startDate = $date[0];
			$endDate = $date[1];
			$map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
		}

		if (!$_SESSION['administrator']) {
			$map['cityID'] = $this->user['cityID'];
		}

		$obj = M('Finance');
        $list = $obj->where($map)->select();
        foreach ($list as $key => $value) {
        	if ($value['status']==0) {
        		$list[$key]['status'] = '未支付';
        	}else{
        		$list[$key]['status'] = '已支付';
        	}
        	$list[$key]['city'] = M('OptionItem')->where(array('id'=>$value['cityID']))->getField('name');
        	$cate = C('moneyType');
        	$list[$key]['typeName'] = $cate[$value['type']]['name'];
        }
        import("Common.ORG.PHPExcel");
        $objPHPExcel = new \PHPExcel();       
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '城市')                 
            ->setCellValue('B1', '类型')
            ->setCellValue('C1', '金额')
            ->setCellValue('D1', '分类')
            ->setCellValue('E1', '文章ID')
            ->setCellValue('F1', '凭证')
            ->setCellValue('G1', '备注')
            ->setCellValue('H1', '状态')
            ->setCellValue('I1', '日期');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['city'])                
                ->setCellValue('B'.$num, $v['typeName'])
                ->setCellValue('C'.$num, $v['money'])                 
                ->setCellValue('D'.$num, $v['extend3'])                 
                ->setCellValue('E'.$num, $v['extend1'])                 
                ->setCellValue('F'.$num, 'http://' . $_SERVER['HTTP_HOST'].$v['picname'])                 
                ->setCellValue('G'.$num, $v['msg'])                 
                ->setCellValue('H'.$num, $v['status'])
                ->setCellValue('I'.$num, date("Y-m-d H:i:s",$v['createTime']));
        }

        $objPHPExcel->getActiveSheet()->setTitle('财务明细');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="财务明细.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }
}
?>