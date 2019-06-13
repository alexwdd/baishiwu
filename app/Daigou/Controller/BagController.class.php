<?php
namespace Daigou\Controller;

class BagController extends CommonController {
	#列表
	public function index() {
		if (IS_POST) {
	        $field = I('post.field','id');
	        $order = I('post.order','desc');
	        $type = I('post.type');
	        $flag = I('post.flag');
	        $print = I('post.print');
	        $keyword = I('post.keyword');
	        $order_no = I('post.order_no');
	        $createDate = I('post.createDate');
	        $sign = I('post.sign');
	        $image = I('post.image');
	        $pageSize = I('post.pageSize',20);
	        
	        if ($flag!='') {
	            $map['flag'] = $flag;
	        }
	        if ($print!='') {
	            $map['print'] = $print;
	        }
	        if ($type!='') {
	            $map['type'] = $type;
	        }
	        if ($keyword!='') {
	            $map['memberID|name|mobile'] = $keyword;
	        }
	        if ($order_no!='') {
	            $map['order_no|kdNo'] = $order_no;
	        }
	        if ($sign==1) {
	            $map['sign'] = array('neq','');
	        }
	        if ($image==1) {
	            $sql = "SELECT `id` FROM `pm_dg_order_baoguo` WHERE (`sign` <> '' AND `type` IN (1,2,3) AND ( `image` = '' )) OR (`image` = '' and `type` NOT IN (1,2,3))";
	            $idArr = M()->query($sql);
	            if ($idArr) {
	                $ids = [];
	                foreach ($idArr as $key => $value) {
	                    array_push($ids,$value['id']);
	                }
	                $map['id'] = array('in',$ids);
	            }else{
	                $map['id'] = 0;
	            }
	        }
	        if ($createDate!='') {
	            $date = explode(" - ", $createDate);
	            $startDate = $date[0];
	            $endDate = $date[1];
	            $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
	        }
	        $map['del'] = 0;
	        $map['status'] = 1;
	        $map['cancel'] = 0;
			$map['agentID'] = $this->user['id'];
			$obj = M('DgOrderBaoguo');
            $total = $obj->where($map)->count();
            
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
			$this->assign('type',C('baoguoType'));
			$this->display();
		}
	}

	#编辑
    public function image() {
        $id = I('get.id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $list = M('DgOrderBaoguo')->where(array('id'=>$id))->find();
        if (!$list) {
            $this->error('信息不存在');
        } else {
            $list['image'] = explode(",",$list['image']);
            $this->assign('list', $list); 
            $this->display();
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
        $map['del'] = 0;
        $map['status'] = 1;
        $map['agentID'] = $this->user['id'];
        $list = M('DgOrderBaoguo')->where($map)->order('id desc')->select();
        foreach ($list as $key => $value) {
        	M("DgOrderBaoguo")->where(array('id'=>$value['id']))->setField('flag',1);
        	$goods = M("DgOrderDetail")->where(array('baoguoID'=>$value['id']))->select();
			$content = '';
			foreach ($goods as $k => $val) {
				if ($val['extends']!='') {
					$goodsName = $val['short'].'['.$val['extends'].']';
				}else{
					$goodsName = $val['short'];
				}	
				if ($k==0) {
					$content .= $goodsName.'*'.$val['trueNumber'];
				}else{
					$content .= ";".$goodsName.'*'.$val['trueNumber'];
				}				
			}		
			$list[$key]['goods'] = $content;
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
            ->setCellValue('G1', '快递')
            ->setCellValue('H1', '商品')
            ->setCellValue('I1', '发件人');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['id'])                
                ->setCellValue('B'.$num, $v['order_no'])                
                ->setCellValue('C'.$num, $v['kdNo'])
                ->setCellValue('D'.$num, $v['name'])                 
                ->setCellValue('E'.$num, $v['mobile'])
                ->setCellValue('F'.$num, $v['province'].'/'.$v['city'].'/'.$v['area'].'/'.$v['address'])
                ->setCellValue('G'.$num, $v['kuaidi'])
                ->setCellValue('H'.$num, $v['goods'])
                ->setCellValue('I'.$num, $v['sender'].'/'.$v['senderMobile']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('包裹');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="包裹'.date("Y-m-d",time()).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }	

    public function import(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            
            $file = I('post.file');
            import("Common.ORG.PHPExcel");
            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load('.'.$file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('DgOrderBaoguo');
            $total = 0;
            $error = '';
            for ( $i = 2; $i <= $highestRow; $i++) {
                $orderID = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());              
                $kdNo = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                $data['kdNo'] = str_replace("，",",",$kdNo);
                $obj->where('id',$orderID)->update($data);
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条，错误信息'.$error;
            return info($msg,1);
        }else{
            $this->display();
        }
    }	

    public function upload(){
        $path = '.'.C('UPLOAD_PATH');
        if(!is_dir($path)){
            mkdir($path);
        }

        $upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = C('image_size')*1024*1024;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
		$upload->rootPath = $path;
		$upload->autoSub = true;
		$upload->replace=true;     //如果存在同名文件是否进行覆盖
		$upload->exts= explode(',',C('image_exts'));     //准许上传的文件后缀
		$info = $upload->upload();	
		if($info){			
			foreach($info as $file){
				if (empty($dir)) {
					$filepath = C('UPLOAD_PATH').$file['savepath'];
					$fname = C('UPLOAD_PATH').$file['savepath'].$file['savename'];
				}else{
					$filepath = C('UPLOAD_PATH').$dir.'/'.$file['savepath'];
					$fname = C('UPLOAD_PATH').$dir.'/'.$file['savepath'].$file['savename'];
				}
				$fileName = $file['name'];
				$fileName = explode(".",$fileName);
            	$fileName = $fileName[0]; //文件原名
			}
			//缩放处理
			// if (C('IMAGE_MAX_WIDTH')) {
			// 	$image = new \Think\Image();
			// 	$image->open('.'.$url);
			// 	$image->thumb(C('IMAGE_MAX_WIDTH'), C('IMAGE_MAX_WIDTH'))->save('.'.$filepath.$file['savename']);
			// }
			$map['kdNo'] = strtoupper(trim($fileName));
            $map['agentID'] = $this->user['id'];
            $list = M("DgOrderBaoguo")->where($map)->find();
            if ($list) {
                if ($list['image']=='') {
                    $data['image'] = $fname;
                }else{
                    $data['image'] = $list['image'].','.$fname;
                }
                $data['flag'] = 1;
                $res = M("DgOrderBaoguo")->where($map)->save($data);
                if ($res) {
                    $orderID = M("DgOrderBaoguo")->where($map)->getField('orderID');
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
            echo json_encode(array('status'=>1,'info'=>$fname));
		}else{
			//是专门来获取上传的错误信息的	
			echo json_encode(array('status'=>0,'info'=>$upload->getError()));
		}        
    }
}
?>