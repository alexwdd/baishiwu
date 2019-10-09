<?php
namespace V1\Controller;

class OrderController extends CommonController {

    public $user;

    public function _initialize() {
        parent::_initialize();
        $token = I('post.token');
        if (!$user = $this->checkToken($token)) {
            returnJson('999','登录超时'); 
        }else{
            $this->user = $user;
        }
    }  

    public function index(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $payStatus = I('post.payStatus');
            switch ($payStatus) {
                case '0':
                    $pageName = '待付款';
                    break;
                case '1':
                    $pageName = '待审核';
                    break;
                case '2':
                    $pageName = '待配货';
                    break;
                case '3':
                    $pageName = '配货中';
                    break;
                case '4':
                    $pageName = '已发货';
                    break;
                case '99':
                    $pageName = '取消订单';
                    break;
                default:
                    $pageName = '全部订单';
                    break;
            } 
            $page = I('post.page',1);

            if ($payStatus!='' && is_numeric($payStatus)) {
                $map['payStatus'] = $payStatus;
            }            
            $map['memberID'] = $this->user['id'];           

            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('DgOrder');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();  
            foreach ($list as $key => $value) {
                $orderID = $value["id"]; //获取数据集中的id            
                $goods = M("DgOrderDetail")->field('*,sum(number) as num')->where(array('orderID'=>$orderID))->group('itemID')->select(); 
                foreach ($goods as $k => $val) {
                    $item = M('DgGoodsIndex')->where(array('id'=>$val['itemID']))->find(); 
                    $goods[$k]['goodsNumber'] = $val['num'] / $item['number'];
                }

                $list[$key]['goods'] = $goods; //给数据集追加字段num并赋值

                unset($where);
                $where['orderID'] = $orderID;
                $where['front'] = array('eq','');
                $where['back'] = array('eq','');
                $where['sn'] = array('eq','');
                $num = M("DgOrderPerson")->where($where)->count(); 
                if ($num>0) {
                    $list[$key]['upload'] = 0;
                }else{
                    $list[$key]['upload'] = 1;
                }

                if ($value['payType']>1) {
                    unset($where);
                    $where['orderID'] = $value["id"];
                    $bag = M("DgOrderBaoguo")->field('type,image')->where($where)->select();     
                    foreach ($bag as $k => $val) {
                        if(in_array($val['type'],[1,2,3])){//奶粉类
                            if($val['image']!='' && $val['sign']!=''){
                                $list[$key]['image'] = 1;
                            }else{
                                $list[$key]['image'] = 0;
                            }
                        }else{
                            if($val['image']=='') {
                                $list[$key]['image'] = 0;
                                break;
                            }else{
                                $list[$key]['image'] = 1;
                            }
                        }                                          
                    }                
                }
            }
            returnJson(0,'success',['next'=>$next,'name'=>$pageName,'data'=>$list]);
        }
    }

    //订单详情
    public function detail(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            
            $id = I('post.id');
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $list = M('DgOrder')->where($map)->find();
            if (!$list) {
                returnJson('-1','不存在的订单');
            }
            $list['createTime'] = date("Y-m-d H:i:s",$list['createTime']);
            $list['payType'] = getPayType($list['payType']);

            $person = M("DgOrderPerson")->where(array('orderID'=>$list['id']))->select();
            foreach ($person as $key => $value) {
                $baoguo = M('DgOrderBaoguo')->where(array('personID'=>$value['id']))->select();
                foreach ($baoguo as $k => $val) {
                    $baoguo[$k]['goods'] = M('DgOrderDetail')->where(array('baoguoID'=>$val['id']))->select();
                    if($val['eimg']){
                        $temp = explode(",", $val['eimg']);
                        foreach ($temp as $key => $value) {
                            $temp[$key] = getRealUrl($value);
                        }
                        $baoguo[$k]['eimg'] = $temp;
                    }
                    if($val['image']){
                        $temp = explode(",", $val['image']);
                        foreach ($temp as $key => $value) {
                            $temp[$key] = getRealUrl($value);
                        }
                        $baoguo[$k]['image'] = $temp;
                    }
                    if(in_array($val['type'],[12,13,14])){
                        $baoguo[$k]['url'] = config('site.url'.$f['type']);
                    }else{
                        $baoguo[$k]['url'] = '';
                    }
                }
                $person[$key]['baoguo'] = $baoguo;
            }

            $goods = M("DgOrderDetail")->field('itemID,price,server,trueNumber,extends,sum(number) as num')->where(array('orderID'=>$list['id']))->group('itemID')->select();    
            foreach ($goods as $key => $value) {
                $item = M('DgGoodsIndex')->where(array('id'=>$value['itemID']))->find();
                $item['picname'] = getRealUrl($item['picname']);
                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("server")->field('name,price')->where($map)->select();
                    $goods[$key]['server'] = $server;
                }else{
                    $goods[$key]['server'] = null;
                }  
                $goods[$key]['goodsNumber'] = $value['num'] / $item['number'];
                $goods[$key]['goods'] = $item;
                $goods[$key]['money'] = $value['price']*($value['num']/$item['number']);
            }
            returnJson(0,'success',['goods'=>$goods,'person'=>$person,'order'=>$list]);
        }
    }

    public function del(){
        if(IS_POST){
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $id = I('post.id');
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $map['payStatus'] = 0;
            $map['image'] = array('eq','');    
            $list = M('DgOrder')->where($map)->find();
            if ($list) {
                M('DgOrder')->where(array('id'=>$id))->delete();
                M('DgOrderBaoguo')->where(array('orderID'=>$id))->delete();
                M('DgOrderPerson')->where(array('orderID'=>$id))->delete();
                M('DgOrderDetail')->where(array('orderID'=>$id))->delete();
                returnJson(0,'success');
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    public function progress(){
        if(IS_POST){
            if(!checkFormDate()){returnJson('-1','ERROR');}

            $No = I('post.No');
            if ($No=='') {
                returnJson('-1','缺少运单号');
            }
            $token = $this->getAueToken();
            if ($token=='') {
                returnJson('-1','系统错误，稍后重试');
            }
            $list = M("DgOrderBaoguo")->where(['kdNo'=>$No])->find();
            if (!$list) {
                returnJson('-1','包裹不存在');
            }
            
            $url = 'http://aueapi.auexpress.com/api/ShipmentOrderTrack/Cache?OrderId='.$No;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
            $result = curl_exec($ch);
            $result = json_decode($result,true);
            if ($result['Code']!=0) {
                returnJson('-1','没有查询到相关资源');
            }
            returnJson(0,'success',['data'=>$result]);
        }
    }
}