<?php
namespace Daigou\Controller;
use Common\Controller\BaseController;

class CommonController extends BaseController {

	public $user;
    public $config;

	public function _initialize() {

		parent::_initialize();

		if (!session('flag')) {
			$this->redirect('Daigou/Login/index');
		}else{
			$crypt = new \Think\Crypt;
            $flag = $crypt->decrypt(session('flag'),C('DATA_CRYPT_KEY'));
            $flagArr = explode(',', $flag);
            if ($flagArr[1]!=get_client_ip(0,1)) {
            	$this->redirect('Daigou/Login/index');
            }
		}

		$user = M('Agent')->where(array('id'=>$flagArr[0],'status'=>1))->find();		
		if (!$user) {
			$this->redirect('Daigou/Login/index');
		}else{
			$this->user = $user;
            $this->assign('user',$this->user);
		}
        $this->assign('empty','<div class="empty"></div>');
	}    

	//品牌
    public function getBrand() {
        $map['agentID'] = $this->user['id'];
        $list = M('Brand')->field('id,name')->where($map)->select();
        if($list){
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>null
                    )
            ));
        }
    }

    //支付方式
    public function getPayType() {
        $map['agentID'] = $this->user['id'];
        $list = M('Card')->field('id,name')->where($map)->select();
        if($list){
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>null
                    )
            ));
        }
    }

    //包裹类型
    public function getBaoguo() {
    	$map['agentID'] = $this->user['id'];
        $list = M('Baoguo')->field('id,name')->where($map)->select();
        if($list){
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>null
                    )
            ));
        }
    }

    //商品模型
    public function getGoodsType() {
    	$map['agentID'] = $this->user['id'];
        $list = M('GoodsType')->field('id,name')->where($map)->select();
        if($list){
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>$list
                    )
            ));
        }else{
            echo json_encode(array(
                'status'=>1,
                'results'=>array(
                    'data'=>null
                    )
            ));
        }
    }

	public function return_json($results){
        return json_encode(array(
                'status'=>1,
                'results'=>$results
            ));
    }  
}