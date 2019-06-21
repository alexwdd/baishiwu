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

    public function getAueToken(){
        if (S("aueToken")) {
            return S("aueToken");
        }else{
            $url = 'http://auth.auexpress.com/api/token';
            $data = C('aue');
            $res = $this->https_post($url,$data,true);
            $res = json_decode($res,true);   
            if ($res['Token']) {
                $token = $res['Token'];
                S("aueToken",$token,7200);
                return $token;
            }else{
                return '';
            }           
        }
    }

    public function saveAuePng($orderNo){        
        $token = $this->getAueToken();
        $url = 'http://aueapi.auexpress.com/api/OrderLabelPrint?orderId='.$orderNo.'&printMode=1&fileType=0&fontSize=0';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
        $res = curl_exec($ch);
        if ($res=='') {
            return '';
        }else{
            $path = C('UPLOAD_PATH').'order/'.date("Ymd").'/'.$orderNo.'.png';
            $filename = '.'.$path;   // 文件保存路径
            $this->createDir(dirname($filename));
            $fp= @fopen($filename,"w"); 
            fwrite($fp,$res);
            return $path;
        }        
    }

    public function createSingleOrder($order){
        $goods = M("DgOrderDetail")->where(array('baoguoID'=>$order['id']))->select();
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

        $brandID = getBrandID($order['type']);
        $config = C("aue");
        $data = [
            'MemberId'=>$config['MemberId'],
            'BrandId'=>$brandID,
            'SenderName'=>$order['sender'],
            'SenderPhone'=>$order['senderMobile'],
            'ReceiverName'=>$order['name'],
            'ReceiverPhone'=>$order['mobile'],
            'ReceiverProvince'=>$order['province'],
            'ReceiverCity'=>$order['city'],
            'ReceiverAddr1'=>$order['area'].$order['address'],
            'ChargeWeight'=>0,
            'Value'=>0,
            'ShipmentContent'=>$content
        ];

        $note = '';
        if ($order['serverIds']) {
            $ids = explode(",",$order['serverIds']);
            $where['id'] = array('in',$ids);
            $server = M("Server")->field('id,short')->where($where)->select();
            foreach ($server as $k => $val) {
                if ($val['short']!="") {
                    if ($val['id']==2 && $order['sign']) {
                        $note .='['.$order['sign'].']';
                    }else{
                        $note .= '['.$val['short'].']';
                    }
                }                                   
            }
        }
        $data['Notes'] = $note;
        $token = $this->getAueToken();
        $url = 'http://aueapi.auexpress.com/api/AgentShipmentOrder/Create';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,'['.json_encode($data).']');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
        $result = curl_exec($ch);
        $result = json_decode($result,true);    

        if ($result['Message']=='Authentication failed, invalid token.') {
            Cache::rm('aueToken');  
        }

        if ($result['Code']==0 && $result['Message']!='' && $result['Message']!='Authentication failed, invalid' && $result['Message']!='Authentication failed, invalid token.') {
            $update = [
                'kdNo'=>$result['Message']
            ];
            M("DgOrderBaoguo")->where(array('id'=>$order['id']))->save($update);
            return ['code'=>1,'msg'=>$result['Message']];
        }else{
            return ['code'=>0,'msg'=>$result['Errors'][0]['Message']];
        }
    }

    public function createDir($path){ 
        if (!file_exists($path)){ 
            $this->createDir(dirname($path)); 
            mkdir($path, 0777); 
        } 
    }

    public function https_post($url,$data = null,$json = false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        if (!empty($data)) {
            if ($json && is_array($data)) {
                $data = json_encode($data);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);            
            if ($json) {//发送JSON数据
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);       
        return $output;
    } 

    public function https_post1111($url,$data = null){
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

    public function setHit($cityID){
        $map['cityID'] = $cityID;
        $res = M('CityVisit')->where($map)->find();
        if (!$res) {
            $data['cityID'] = $cityID;
            $data['hit'] = 1;
            M('CityVisit')->add($data);
        }else{
            M('CityVisit')->where($map)->setInc('hit');
        }
    }
}