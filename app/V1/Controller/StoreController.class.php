<?php
namespace V1\Controller;

class StoreController extends CommonController {

    public $user;
    public $agent;

    public function _initialize() {
        parent::_initialize();
        $token = I('post.token');
        if (!$user = $this->checkToken($token)) {
            returnJson('999','登录超时'); 
        }else{
            $this->user = $user;
        }

        $agentID = I('agentid');
        if ($agentID=='' || !is_numeric($agentID)) {die;}
        $map['id'] = $agentID;
        $map['show'] = 1;
        $agent = M('Agent')->where($map)->find();        
        if (!$agent) {die;}
        $this->agent = $agent;
    }

    public function getMain(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $ad = $this->getAd();
            if ($this->agent['hotkey']!='') {
                $hotkey = explode(",", $this->agent['hotkey']);
            }

            unset($map);
            $map['fid'] = 0;
            $map['comm'] = 1;
            $indexCate = M("AgentCate")->field('id,path,name')->where($map)->select();
            foreach ($indexCate as $key => $value) {
                unset($map);
                $map['comm'] = 1;
                $map['show'] = 1;
                $map['path|path1'] = array('like',$value['path'].'%');
                $goods = M("DgGoodsIndex")->where($map)->order('sort asc,id desc')->select();
                foreach ($goods as $k => $val) {
                    $goods[$k]['picname'] = getRealUrl($val['picname']);
                    $goods[$k]['num'] = 0;
                }
                $indexCate[$key]['goods'] = $goods;
            }
            returnJson(0,'success',['ad'=>$ad,'hotkey'=>$hotkey,'notice'=>$this->agent['notice'],'goods'=>$indexCate]);      
        } 
    }

    public function getAd(){
        $map['agentID'] = $this->agent['id'];
        $list = M('AgentAd')->cache(true)->where($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['image'] = getRealUrl($value['image']);
        }
        return $list;
    }

    //商品详情
    public function detail(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            
            $id = I('post.id');
            $specid = I('post.specid',0);
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            $map['id'] = $id;
            $map['show'] = 1;
            $map['agentID'] = $this->agent['id'];
            $list = M('DgGoods')->where($map)->find();
            if (!$list) {
                returnJson('-1','不存在的商品');
            }

            if ($list['image']=='') {
                $list['image'] = array($list['picname']);            
            }else{
                $list['image'] = explode(",", $list['image']);
            }
            foreach ($list['image'] as $key => $value) {
                $list['image'][$key] = getRealUrl($value);
            }

            if ($list['extends'] != '') {
                $list['extends'] = explode("\n", $list['extends']);
            }

            //获取套餐价格
            if ($specid!='' && is_numeric($specid)) {
                $thisSpec = M("DgGoodsIndex")->where(array('id'=>$specid,'agentID'=>$this->agent['id']))->find();
            }

            //贴心服务
            if ($list['server']!='') {
                $serID = explode(",", $list['server']);
                unset($map);
                $map['id'] = array('in',$serID);
                $server = M("Server")->where($map)->order('sort asc')->select();
                foreach ($server as $key => $value) {
                    $server[$key]['checked'] = false;
                }
            }

            //套餐
            unset($map);
            $map['goodsID'] = $list['id'];
            $map['base'] = 0;
            $map['agentID'] = $this->agent['id'];
            $spec = M("DgGoodsIndex")->where($map)->select();

            $list['content'] = htmlspecialchars_decode($list['content']);
            returnJson(0,'success',['goods'=>$list,'server'=>$server,'spec'=>$spec,'thisSpec'=>$thisSpec]);
        }
    }

    public function cart(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $map['memberID'] = $this->user['id'];
            $list = M("DgCart")->where($map)->order('typeID asc,number desc')->select();
            $total = 0;
            $weight = 0;
            foreach ($list as $key => $value) {
                $goods = M('DgGoodsIndex')->where('id='.$value['itemID'])->find(); 
                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("Server")->field('name,price')->where($map)->select();
                    $list[$key]['server'] = $server;
                }else{
                    $list[$key]['server'] = null;
                }
                $list[$key]['goodsNumber'] = $goods['number'];//套餐中包含几个商品
                //小计金额
                $money = $value['number'] * $goods['price'];
                $list[$key]['goods'] = $goods;
                $list[$key]['money'] = number_format($money,2);
            }       

            $heji = $this->getCartNumber($this->user);
            $wuliu = M("Wuliu")->where(array('agentID'=>0))->select();
            returnJson(0,'success',['goods'=>$list,'wuliu'=>$wuliu,'heji'=>$heji]);
        }
    }

    public function getCartNumber($user){
        $map['memberID'] = $user['id']; 
        $list = M("DgCart")->where($map)->select();
        $total = 0;
        $server = 0;
        $weight = 0;
        foreach ($list as $key => $value) {
            $goods = M('DgGoodsIndex')->where('id='.$value['itemID'])->find(); 
            if ($value['server']!='') {
                $serverID = explode(",",$value['server']);
                unset($map);
                $map['id'] = array('in',$serverID);
                $serverMoney = M("server")->where($map)->sum('price');                
            }else{
                $serverMoney = 0;
            }

            //贴心服务需要计算商品个数，所以要乘套餐里边商品的数量
            $goodsMoney = $goods['price'] * $value['number'];
            $serverMoney = $serverMoney * $value['goodsNumber'];
            $total += $goodsMoney + $serverMoney;
            $server += $serverMoney;
            $weight += $value['goodsNumber'] * $goods['weight'];       
            $number = count($list);
        }
        return array('number'=>$number,'total'=>$total,'serverMoney'=>$server,'weight'=>number_format($weight,2)); 
    }

    //添加到购物车
    public function cartAdd(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');} 
            $goodsID = I('post.goodsID');
            $number = I('post.number');
            $specid = I('post.specid');
            $server = I('post.server');
            $exts = I('post.exts');
            $typeID = I('post.typeID');
            $act = I('post.act','inc');

            if ($number=='' || !is_numeric($number) || $number<1) {
                returnJson('-1','参数错误');
            }
            if ($specid=='' || !is_numeric($specid)) {
                returnJson('-1','参数错误');
            }

            $goods = M("DgGoodsIndex")->where('id',$specid)->find();
            if (!$goods) {
                returnJson('-1','商品不存在');
            }

            if ($goods['empty']==1) {
                returnJson('-1','该商品已售罄');
            }

            $db = M("DgCart");
            $map['memberID'] = $this->user['id'];
            $map['itemID'] = $specid;
            $list = $db->where($map)->find();
            if ($act=='inc') {
                if ($list) {
                    if ($server) {
                        $data['server'] = $server;
                    }
                    $number = $list['number']+$number;
                    $data['number'] = $number;
                    $data['goodsNumber'] = $number*$goods['number'];
                    $db->where($map)->save($data);
                }else{
                    $data = [
                        'memberID'=>$this->user['id'],
                        'goodsID'=>$goods['goodsID'],
                        'itemID'=>$specid,
                        'number'=>$number,
                        'goodsNumber'=>$number*$goods['number'],
                        'typeID'=>$goods['typeID'],
                        'server'=>$server,
                        'extends'=>$exts
                    ];
                    $db->add($data);
                }
            }else{
                if ($list) {
                    if ($list['number']<=1) {
                        $db->where($map)->delete();
                    }else{
                        $number = $list['number']-$number;
                        $data['number'] = $number;
                        $data['goodsNumber'] = $number*$goods['number'];
                        $db->where($map)->save($data);
                    }                
                }
            }        
            $count = M("DgCart")->where(array('memberID'=>$this->user['id']))->count();
            returnJson(0,'添加成功',$count); 
        }
    }

    public function cartNumber(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}       
            $count = M("DgCart")->where(array('memberID'=>$this->user['id']))->count();
            returnJson(0,'success',$count); 
        }
    }

    //清空购物车
    public function cartClear(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}       
            $count = M("DgCart")->where(array('memberID'=>$this->user['id']))->delete();
            returnJson(0,'success'); 
        }
    }

    public function cartDel(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }

            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $res = M('DgCart')->where($map)->delete();
            if ($res) {
                returnJson(0,'success');
            }else{
                returnJson('-1','操作失败');
            }
        }
    }
}