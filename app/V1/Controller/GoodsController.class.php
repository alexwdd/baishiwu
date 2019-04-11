<?php
namespace V1\Controller;

class GoodsController extends CommonController {

	public function publish(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');

			$user = $this->userCheck($userid,$password,$openid);

	       	$obj = D('TuanDetail');
        	if ($data = $obj->create()) {
        		$tuan = M('Tuan')->where(array('articleid'=>$data['articleid']))->find();
        		if (!$tuan) {
        			returnJson('-1','拼团信息不存在');
        		}

        		if ($tuan['status']>1) {
        			returnJson('-1','拼团已截至不允许添加');
        		}

        		//拼团重量查询
        		/*$localWeight = $obj->where(array('articleid'=>$data['articleid']))->sum('weight');
        		$localWeight = $localWeight?$localWeight:0;
        		if (($localWeight+$data['weight']) >= $tuan['maxWeight']) {
        			//returnJson('-1','超出拼团总重量');
        			M('Tuan')->where(array('articleid'=>$data['articleid'],'status'=>0))->setField("status",1);
        		}*/

				$data['cityID'] = $tuan['cityID'];
				$data['createID'] = $tuan['userid'];
				$data['tuan_order_no'] = $tuan['order_no'];
	        	$data['userid'] = $userid;
	        	$data['headimg'] = $user['headimg'];
				$data['status'] = 0;
				$data['title'] = '会员【'.$user['nickname'].'】加入拼邮，包裹重量'.$data['weight'].'公斤';
	            if ($list = $obj->add($data)) {
	            	M('Tuan')->where(array('articleid'=>$data['articleid']))->setField("status",0);
	            	returnJson('0',C("SUCCESS_RETURN"),array('goodsid'=>$list));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }			
		}
    }

    //获取包裹信息
	public function getinfo(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$goodsid = I('post.goodsid');

			if ($goodsid=='' || !is_numeric($goodsid)) {
	    		returnJson('-1','缺少goodsid');
	    	}
        	
        	$map['goodsid'] = $goodsid; 
        	$obj = M('TuanDetail'); 
        	$list = $obj->field('goodsid,userid,articleid,title,order,weight,weight_a,company,contact,phone,wechat,detail,status,createTime as time')->where($map)->find();
        	if ($list){
        		$list['pinyou'] = M('Tuan')->field('articleid,title,type,goodstype,status,createTime as time')->where(array('articleid'=>$list['articleid']))->find();
        		return returnJson('0',C('SUCCESS_RETURN'),$list);
        	}else{
        		returnJson('-1','不存在的信息');
        	}
		}
	}

	//编辑
	public function edit(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');
			$goodsid = I('post.goodsid');

			if ($goodsid=='' || !is_numeric($goodsid)) {
	    		returnJson('-1','缺少goodsid');
	    	}

			$user = $this->userCheck($userid,$password,$openid);

        	$obj = D('TuanDetail');
        	$r = $obj->where(array('goodsid'=>$goodsid,'userid'=>$userid))->find();
        	if (!$r) {
        		returnJson('-1','信息不存在');
        	}
        	if ($data = $obj->create()) {
        		$data['goodsid'] = $goodsid;

        		$tuan = M('Tuan')->where(array('articleid'=>$r['articleid']))->find();
        		if (!$tuan) {
        			returnJson('-1','拼团信息不存在');
        		}

        		if ($tuan['status']>1) {
        			returnJson('-1','拼团已截至不允许编辑');
        		}        		

	            if ($list = $obj->where(array('goodsid'=>$goodsid,'userid'=>$userid))->save($data)) {
	            	$this->setTuanStatus($tuan,$data);	            	
	            	returnJson('0',C("SUCCESS_RETURN"),array('goodsid'=>$data['goodsid']));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }		
		}
	}

	/*
	获取包裹列表
	如果带上userid则获取用户包裹列表    
	如果带上articleid则获取拼邮帖子相关列表
	*/
	public function getlist(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$articleid = I('post.articleid');			
			$status = I('post.status');			
			$page = I('post.page',1);

        	if ($status!='' && is_numeric($status)) {
        		$map['status'] = $status;
        	}

        	if ($articleid!='' && is_numeric($articleid)) {
        		$map['articleid'] = $articleid;
        	}

        	if ($userid!='' && is_numeric($userid)) {
        		$map['userid'] = $userid;
        	}

        	$pagesize = 10;
        	$firstRow = $pagesize*($page-1);         

        	$obj = M('TuanDetail');
        	$count = $obj->where($map)->count();
        	$totalPage = ceil($count/$pagesize);
        	if ($page < $totalPage) {
        		$next = 1;
        	}else{
        		$next = 0;
        	}

        	$list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('goodsid desc')->select();
        	returnJson('0',C("SUCCESS_RETURN"),array('articles'=>$list,'next'=>$next));
		}
	}

	//删除
	public function delete(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');
			$goodsid = I('post.goodsid');

			$user = $this->userCheck($userid,$password,$openid);

			$map['userid'] = $userid;
			$map['goodsid'] = $goodsid;
	       	$obj = M('TuanDetail');
	       	$list = $obj->where($map)->select();	       	
	       	if ($list) {
	       		$tuan = M('Tuan')->where(array('articleid'=>$list['articleid']))->find();
	       		if ($tuan['status']>1) {
        			returnJson('-1','拼团已截至不允许删除');
        		}
	       		$r = $obj->where($map)->delete();
	       		if ($r) {
	       			if ($tuan['status']==1) {
	       				$notComeNumber =  M('TuanDetail')->where(array('articleid'=>$list['articleid'],'status'=>0))->count();
						if ($notComeNumber==0) {//都已入库
			        		//拼团总重量是否达到
			        		$localWeight = M('TuanDetail')->where(array('articleid'=>$list['articleid']))->sum('weight_a');
			        		$localWeight = $localWeight?$localWeight:0;
			        		if ($localWeight < $tuan['maxWeight']) {
			        			M('Tuan')->where(array('articleid'=>$list['articleid']))->setField("status",0);
			        		}
			        	}
	       			}	       				
	       			returnJson('0',C("SUCCESS_RETURN"));
	       		}else{
	       			returnJson('-1','操作失败');
	       		}	       		
	       	}else{
	       		returnJson('-1','包裹不存在');
	       	}		
		}
	}

	//$tuan团信息 $goods包裹信息
	public function setTuanStatus($tuan,$goods){
		if ($goods['status']==0) {
			M('Tuan')->where(array('articleid'=>$tuan['articleid']))->setField("status",0);
		}else{
			$notComeNumber =  M('TuanDetail')->where(array('articleid'=>$tuan['articleid'],'status'=>0))->count();
			if ($notComeNumber==0) {//都已入库
        		//拼团总重量是否达到
        		$localWeight = M('TuanDetail')->where(array('articleid'=>$tuan['articleid']))->sum('weight_a');
        		$localWeight = $localWeight?$localWeight:0;
        		if ($localWeight >= $tuan['maxWeight']) {
        			M('Tuan')->where(array('articleid'=>$tuan['articleid']))->setField("status",1);
        		}
        	}
		}     	
	}
}