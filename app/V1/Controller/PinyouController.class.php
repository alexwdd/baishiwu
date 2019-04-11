<?php
namespace V1\Controller;

class PinyouController extends CommonController {

	public function publish(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$password = I('post.password');			
			$openid = I('post.openid');

			$user = $this->userCheck($userid,$password,$openid);

        	$obj = D('Tuan');
        	if ($data = $obj->create()) {
        		$cityArr = S('C_DATA')['city'];		
        		$flag = false;
				for ($i=0; $i < count($cityArr); $i++) {
					if ($data['cityID'] == $cityArr[$i]['id']) {
						$flag=true;
						break;
					}
				}
				if (!$flag) {
					returnJson('-1','cityID不正确');
				}

				$data['order_no'] = getOrderNo('P');
	        	$data['userid'] = $userid;
	        	$data['headimg'] = $user['headimg'];
				$data['status'] = 0;
				$data['auth'] = 0;
				$data['order'] ='';
				$data['company'] = '';
				$data['title'] = '会员【'.$user['nickname'].'】发起拼邮，包裹下限'.$data['maxWeight'].'公斤';
	            if ($list = $obj->add($data)) {
	            	returnJson('0',C("SUCCESS_RETURN"),array('articleid'=>$list,'order_no'=>$data['order_no']));
	            } else {
	            	returnJson('-1','操作失败');
	            }
	        } else {
	            returnJson('-1',$obj->getError());
	        }			
		}
    }

    //获取帖子信息
	public function getinfo(){
		if ($_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$articleid = I('post.articleid');
			if ($articleid=='' || !is_numeric($articleid)) {
				returnJson('-1','缺少articleid');
			}
        	$map['articleid'] = $articleid;        	
        	$list = M('Tuan')->field('articleid,title,userid,type,goodstype,address,longitude,latitude,maxWeight,contact,phone,wechat,detail,bill,status,auth,order,company,isStop,createTime')->where($map)->find();
        	if ($list){
        		$list['goods'] = M('TuanDetail')->field('goodsid,contact,status,detail,weight,weight_a,createTime')->where(array('articleid'=>$articleid))->select();
                $list['currentWeight'] = 0;
        		$list['currentWeight_a'] = 0;
        		foreach ($list['goods'] as $key => $value) {
                    $list['currentWeight'] += $value['weight'];
        			$list['currentWeight_a'] += $value['weight_a'];
        		}

                if ($list['company']!='') {
                    $item = M('OptionItem')->where(array('name'=>$list['company'],'cate'=>5))->find();
                    if ($item) {
                        $list['companyUrl'] = $item['value'];
                    }else{
                        $list['companyUrl'] = '';
                    }
                }else{
                    $list['companyUrl'] = '';
                }

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

			$articleid = I('post.articleid');
			$data['detail'] = I('post.detail');
			$data['status'] = I('post.status');
            $data['isStop'] = I('post.isStop');
			
			if ($articleid=='' || !is_numeric($articleid)) {
	    		returnJson('-1','缺少articleid');
	    	}

			$user = $this->userCheck($userid,$password,$openid);
	
        	$obj = M('Tuan');
        	$r = $obj->where(array('articleid'=>$articleid,'userid'=>$userid))->find();
        	if (!$r) {
        		returnJson('-1','信息不存在');
        	}

            /*if ($data['isStop']) {
                $localWeight = M('TuanDetail')->where(array('articleid'=>$articleid))->sum('weight_a');
                $localWeight = $localWeight?$localWeight:0;
                if ($localWeight < $r['maxWeight']) {
                    unset($data['isStop']);
                }
            }*/

        	if ($data['detail']=='' && $data['status']=='') {
        		$data['updateTime'] = time();
        	}

        	if ($data['detail']=='') {
        		unset($data['detail']);
        	}

        	if ($data['status']=='') {
        		unset($data['status']);
        	}
            unset($data['type']);
            if ($list = $obj->where(array('articleid'=>$articleid))->save($data)) {
            	returnJson('0',C("SUCCESS_RETURN"),array('articleid'=>$articleid));
            } else {
            	returnJson('-1','操作失败');
            }		
		}
	}

	/*获取拼团列表 
	/ 如果带上userid则获取用户参与拼团列表；服务器需联合查询用户的包裹和包裹所在的拼团来获得用户参与
	/ 的拼团
	*/
	public function getlist(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

            $userid = I('post.userid');
			$createID = I('post.createID');
			$type = I('post.type');
			$status = I('post.status');
			$auth = I('post.auth');
			$goodstype = I("post.goodstype");
			$page = I('post.page',1);
            $lng = I('post.lng');
            $lat = I('post.lat');
            $cityID = I('post.cityID');

        	if ($type!='') {
        		$map['type'] = $type;
        	}

            if ($createID!='') {
                $map['userid'] = $createID;
            }

        	if ($auth!='') {
        		$map['auth'] = $auth;
        	}

            if ($cityID!='' && is_numeric($cityID)) {
                $map['cityID'] = $cityID;
            }

        	if ($goodstype!='') {
        		$map['goodstype'] = $goodstype;
        	}
            
        	if ($userid!='') {
        		$tuanID = M('TuanDetail')->where(array('userid'=>$userid))->getField('articleid',true);
        		if ($tuanID) {
        			$map['articleid'] = array('in',$tuanID);
        		}else{      
        			$map['articleid'] = 0;
        		}     
                if ($status!='') {
                    $map['status'] = $status;
                }	
        	}else{
                if ($status!='') {
                    $map['status'] = $status;
                }else{
                    $map['status'] = array('lt',3);
                }
            }      	
        	$pagesize = 10;
        	$firstRow = $pagesize*($page-1);         

        	$obj = M('Tuan');
        	$count = $obj->where($map)->count();
        	$totalPage = ceil($count/$pagesize);
        	if ($page < $totalPage) {
        		$next = 1;
        	}else{
        		$next = 0;
        	}

            if ($lng!='' && $lat!='') {
                $map['status'] = array('lt',3);
                $field='articleid,userid,title,goodstype,type,address,maxWeight,contact,auth,status,isStop,createTime as time,latitude as lat,longitude as lng,ROUND(6378.138*2*ASIN(SQRT(POW(SIN(('.$lat.'*PI()/180-latitude*PI()/180)/2),2)+COS('.$lat.'*PI()/180)*COS(latitude*PI()/180)*POW(SIN(('.$lng.'*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance';
                $order = 'distance asc,status asc,isStop asc,updateTime desc';
            }else{
                $field = 'articleid,userid,title,goodstype,type,address,maxWeight,contact,auth,status,isStop,createTime as time,latitude as lat,longitude as lng';
                $order = 'status asc,isStop asc,updateTime desc';
            }

        	$list = $obj->field($field)->where($map)->limit($firstRow.','.$pagesize)->order($order)->select();
        	foreach ($list as $key => $value) {
        		$localWeight = M('TuanDetail')->where(array('articleid'=>$value['articleid']))->sum('weight');
        		$localWeight = $localWeight?$localWeight:0;
        		$list[$key]['currentWeight'] = $localWeight;
        	}

        	returnJson('0',C("SUCCESS_RETURN"),array('articles'=>$list,'next'=>$next));
		}
	}

    /*获取拼团列表 
    / 如果带上userid则获取用户参与拼团列表；服务器需联合查询用户的包裹和包裹所在的拼团来获得用户参与
    / 的拼团
    */
    public function getbill(){
        if (IS_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $articleid = I('post.articleid');

            if ($articleid=='' || !is_numeric($articleid)) {
                echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
            }

            $map['articleid'] = $articleid; 
            $obj = M('TuanBill');
            $list = $obj->where($map)->select();
            foreach ($list as $key => $value) {
                $list[$key]['nickname'] = M('Member')->where(array('id'=>$value['userid']))->getField('nickname');
            }
            returnJson('0',C("SUCCESS_RETURN"),$list);
        }
    }

    public function getmybill(){
        if (IS_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $articleid = I('post.articleid');
            $userid = I('post.userid');

            if ($articleid=='' || !is_numeric($articleid)) {
                echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
            }

            if ($userid=='' || !is_numeric($userid)) {
                echo json_encode(array('code'=>0,'msg'=>'参数错误'));die;
            }

            $map['articleid'] = $articleid; 
            $map['userid'] = $userid; 
            $obj = M('TuanBill');
            $list = $obj->where($map)->find();
            $list['nickname'] = M('Member')->where(array('id'=>$list['userid']))->getField('nickname');
            returnJson('0',C("SUCCESS_RETURN"),$list);
        }
    }

    public function dopay(){
        if ($_POST) {
            if (!checkFormDate()){
                returnJson('-1','未知错误');
            }

            $userid = I('post.userid');
            $openid = I('post.openid');
            $password = I('post.password');
            $articleid= I('post.articleid');
            $jietu= I('post.jietu');

            $user = $this->userCheck($userid,$password,$openid);
            $map['userid'] = $userid;
            $map['articleid'] = $articleid;
            $res = M('TuanBill')->where($map)->save(array('jietu'=>$jietu,'status'=>1));
            if ($res) {
                returnJson('0',C('SUCCESS_RETURN'));
            }else{
                returnJson('-1','操作失败');
            }
            
        }
    }

	//获取用户发布的拼邮信息
	public function get_userlist(){
		if (IS_POST) {
			if (!checkFormDate()){
				returnJson('-1','未知错误');
			}

			$userid = I('post.userid');
			$type = I('post.type');
			$status = I('post.status');
			$auth = I('post.auth');
			$goodstype = I("post.goodstype");
			$page = I('post.page',1);

        	if ($type!='') {
        		$map['type'] = $type;
        	}

        	if ($auth!='') {
        		$map['auth'] = $auth;
        	}

        	if ($status!='') {
        		$map['status'] = $status;
        	}

        	if ($goodstype!='') {
        		$map['goodstype'] = $goodstype;
        	}

        	if ($userid!='') {
        		$map['userid'] = $userid;      		
        	}        	
        	$pagesize = 10;
        	$firstRow = $pagesize*($page-1);         

        	$obj = M('Tuan');
        	$count = $obj->where($map)->count();
        	$totalPage = ceil($count/$pagesize);
        	if ($page < $totalPage) {
        		$next = 1;
        	}else{
        		$next = 0;
        	}

        	$list = $obj->field('articleid,userid,title,goodstype,type,address,maxWeight,contact,auth,status,isStop,createTime as time')->where($map)->limit($firstRow.','.$pagesize)->order('articleid desc')->select();
        	foreach ($list as $key => $value) {
        		$localWeight = M('TuanDetail')->where(array('articleid'=>$value['articleid']))->sum('weight');
        		$localWeight = $localWeight?$localWeight:0;
        		$list[$key]['currentWeight'] = $localWeight;
        	}

        	returnJson('0',C("SUCCESS_RETURN"),array('articles'=>$list,'next'=>$next));
		}
	}
}