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
    public function orderInfo(){
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

            $person = M("DgOrderPerson")->where(array('orderID'=>$list['id']))->select();
            foreach ($person as $key => $value) {
                $baoguo = M('DgOrderBaoguo')->where(array('personID'=>$value['id']))->select();
                foreach ($baoguo as $k => $val) {
                    $baoguo[$k]['goods'] = M('DgOrderDetail')->where(array('baoguoID'=>$val['id']))->select();
                    if($val['eimg']){
                        $baoguo[$k]['eimg'] = explode(",", $val['eimg']);
                    }
                    if($val['image']){
                        $baoguo[$k]['image'] = explode(",", $val['image']);
                    }
                }
                $person[$key]['baoguo'] = $baoguo;
            }

            $goods = M("DgOrderDetail")->field('itemID,price,server,trueNumber,extends,sum(number) as num')->where(array('orderID'=>$list['id']))->group('itemID')->select(); 
            foreach ($goods as $key => $value) {
                $item = M('DgGoodsIndex')->where(array('id'=>$value['itemID']))->find(); 
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
}