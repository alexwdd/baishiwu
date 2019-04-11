<?php
namespace Adminx\Controller;

class MemberController extends AdminController {

	#列表
	public function index() {
        if (IS_POST) {
            $disable  = I('disable');
            $authentication  = I('authentication');
            $oauth  = I('oauth');
            $keyword  = I('keyword');            

            if($keyword!=''){
                $map['id|phone|email|nickname|name'] = array('like','%'.$keyword.'%');
            }   

            if($disable!=''){
                $map['disable'] = $disable;
            }
            if($authentication!=''){
                $map['authentication'] = $authentication;
            }
            if($oauth!=''){
                $map['oauth'] = $oauth;
            }

            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            
            $obj = M('Member');
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

	public function status(){
		if (!IS_POST) {E('页面不存在！');}
    	$id = I('post.id');
    	if (empty($id)) {
        	$this->error('ID不能为空！');
    	}
      	$user = M('Member');
		$map['id'] = $id;
    	$rs=$user->where($map)->find();
    	if(!$rs){
        	$this->error('该用户不存在！');
    	}

      	$isstop=$rs['disable']==0?1:0;	
    	$rs = $user->where(array('id'=>$id))->save(array('disable'=>$isstop));

    	if ($rs) {        
    		$this->success('状态更新成功');
    	}
	}
	#添加
	public function add() {
		if($_POST){
			if (!checkRequest()) {$this->error('未知错误');}

	        $user = D('Member');
	        if (!($data = $user->create())) {
	            $this->error($user->getError());
	        }

	        $data['loginTime'] = time();
	        $data['loginIP'] = get_client_ip();
	        $data['disable'] = 0;          
            $list = $user->add($data);
	        if ($list) {
	            $this->success('经销商注册成功！', U('Member/index'));
	        }else{
	            $this->error('经销商注册失败！');
	        }
        }else{
        	$province = M("Province")->where(array('show'=>1))->select();
			$this->assign('province',$province);
			//$city = M("City")->where('fatherID=410000')->select();
			//$this->assign('city',$city);
        	$this->display();
        }
	}

	#编辑
	public function edit() {
		if($_POST){
			if (!checkRequest()) {
	            $this->error('未知错误');
	        }

			$password = trim(I('post.password'));
			$disable = trim(I('post.disable'));
			$cityID = trim(I('post.cityID'));
			$authentication = trim(I('post.authentication'));
			$id = trim(I('post.id'));
    		if (empty($id)) {
        		$this->error('ID不能为空！');
      		}

    		$data=array();
			if (!empty($password)) {
				$data['password']=md5($password);
			}
            $data['disable'] = $disable;
            $data['cityID'] = $cityID;
            $data['authentication'] = $authentication;      	
			$map['id'] = $id;
	    	$rs = M('Member')->where($map)->save($data);
	    	if ($rs) {
	        	$this->success('修改成功！',U('Member/index'));
	      	}else{		  
				$this->error('修改失败，或者没有做任何改动'); 
			}
	    }else{
	    	$id = I('get.id');
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}		
			$map['id'] = $id;
			$list = M('Member')->where($map)->find();

			if (!$list) {
				$this->error('信息不存在');
			} else {
                $city = M('OptionItem')->field('id,name')->where("cate=1")->select();;
                $this->assign('city',$city);
				$this->assign('list', $list);
				$this->display();
			}
	    }		
	}

	#删除
	public function del() {
		//$this->error('经销商不允许删除，您可以选择封号');
		$id=I('post.selectedids');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
        	if (!$_SESSION['administrator']) {
				$map['teamID'] = $this->admin['teamID'];
			}
            $map['id'] = array('in',$id);
            $obj = M('Member');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功','reload');
            }else{
                $this->error('操作失败');
            }
        }
	}

	public function import(){
        if (IS_POST) {
            set_time_limit(0);
            ini_set("memory_limit", "512M"); 
            //上传文件
            $file = $this->_upload();
            import("Common.ORG.PHPExcel");

            $objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);
            $sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumm = $sheet->getHighestColumn(); // 取得总列数

            //$highestColumm= PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27
            $obj = M('Member');
            $total = 0;
            for ( $i = 2; $i <= $highestRow; $i++) {  
                $data['mobile'] = trim($sheet->getCellByColumnAndRow(0, $i)->getValue());
                $data['company'] = trim($sheet->getCellByColumnAndRow(1, $i)->getValue());
                $data['name'] = trim($sheet->getCellByColumnAndRow(2, $i)->getValue());
                $data['province'] = trim($sheet->getCellByColumnAndRow(3, $i)->getValue());
                $data['city'] = trim($sheet->getCellByColumnAndRow(4, $i)->getValue());
                $data['address'] = trim($sheet->getCellByColumnAndRow(5, $i)->getValue());
                $data['weixin'] = trim($sheet->getCellByColumnAndRow(6, $i)->getValue());
                $data['qq'] = trim($sheet->getCellByColumnAndRow(7, $i)->getValue());        
                $data['disable'] = 0;    
                $data['password'] = md5('123456');        
                $data['createTime'] = time();
                $data['createIP'] = get_client_ip();
                if ($data['name']!='' && $data['company']!='' && $data['mobile']!='') {
                    $list = $obj->add($data);
                    if ($list) {                          
                        $total++; 
                    }
                }                
            }
            
            $msg = '共'.($highestRow-1).'条数据，成功导入'.$total.'条';
            $this->success($msg);
        }else{
            $this->display();
        }
    }

    #文件上传
    private function _upload(){
        $path = './uploads/';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 10000000;  //是指上传文件的大小，默认为-1,不限制上传文件大小bytes
        $upload->rootPath = $path;        //上传保存到什么地方？路径建议大家已主文件平级目录或者平级目录的子目录来保存
        $upload->saveRule=uniqid;    //上传文件的文件名保存规则  time uniqid  com_create_guid  uniqid
        $upload->autoSub = true;
        $upload->replace=true;     //如果存在同名文件是否进行覆盖
        $upload->exts= array('xls','xlsx');     //准许上传的文件后缀
        //$upload->allowTypes= array('application/vnd.ms-excel'); //检测mime类型
        $info = $upload->upload();
        if($info){
            foreach($info as $file){
                $filepath = $path.$file['savepath'];
                $url = $path.$file['savepath'].$file['savename'];
            }
            return $url;          
        }else{
            //是专门来获取上传的错误信息的            
            $this->error($upload->getError());
        }
    }

	public function export(){
        $list = M('Member')->select();
        import("Common.ORG.PHPExcel");
        $objPHPExcel = new \PHPExcel();       
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '手机号码')                 
            ->setCellValue('B1', '公司名称')
            ->setCellValue('C1', '联系人')
            ->setCellValue('D1', '省份')
            ->setCellValue('E1', '城市')
            ->setCellValue('F1', '地址')
            ->setCellValue('G1', '微信号')
            ->setCellValue('H1', 'QQ');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['mobile'])                
                ->setCellValue('B'.$num, $v['company'])
                ->setCellValue('C'.$num, $v['name'])                 
                ->setCellValue('D'.$num, $v['province'])                 
                ->setCellValue('E'.$num, $v['city'])                 
                ->setCellValue('F'.$num, $v['address'])                 
                ->setCellValue('G'.$num, $v['weixin'])
                ->setCellValue('H'.$num, $v['qq']);
        }

        $objPHPExcel->getActiveSheet()->setTitle('经销商');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="member.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }
	
}
?>