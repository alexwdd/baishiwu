<?php
namespace V1\Controller;

class AutoController extends CommonController {

	public function index(){
		foreach (C('infoArr') as $key => $value) {
			unset($map);
			$obj = M($value['db']);
			$map['isTop'] = 1;
			$list = $obj->where($map)->select();
			foreach ($list as $key => $value) {
				if ($value['showTime'] < time()) {
					$obj->where(array('articleid'=>$value['articleid']))->setField('isTop',0);
				}
			}
		}
    }

    //创建运单
	public function createOrder(){
		/*$content = date('Y-m-d H:i:s')." 创建运单\r\n";
        $file = './log/auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = '';
		//$map['status'] = 1;
		$map['kuaidi'] = array('neq','');
		$map['type'] = array('not in',[12,13,14]);
		$list = M("DgOrderBaoguo")->where($map)->select();
		foreach ($list as $key => $value) {
			$this->createSingleOrder($value);
		}
	}

	//创建电子单
	public function createOrderPng(){
		/*$content = date('Y-m-d H:i:s')." 创建运单图片\r\n";
        $file = './log/auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = array('neq','');
		$map['type'] = array('not in',[12,13,14]);
		$map['eimg'] = array('eq','');
		//$map['status'] = 1;
		$list = M("DgOrderBaoguo")->where($map)->select();
		foreach ($list as $key => $value) {
			$eimg = $this->saveAuePng($value['kdNo']);	
			if ($eimg!='') {
				$update = [
					'eimg'=>$eimg
				];
				M("DgOrderBaoguo")->where(array('id'=>$value['id']))->save($update);
			}
		}
	}

	//上传身份证
	public function uploadPersonPhoto(){
		/*$content = date('Y-m-d H:i:s')." 上传身份证\r\n";
        $file = './log/auto.log';
        file_put_contents($file, $content,FILE_APPEND);*/

		$map['kdNo'] = array('neq','');
		$map['type'] = array('not in',[12,13,14]);
		//$map['status'] = 1;
		$map['snStatus'] = 0;
		$list = M("DgOrderBaoguo")->where($map)->select();		
		$token = $this->getAueToken();
		foreach ($list as $key => $value) {
			$order = M('DgOrderPerson')->where(array('id'=>$value['personID']))->find();
			if ($order['front']!='' && $order['back']!='') {
				$data = [
					'OrderIds'=>[$value['kdNo']],
					'ReceiverName'=>$order['name'],
					'ReceiverPhone'=>$order['mobile'],
					'PhotoID'=>$order['sn'],
					'PhotoFront'=>base64EncodeImage('.'.$order['front']),
					'PhotoRear'=>base64EncodeImage('.'.$order['back'])
				];		
				$url = 'http://aueapi.auexpress.com/api/PhotoIdUpload';
				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$token));
				$result = curl_exec($ch);
				$result = json_decode($result,true);
				if ($result['Code']==0 && $result['ReturnResult']=='Success') {
					M("DgOrderBaoguo")->where($map)->setField('snStatus',1);
				}
			}
		}
	}
}