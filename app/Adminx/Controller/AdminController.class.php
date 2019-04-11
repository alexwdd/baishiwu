<?php 
namespace Adminx\Controller;
use Common\Controller\BaseController;

class AdminController extends BaseController {

	public $cateArray;
	public $admin;

	public function _initialize(){

    	parent::_initialize();
    	// 用户权限检查
		if (C ( 'USER_AUTH_ON' ) && !in_array(MODULE_NAME,explode(',',C('NOT_AUTH_MODULE')))) {
			$rbac = \Org\Util\Rbac::AccessDecision();			
			if (!$rbac) {	
				//检查认证识别号
				if (! $_SESSION [C ( 'USER_AUTH_KEY' )]) {
					//跳转到认证网关
					redirect ( C ( 'USER_AUTH_GATEWAY' ) );
				}
				// 没有权限 抛出错误
				if (C ( 'RBAC_ERROR_PAGE' )) {
					// 定义权限错误页面
					redirect ( C ( 'RBAC_ERROR_PAGE' ) );
				} else {
					if (C ( 'GUEST_AUTH_ON' )) {
						$this->assign ( 'jumpUrl', C ( 'USER_AUTH_GATEWAY' ) );
					}
					// 提示错误信息
					$this->error ( L ( '_VALID_ACCESS_' ) );
				}
			}
		}

		$this->user = M('User')->where(array('id'=>session('adminID')))->find();
		$this->assign('admin',$this->user); 

		//数据缓存
        if (S('C_DATA')) {
            $this->assign('c_data',S('C_DATA'));
        }else{
            $arr = array();
            $data = M('OptionCate')->select();
            foreach ($data as $key => $value) {
                $arr[$value['value']] = M("OptionItem")->field('id,name,value')->where(array('cate'=>$value['id']))->order('sort asc,id asc')->select();
            }
            S('C_DATA',$arr);
        }   
        $this->assign('c_data',S('C_DATA'),2592000);

		//内容分类的权限
		$map['user'] = array('like','%'.$this->user['username'].'%');
		$list = M('Category')->where($map)->getField('id',true);
		$this->cateArray = M('Category')->where($map)->getField('id',true);	
		if(!$this->cateArray) {
			$this->cateArray = '';
		}		
    }

    //用户分组
	public function getGroup() {
		$list = M('Role')->field('id,name')->select();
		if($list){
			echo json_encode(array(
	            'status'=>1,
	            'results'=>array(
	                'data'=>$list
	                )
	        ));
		}else{
			$this->error("信息不存在");
		}
	}

	public function getOptionItem(){
		$cate = I('get.cate');
		$map['cate'] = $cate;
		$list = M('OptionItem')->field('id,name')->where($map)->select();
		if($list){
			echo json_encode(array(
	            'status'=>1,
	            'results'=>array(
	                'data'=>$list
	                )
	        ));
		}else{
			$this->error("信息不存在");
		}
	}
	
  	public function getPhoneType(){
		$list = array();
		foreach (C('phoneType') as $key => $value) {
			array_push($list, array('id'=>$value['id'],'name'=>$value['name']));
		}
		echo json_encode(array(
            'status'=>1,
            'results'=>array(
                'data'=>$list
                )
        ));	
	}

	public function getPhoneCate(){
		$list = array();
		foreach (C('phoneCate') as $key => $value) {
			array_push($list, array('id'=>$value['id'],'name'=>$value['name']));
		}
		echo json_encode(array(
            'status'=>1,
            'results'=>array(
                'data'=>$list
                )
        ));	
	}
  
	public function getFinance(){
		$list = array();
		foreach (C('moneyType') as $key => $value) {
			array_push($list, array('id'=>$key,'name'=>$value['name']));
		}
		echo json_encode(array(
            'status'=>1,
            'results'=>array(
                'data'=>$list
                )
        ));	
	}

	//图片生成缩略图
	public function getThumb($path, $width, $height) {
	    if(file_exists(".".$path) && !empty($path)){
	        $fileArr = pathinfo($path); 
	        $dirname = $fileArr['dirname']; 
	        $filename = $fileArr['filename']; 
	        $extension = $fileArr['extension']; 

	      if ($width > 0 && $height > 0) { 
	          $image_thumb =$dirname . "/" . $filename . "_" . $width . "_" .$height. "." . $extension; 
	          if (!file_exists(".".$image_thumb)) { 
	              $image = new \Think\Image(); 
	              $image->open(".".$path); 
	              $image->thumb($width, $height,3)->save(".".$image_thumb);
	          } 
	          $image_rs = $image_thumb; 
	      } else { 
	          $image_rs = $path; 
	      } 
	      return $image_rs;
	    }else{
	      return false;
	    } 
	}
}
?>