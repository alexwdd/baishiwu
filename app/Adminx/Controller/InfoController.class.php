<?php
namespace Adminx\Controller;

class InfoController extends AdminController {

	public function index() {
		$startDate  = I('startDate');
		$endDate  = I('endDate');
		$type  = I('type');
		$keyword  = I('keyword');
		$keyword1  = I('keyword1');

		if ($type!='') {
			$map['flag'] = $type;
		}

		if ($keyword!='') {
			$map['memberName|memberMobile'] = array('like','%'.$keyword.'%');
		}

		if ($keyword1!='') {
			$map['mobile|name|fadongji|chejia'] = array('like','%'.$keyword1.'%');
		}

		if($startDate!='' && $endDate!=''){
			$map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
		}

		$obj = M('Info');
		$count = $obj->where($map)->count();
		import("Common.ORG.Page");
		$page = new \Page($count, 50);
		$show = $page->show();
		$list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($list as $key => $value) {
            if ($value['flag']==1) {
                $list[$key]['prize'] = M('Coupon')->where(array('infoID'=>$value['id']))->find();
            }
        }
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	#编辑
	public function edit() {
		if($_POST){
			$this->all_save("Info",U('Info/index'));
		}else{
			$id = (int) $_GET['id'];
			if (!isset ($id) || !is_numeric($id)) {
				$this->error('参数错误');
			}
			$map['id'] = $id;
			$obj = M('Info');
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$coupon = M('Coupon')->where(array('infoID'=>$list['id']))->find();
				$this->assign('coupon', $coupon);
				$this->assign('list', $list);

				$chexing = M('OptionCate')->cache(true)->select();
            	$this->assign('chexing',$chexing);

            	unset($map);
				$map['name'] = $list['chexing'];
				$provinceID = M('OptionCate')->where($map)->getField('id');

				unset($map);

				$map['cate'] = $provinceID;
				$chexing1 = M('OptionItem')->where($map)->select();
				$this->assign('chexing1', $chexing1);


				$this->display();
			}
		}
	}

	public function getChexing(){
        if(IS_POST){
        	$pro = I('post.pro');
	        if ($pro=='') {
	            $state = 0;
	        }else{
	            $map['cate'] = $pro;
            	$list = M('OptionItem')->cache(true)->where($map)->order('sort asc')->select();
	        }
	        $josn_arr = array(
	                        'state' =>   $state,
	                        'data' => $list,
	                        ); 
	        echo json_encode($josn_arr);
        }
    }
	
	#删除
	public function del() {
		$this->all_del('Info','reload');
	}
}
?>