<?php
namespace Store\Controller;

class StoreController extends HomeController
{
    public $sessionID;

    public function _initialize(){
        parent::_initialize();
        $this->sessionID = session_id(); 
    }

    public function index()
    {
        $path = I('path');
        $brandID = I('brandID');
        $keyword = I('keyword');

        $map['agentID'] = $this->agent['id'];
        if ($path!='') {
            $map['path'] = array('like',$path.'%');            
            $cateName = M("AgentCate")->where(array('path'=>$path))->getField('name');
        }else{
            $cateName = '全部';
        }

        $this->assign('cateName',$cateName);  
        $this->assign('brandID',$brandID);  
        $this->assign('path',$path); 
        $this->assign('keyword',$keyword); 

        unset($map);
        $map['agentID'] = $this->agent['id'];
        $brand = M("Brand")->where($map)->cache(true)->select();
        $this->assign('brand',$brand);

        //规格筛选     
        $map1['path'] = array('like',$path.'%');
        $map1['agentID'] = $this->agent['id'];
        $filter = M("Goods")->where($map1)->group('typeID')->getField("typeID",true);

        unset($map);
        if ($filter) {
            $map['typeID'] = array('in',$filter);
            $filter = M('GoodsAttribute')->where($map)->select();
            foreach ($filter as $key => $value) {
                $filter[$key]['values'] = explode("\n", $value['values']);
            }
            $this->assign('filter',$filter);
        }        
        $this->display();
    }

    public function ajaxGoods()
    {
        $path = I('path');
        $keyword = I('keyword');
        $brandID = I('brandID');
        $price = I('price');
        $attr = I('attr');
        $page = I('post.page',1);

        $map['agentID'] = $this->agent['id'];
        $map['show'] = 1;
        if ($path!='') {
            $map['path'] = array('like',$path.'%');            
        }
        if ($keyword!='') {
            $map['name'] = array('like','%'.$keyword.'%');            
        }
        if ($price!='') {
            $price = explode("-", $price);
            if (count($price)==2) {
                $map['price'] = array('between',array($price[0],$price[1]));
            }else{
                $map['price'] = array('egt',$price[0]);
            }
        }
        if ($brandID!='') {
            $map['brandID'] = $brandID;
        }
        if ($attr!='') {
            $attr = explode(",", $attr);
            $mapArr = [];
            foreach ($attr as $key => $value) {
                if ($value!='') {
                    array_push($mapArr, $value);
                }
            }
            if (count($mapArr)>0) {
                $where['attr_value'] = array('in',$attr);
                $goodID = M("GoodsAttr")->where($where)->group('goods_id')->getField("goods_id",true);
                if (!$goodID) {
                    $map['id'] = 0;
                }                
            }            
        }        

        $pagesize = 10;
        $firstRow = $pagesize*($page-1); 

        $obj = M('Goods');
        $count = $obj->where($map)->count();
        $totalPage = ceil($count/$pagesize);
        if ($page < $totalPage) {
            $next = 1;
        }else{
            $next = 0;
        }
        $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
        $this->assign('list',$list);  
        $res = $this->fetch();
        echo json_encode(['next'=>$next,'data'=>$res]);
    }

    public function search(){
        if (IS_POST) {
            $keyword = I('keyword');
            if ($keyword=='') {
                $this->error('请输入关键词');
            }
            
            $map['agentID'] = $this->agent['id'];
            $map['name'] = array('like','%'.$keyword.'%');
            $map['show'] = 1;

            //查询数据
            $list = M('Goods')->field('id,name,picname,price')->where($map)->order('id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['picname'] = getThumb($value["picname"],350,350);
            }
            echo json_encode($list);
        }else{
            $this->display();
        }
    }

    public function category(){
        $map['model'] = 2;
        $map['fid'] = 0;
        $map['agentID'] = $this->agent['id'];
        $list = M('AgentCate')->cache(true)->where($map)->order('sort asc,id asc')->select();
        foreach ($list as $key => $value) {
            $child = M('AgentCate')->cache(true)->where(array('fid'=>$value['id']))->order('sort asc,id asc')->select();
            foreach ($child as $k => $v) {
                $child[$k]['child'] = M('AgentCate')->cache(true)->where(array('fid'=>$v['id']))->order('sort asc,id asc')->select();
            }
            $list[$key]['child'] = $child;
        }
        $this->assign('list',$list);

        unset($map);
        $map['agentID'] = $this->agent['id'];
        $brand = M('Brand')->where($map)->cache(true)->select();
        $this->assign('brand',$brand);
        $this->display();
    }

    public function detail(){     
    	$id = I('get.id');
        if ($id=='' || !is_numeric($id)) {
            $this->error('参数错误');
        }
        $map['id'] = $id;
        $map['show'] = 1;
        $map['agentID'] = $this->agent['id'];
        $list = M('Goods')->where($map)->find();
        if (!$list) {
            $this->error('不存在的商品');
        }
        if ($list['image']=='') {
        	$image = array($list['picname']);
        }else{
        	$image = explode(",", $list['image']);
        }

        if ($list['brandID']>0) {
            $list['brandName'] = M('Brand')->where(array('id'=>$list['brandID']))->getField("name");
        }
        $list['totalYunfei'] = $list['yunfei']*$list['weight'];
        $this->assign('image',$image);
        $this->assign('list',$list);

        if ($list['typeID']>0) {
            $attr = M("GoodsAttr")->where('goods_id='.$list['id'])->select();
            foreach ($attr as $key => $value) {
                $attr[$key]['name'] = M('GoodsAttribute')->where('id='.$value['attr_id'])->getField('name');
            }
            $this->assign('attr',$attr);

            $filter_spec = $this->get_spec($list['id']);
            $this->assign('filter_spec',$filter_spec);

            $spec_goods_price  = M('SpecGoodsPrice')->where("goods_id", $list['id'])->getField("key,price,weight,number,isBaoyou,item_id"); // 规格 对应 价格 库存表
            $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应
        }
        
        //贴心服务
        if ($list['server']!='') {
            $serID = explode(",", $list['server']);
            unset($map);
            $map['id'] = array('in',$serID);
            $map['agentID'] = $this->agent['id'];
            $server = M("Server")->where($map)->order('sort asc')->select();
            $this->assign('server',$server);
        }

        $this->display();      
    }

    public function get_spec($goods_id){
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('SpecGoodsPrice')->where("goods_id=".$goods_id)->getField("GROUP_CONCAT(`key` ORDER BY store_count desc SEPARATOR '_') ");  
        $filter_spec = array();
        if ($keys) {
            $keys = str_replace('_', ',', $keys);
            $sql = "SELECT a.name,a.sort,b.* FROM pm_spec AS a INNER JOIN pm_spec_item AS b ON a.id = b.specID WHERE b.id IN($keys) ORDER BY b.id";

            $filter_spec2 = \think\Db::query($sql);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item']
                );
            }
        }
        return $filter_spec;
    }

    public function addcart(){

        $goodsID = I('get.goodsID');
        $baoguoID = I('get.baoguoID');
        $number = I('get.number');
        $itemID = I('get.itemID');
        $server = I('get.server');
        if ($goodsID=='' || !is_numeric($goodsID)) {
            $this->error('参数错误');
        }
        if ($baoguoID=='' || !is_numeric($baoguoID)) {
            $this->error('参数错误');
        }
        if ($number=='' || !is_numeric($number) || $number<1) {
            $this->error('参数错误');
        }
        if ($itemID=='' || !is_numeric($itemID)) {
            $this->error('参数错误');
        }

        $db = M("Cart");
        if ($this->user['id']==0) {
            $sql = "`sessionID` = '".$this->sessionID."' AND `goodsID` = ".$goodsID." AND `itemID` = ".$itemID;
        }else{
            $sql = "(`memberID` = ".$this->user['id']." OR `sessionID` = '".$this->sessionID."') AND `goodsID` = ".$goodsID." AND `itemID` = ".$itemID;
        } 
        $list = $db->where($sql)->find();
        if ($list) {
            $db->where($sql)->setInc('number',$number);
        }else{
            $data = [
                'memberID'=>$this->user['id'],
                'sessionID'=>$this->sessionID,
                'goodsID'=>$goodsID,
                'baoguoID'=>$baoguoID,
                'itemID'=>$itemID,
                'number'=>$number,
                'server'=>$server,
            ];
            $db->add($data);
        }
        $count = $this->getCartNumber();
        $this->success($count);
    }

    public function ajaxCartNumber(){
        echo $this->getCartNumber();
    }

    public function getCartNumber(){
        if ($this->user['id']==0) {
            $map['sessionID'] = $this->sessionID;
        }else{
            $map['memberID'] = $this->user['id'];
            $map['sessionID'] = $this->sessionID;
            $map['_logic'] = 'OR';
        }        
        return M("Cart")->where($map)->count();
    }

    //我的购物车
    public function cart(){
        if ($this->user['id']==0) {
            echo "<script>window.location.href='app://login';</script>";
            die;
        }        
        $addID = I('param.addID');
        $this->assign('addID',$addID);  
        $this->display();
    }

    public function ajaxCart(){    
        $map['memberID'] = $this->user['id'];
        $map['sessionID'] = $this->sessionID;
        $map['_logic'] = 'OR';
        $list = M("Cart")->where($map)->select();
        unset($map);
        $map['agentID'] = $this->agent['id'];
        $baoguo = M('Baoguo')->where($map)->field('id,name,price')->select();
        foreach ($baoguo as $k => $v) {
            $baoguo[$k]['order_no'] = getStoreOrderNo();
            $baoguo[$k]['goods']=[];
        } 
        $totalPrice = 0;
        foreach ($list as $key => $value) {
            $goods = M('Goods')->where('id='.$value['goodsID'])->find();            
            if ($goods) { 
                $list[$key]['pname'] = $goods['name'];
                $list[$key]['picname'] = $goods['picname'];
                $list[$key]['yunfei'] = $goods['yunfei'];
                //读取参数
                if ($value['itemID']>0) {
                    $pram = M('SpecGoodsPrice')->where('item_id='.$value['itemID'])->find();
                    $list[$key]['price'] = $pram['price'];
                    $list[$key]['weight'] = $pram['weight'];
                    $list[$key]['isBaoyou'] = $pram['isBaoyou'];
                    $totalPrice = $totalPrice + $pram['price']*$value['number'];
                    $list[$key]['pram'] = $pram['key_name'];
                    $list[$key]['goodsNumber'] = $pram['number']*$value['number'];//套餐中包含几个商品
                }else{
                    $list[$key]['price'] = $goods['price'];
                    $list[$key]['weight'] = $goods['weight'];
                    $list[$key]['isBaoyou'] = $goods['isBaoyou'];
                    $list[$key]['goodsNumber'] = $value['number'];
                    $totalPrice = $totalPrice + $goods['price']*$value['number'];
                }

                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("server")->where($map)->select();
                    $serverMoney = 0;
                    foreach ($server as $k => $v) {
                        $serverMoney += $v['price'];
                    }
                    $totalPrice = $totalPrice + $serverMoney*$list[$key]['goodsNumber'];
                    $list[$key]['serverArr'] = $server;
                }

                foreach ($baoguo as $k => $v) {
                    if ($goods['baoguoID'] == $v['id']) {
                        array_push($baoguo[$k]['goods'], $list[$key]);                     
                    }
                }
                $totalPrice = number_format($totalPrice, 2, '.', '');
            }   
        }  
        $this->assign('totalPrice',$totalPrice);
        $this->assign('list',$list);

        //收件信息
        unset($map);
        $addID = I('param.addID');
        if ($addID!='') {
            $map['id']=$addID;
        }
        $map['memberID'] = $this->user['id'];
        $address = M('Address')->where($map)->order('def desc , id desc')->find();
        $this->assign('address',$address);

        //发件人信息
        $where['memberID'] = $this->user['id'];
        $where['sender'] = array('neq','');
        $last = M('Order')->field('sendName,sendPhone')->where($where)->order('id desc')->find(); 
        $this->assign('last',$last);

        //支付方式
        unset($map);
        $map['agentID'] = $this->agent['id'];
        $card = M('Card')->where($map)->cache(true)->order('sort asc')->select();
        $this->assign('card',$card);   


        //获取运费
        $baoguo = $this->getYunfei($baoguo);
        $this->assign('baoguo',$baoguo);
        $totalPriceRmb = number_format(($totalPrice+$baoguo[
            'totalYunfei'])*$this->agent['huilv'], 2, '.', '');
        $this->assign('totalPriceRmb',$totalPriceRmb);

        $heji = number_format(($totalPrice+$baoguo[
            'totalYunfei']), 2, '.', '');
        $this->assign('heji',$heji);
        $content = $this->fetch();
        echo $content;
    }

    //获取运费
    public function getCartYunfei(){
        if ($this->user['id']==0) {
            die;
        }
        unset($map);
        $map['memberID'] = $this->user['id'];
        $map['sessionID'] = $this->sessionID;
        $map['_logic'] = 'OR';
        $list = M("Cart")->where($map)->select();

        unset($map);
        $map['agentID'] = $this->agent['id'];
        $baoguo = M('Baoguo')->where($map)->field('id,name,price')->select();
        foreach ($baoguo as $k => $v) {
            $baoguo[$k]['order_no'] = getStoreOrderNo();
            $baoguo[$k]['goods']=[];
        } 
        $totalPrice = 0;
        foreach ($list as $key => $value) {
            $goods = M('Goods')->where('id='.$value['goodsID'])->find();            
            if ($goods) {                
                $list[$key]['pname'] = $goods['name'];
                $list[$key]['picname'] = $goods['picname'];
                $list[$key]['yunfei'] = $goods['yunfei'];
                //读取参数
                if ($value['itemID']>0) {
                    $pram = M('SpecGoodsPrice')->where('item_id='.$value['itemID'])->find();
                    $list[$key]['price'] = $pram['price'];
                    $list[$key]['weight'] = $pram['weight'];
                    $list[$key]['isBaoyou'] = $pram['isBaoyou'];
                    $totalPrice = $totalPrice + $pram['price']*$value['number'];
                    $list[$key]['pram'] = $pram['key_name'];
                    $list[$key]['goodsNumber'] = $pram['number']*$value['number'];//套餐中包含几个商品
                }else{
                    $list[$key]['price'] = $goods['price'];
                    $list[$key]['weight'] = $goods['weight'];
                    $list[$key]['isBaoyou'] = $goods['isBaoyou'];
                    $list[$key]['goodsNumber'] = $value['number'];
                    $totalPrice = $totalPrice + $goods['price']*$value['number'];
                }      

                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("server")->where($map)->select();
                    $serverMoney = 0;
                    foreach ($server as $k => $v) {
                        $serverMoney += $v['price'];
                    }
                    $totalPrice = $totalPrice + $serverMoney*$list[$key]['goodsNumber'];
                    $list[$key]['serverArr'] = $server;
                }

                foreach ($baoguo as $k => $v) {
                    if ($goods['baoguoID'] == $v['id']) {
                        array_push($baoguo[$k]['goods'], $list[$key]);        
                    }
                }      
                $totalPrice = number_format($totalPrice, 2, '.', '');
            }   
        }       
        //获取运费
        $baoguo = $this->getYunfei($baoguo);
        if (I('get.quhuoType')==2) {
            $baoguo['totalYunfei'] = 0;
        }
        $totalPriceRmb = number_format(($totalPrice+$baoguo[
            'totalYunfei'])*$this->agent['huilv'], 2, '.', '');

        $res = ['totalPrice'=>$totalPrice,'yunfei'=>$baoguo['totalYunfei'],'heji'=>($totalPrice+$baoguo['totalYunfei']),'rmb'=>$totalPriceRmb];
        echo json_encode($res);
    }

    //设置购物数量
    public function setCartNum(){
        if ($this->user['id']==0) {
            die;
        }
        $map['id'] = I('param.cartID');
        $data['number'] = I('param.number');
        $obj = M('Cart');
        $obj->where($map)->setField($data); 

        unset($map);
        $map['memberID'] = $this->user['id'];
        $map['sessionID'] = $this->sessionID;
        $map['_logic'] = 'OR';
        $list = M("Cart")->where($map)->select();

        unset($map);
        $map['agentID'] = $this->agent['id'];
        $baoguo = M('Baoguo')->where($map)->field('id,name,price')->select();
        foreach ($baoguo as $k => $v) {
            $baoguo[$k]['order_no'] = getStoreOrderNo();
            $baoguo[$k]['goods']=[];
        } 
        $totalPrice = 0;
        foreach ($list as $key => $value) {
            $goods = M('Goods')->where('id='.$value['goodsID'])->find();            
            if ($goods) {                
                $list[$key]['pname'] = $goods['name'];
                $list[$key]['picname'] = $goods['picname'];
                $list[$key]['yunfei'] = $goods['yunfei'];
                //读取参数
                if ($value['itemID']>0) {
                    $pram = M('SpecGoodsPrice')->where('item_id='.$value['itemID'])->find();
                    $list[$key]['price'] = $pram['price'];
                    $list[$key]['weight'] = $pram['weight'];
                    $list[$key]['isBaoyou'] = $pram['isBaoyou'];
                    $totalPrice = $totalPrice + $pram['price']*$value['number'];
                    $list[$key]['pram'] = $pram['key_name'];
                    $list[$key]['goodsNumber'] = $pram['number']*$value['number'];//套餐中包含几个商品
                }else{
                    $list[$key]['price'] = $goods['price'];
                    $list[$key]['weight'] = $goods['weight'];
                    $list[$key]['isBaoyou'] = $goods['isBaoyou'];
                    $list[$key]['goodsNumber'] = $value['number'];
                    $totalPrice = $totalPrice + $goods['price']*$value['number'];
                }      

                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("server")->where($map)->select();
                    $serverMoney = 0;
                    foreach ($server as $k => $v) {
                        $serverMoney += $v['price'];
                    }
                    $totalPrice = $totalPrice + $serverMoney*$list[$key]['goodsNumber'];
                    $list[$key]['serverArr'] = $server;
                }

                foreach ($baoguo as $k => $v) {
                    if ($goods['baoguoID'] == $v['id']) {
                        array_push($baoguo[$k]['goods'], $list[$key]);        
                    }
                }      
                $totalPrice = number_format($totalPrice, 2, '.', '');
            }   
        }       
        //获取运费
        $baoguo = $this->getYunfei($baoguo);
        if (I('get.quhuoType')==2) {
            $baoguo['totalYunfei'] = 0;
        }
        $totalPriceRmb = number_format(($totalPrice+$baoguo[
            'totalYunfei'])*$this->agent['huilv'], 2, '.', '');

        $res = ['totalPrice'=>$totalPrice,'yunfei'=>$baoguo['totalYunfei'],'heji'=>($totalPrice+$baoguo['totalYunfei']),'rmb'=>$totalPriceRmb];
        echo json_encode($res);
    }

    public function delcart(){
        $map['id'] = I('param.id');
        M('Cart')->where($map)->delete();          
    }

    //保存订单
    public function order(){
        if ($this->user['id']==0) {
            echo "<script>window.location.href='app://login';</script>";
            die;
        }

        //保存地址
        $sn = I('post.sn');
        $front = I('post.front');
        $back = I('post.back');
        $addressID = I('post.addressID');
        if ($sn!='') {
            $address['sn'] = $sn;
        }
        if ($front!='') {
            $address['front'] = $front;
        }
        if ($back!='') {
            $address['back'] = $back;
        }
        if ($address) {
            M("Address")->where(["id"=>$addressID])->save($address);
        }
        
        $map['memberID'] = $this->user['id'];
        $map['sessionID'] = $this->sessionID;
        $map['_logic'] = 'OR';
        $list = M("Cart")->where($map)->select();
        if (!$list) {
            $this->error('没有选择任何商品');
        }

        $baoguo = M('Baoguo')->where(['agentID'=>$this->agent['id']])->field('id,name,price')->select();
        foreach ($baoguo as $k => $v) {
            $baoguo[$k]['order_no'] = getStoreOrderNo();
            $baoguo[$k]['goods']=[];
        } 

        $totalPrice = 0;
        $yongjin = 0;
        foreach ($list as $key => $value) {
            $goods = M('Goods')->where('id='.$value['goodsID'])->find();            
            if ($goods) {                
                $list[$key]['pname'] = $goods['name'];
                $list[$key]['picname'] = $goods['picname'];
                $list[$key]['yunfei'] = $goods['yunfei'];
                //读取参数
                if ($value['itemID']>0) {
                    $pram = M('SpecGoodsPrice')->where('item_id='.$value['itemID'])->find();
                    $list[$key]['price'] = $pram['price'];
                    $list[$key]['fencheng1'] = $pram['fencheng1'];
                    $list[$key]['fencheng2'] = $pram['fencheng2'];
                    $list[$key]['weight'] = $pram['weight'];
                    $list[$key]['isBaoyou'] = $pram['isBaoyou'];
                    $totalPrice = $totalPrice + $pram['price']*$value['number'];
                    $list[$key]['pram'] = $pram['key_name'];
                    $list[$key]['goodsNumber'] = $pram['number']*$value['number'];//套餐中包含几个商品
                }else{
                    $list[$key]['price'] = $goods['price'];
                    $list[$key]['fencheng1'] = $goods['fencheng1'];
                    $list[$key]['fencheng2'] = $goods['fencheng2'];
                    $list[$key]['weight'] = $goods['weight'];
                    $list[$key]['isBaoyou'] = $goods['isBaoyou'];
                    $list[$key]['pram'] = '';
                    $list[$key]['goodsNumber'] = $value['number'];
                    $totalPrice = $totalPrice + $goods['price']*$value['number'];
                }          
                $yongjin += ($list[$key]['fencheng1']+$list[$key]['fencheng2'])*$value['number'];

                if ($value['server']!='') {
                    $serverID = explode(",",$value['server']);
                    unset($map);
                    $map['id'] = array('in',$serverID);
                    $server = M("server")->where($map)->select();
                    $serverMoney = 0;
                    foreach ($server as $k => $v) {
                        $serverMoney += $v['price'];
                    }
                    $totalPrice = $totalPrice + $serverMoney*$list[$key]['goodsNumber'];
                    $list[$key]['serverArr'] = $server;
                }

                foreach ($baoguo as $k => $v) {
                    if ($goods['baoguoID'] == $v['id']) {
                        array_push($baoguo[$k]['goods'], $list[$key]);                    
                    }
                } 
            }   
        }
        $totalPrice = number_format($totalPrice, 2, '.', '');
        $baoguo = $this->getYunfei($baoguo);        
        $totalYunfei = $baoguo['totalYunfei'];
        $baoguo = $baoguo['baoguo'];

        $data = I('post.');
        if ($data['quhuoType']==2) {
            $totalYunfei = 0;
        }
        $totalPriceRmb = 
        $data['agentID'] = $this->agent['id'];
        $data['memberID'] = $this->user['id'];
        $data['order_no'] = getStoreOrderNo();
        $data['goodsMoney'] = $totalPrice;
        $data['money'] = $totalPrice+$totalYunfei;
        $data['rmb'] = number_format(($totalPrice+$totalYunfei)*$this->agent['huilv'], 2, '.', '');
        $data['payment'] = $totalYunfei;
        $data['yongjin'] = $yongjin;
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['jifen'] = 0;
        $data['payStatus'] = 0;
        $data['status'] = 0;
        $res = M('Order')->add( $data ); 
        if ($res) {
            //保存包裹
            $orderID = $res;
            foreach ($baoguo as $key => $value) {
                $aData = array(
                    'orderID'=>$orderID,
                    'order_no'=>$data['order_no'],
                    'memberID'=>$this->user['id'],
                    'agentID'=> $this->agent['id'],
                    'typeName'=>$value['name'],
                    'goodsMoney'=>$value['money'],
                    'payment'=>$value['yunfei'],
                    'money'=>$value['yunfei']+$value['money'],
                    'wuliu'=>'',
                    'wuliuNumber'=>'',
                    'name'=>$data['name'],
                    'mobile'=>$data['mobile'],
                    'address'=>$data['address'],
                    'remark'=>'',
                    'del'=>0,
                    'status'=>0,
                    'createTime'=>time(),
                    'updateTime'=>time()
                );
                $bID = M('OrderBaoguo')->add($aData);
                if ($bID) {
                    for ($i=0; $i < count($value['goods']); $i++) { 
                        //保存详单
                        $detail['baoguoID'] = $bID;
                        $detail['pid'] = $value['goods'][$i]['goodsID'];  
                        $detail['picname'] = $value['goods'][$i]['picname'];
                        $detail['pname'] = $value['goods'][$i]['pname'];
                        $detail['pram'] = $value['goods'][$i]['pram'];
                        $detail['number'] = $value['goods'][$i]['number'];
                        $detail['price'] = $value['goods'][$i]['price'];
                        $detail['money'] = $value['goods'][$i]['number']*$value['goods'][$i]['price'];
                        $detail['server'] = $value['goods'][$i]['server'];
                        $detail['fencheng1'] = $value['goods'][$i]['number']*$value['goods'][$i]['fencheng1'];
                        $detail['fencheng2'] = $value['goods'][$i]['number']*$value['goods'][$i]['fencheng2'];
                        $detail['order_no'] = $data['order_no'];
                        $detail['memberID'] = $this->user['id'];   
                        $detail['agentID'] = $this->agent['id'];
                        $detail['createTime'] = time();
                        M('OrderDetail')->add($detail);
                        M('Goods')->where('id='.$value['goods'][$i]['goodsID'])->setInc('sellNumber',$value['goods'][$i]['number']);
                        unset($detail);
                    }
                }
            }
            unset($map);
            $map['memberID'] = $this->user['id'];
            $map['sessionID'] = $this->sessionID;
            $map['_logic'] = 'OR';
            M("Cart")->where($map)->delete();
            $this->success('操作成功',U('Order/pay',['orderID'=>$orderID,'agentid'=>$this->agent['id'],'token'=>I('token')]));
        }else{
            $this->error('操作失败');
        }
    }

    //计算运费
    public function getYunfei($baoguo){
        $total = 0;
        foreach ($baoguo as $key => $value) {
            $money = 0;
            $weight = 0;
            if (count($value['goods'])==0) {
                unset($baoguo[$key]);
            }else{
                foreach ($value['goods'] as $k => $v) {
                    if ($v['isBaoyou']==0) {
                        $weight += $v['weight']*$v['number'];       
                    }
                    $money += $v['number'] * $v['price'];
                }
                $weight = $weight>=1 ? $weight : 1;
                $yunfei = $weight * $value['price'];
                $baoguo[$key]['yunfei'] = $yunfei;
                $baoguo[$key]['money'] = $money;
                $total += $yunfei;
            }            
        }
        return ['baoguo'=>$baoguo,'totalYunfei'=>$total];
    }

    public function get_filter_attr($goods_id_arr = array(), $filter_param, $action, $mode = 0)
    {
        $goods_id_str = implode(',', $goods_id_arr);
        $goods_id_str = $goods_id_str ? $goods_id_str : '0';
        $goods_attr = M('goods_attr')->where(['goods_id'=>['in',$goods_id_str],'attr_value'=>['<>','']])->select();
        // $goods_attr = M('goods_attr')->where("attr_value != ''")->select();
        $goods_attribute = M('goods_attribute')->where("attr_index = 1")->getField('attr_id,attr_name,attr_index');
        if (empty($goods_attr)) {
            if ($mode == 1) return array();
            return array('status' => 1, 'msg' => '', 'result' => array());
        }
        $list_attr = $attr_value_arr = array();
        $old_attr = $filter_param['attr'];
        foreach ($goods_attr as $k => $v) {
            // 存在的帅选不再显示
            if (strpos($old_attr, $v['attr_id'] . '_') === 0 || strpos($old_attr, '@' . $v['attr_id'] . '_'))
                continue;
            if ($goods_attribute[$v['attr_id']]['attr_index'] == 0)
                continue;
            $v['attr_value'] = trim($v['attr_value']);
            // 如果同一个属性id 的属性值存储过了 就不再存贮
            if (in_array($v['attr_id'] . '_' . $v['attr_value'], (array)$attr_value_arr[$v['attr_id']]))
                continue;
            $attr_value_arr[$v['attr_id']][] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_id'] = $v['attr_id'];
            $list_attr[$v['attr_id']]['attr_name'] = $goods_attribute[$v['attr_id']]['attr_name'];

            // 帅选参数
            if (!empty($old_attr))
                $filter_param['attr'] = $old_attr . '@' . $v['attr_id'] . '_' . $v['attr_value'];
            else
                $filter_param['attr'] = $v['attr_id'] . '_' . $v['attr_value'];

            $list_attr[$v['attr_id']]['attr_value'][] = array('key' => $v['attr_id'], 'val' => $v['attr_value'], 'attr_value' => $v['attr_value'], 'href' => U("Goods/$action", $filter_param, ''));
            //unset($filter_param['attr_id_'.$v['attr_id']]);
        }
        if ($mode == 1) return $list_attr;
        return array('status' => 1, 'msg' => '', 'result' => $list_attr);
    }
}