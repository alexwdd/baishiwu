<?php
namespace Common\Controller;
use Think\Controller;

class BaseController extends Controller{

    public function _initialize(){
        if (C('DEFAULT_THEME')) {
            $THEME_PATH = '/tpl/'.MODULE_NAME.'/'.C('DEFAULT_THEME').'/';
        }else{
            $THEME_PATH = '/tpl/'.MODULE_NAME.'/';
        }
        
        define('RES', $THEME_PATH . 'common');
        define('STATICS', '/tpl/' . 'static');

        $config = tpCache('basic');
        C('site',$config);
    }

    //返回json数据
    public function echo_json_str($state , $message , $url=''){
		if(empty($url)){
			$url = '';	
		}
		$josn_arr = array(
                        'state' =>   $state,
                        'message' => $message,
                        'url' => $url,
                        ); 
        return json_encode($josn_arr);
        die;
	}

    public function return_json($results){
        return json_encode(array(
                'status'=>1,
                'results'=>$results
            ));
    }

	//验证码显示
	public function verify() {
        ob_clean();
        $Verify = new \Think\Verify();     
        $Verify->length   = 4; 
        $Verify->fontSize = 100;
        $Verify->useCurve = false;//关闭干扰线
        $Verify->entry();
	}

    //验证验证码
    function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

	//公用插入数据
	protected function all_add($name = '', $back = '/index'){
        $name = $name ? $name : MODULE_NAME;
        $obj = D($name);
        if ($data = $obj->create()) {
            if ($list = $obj->add($data)) {
                $this->success('操作成功',$back);
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->error($obj->getError());
        }
    }

    //公用更新数据
    protected function all_save($name = '', $back = '/index'){        
        $name = $name ? $name : MODULE_NAME;
        $obj = D($name);
        if ($data = $obj->create()) {
            if ($list = $obj->save($data)) {                
                $this->success('操作成功',$back);
            } else {
                $this->error('操作失败');
            }            
        } else {
            $this->error($obj->getError());
        }
    }  

    //公用删除数据
    protected function all_del($name = '', $id , $back = '/index'){        
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $obj = M($name);
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功',$back);
            }else{
                $this->error('操作失败');
            }
        }
    }  

    public function https_post($url,$data = null){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if (!$empty) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);       
        return $output;
    }

    public function getCity(){
        $pro = I('post.pro');
        if ($pro=='') {
            $state = 0;
        }else{
            $map['fatherID'] = $pro;
            $list = M('City')->where($map)->select();
        }
        $josn_arr = array(
                        'state' =>   $state,
                        'data' => $list,
                        ); 
        echo json_encode($josn_arr);
    }

    public function getArea(){
        $city = I('post.city');
        if ($city=='') {
            $state = 0;
        }else{
            $map['fatherID'] = $city;
            $list = M('Area')->where($map)->select();
        }

        $josn_arr = array(
                        'state' =>   $state,
                        'data' => $list,
                        ); 

        echo json_encode($josn_arr);
    }
}