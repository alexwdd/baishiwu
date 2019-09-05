<?php
namespace V1\Controller;

class StoreController extends CommonController {

    public $user;
    public $agent;
    private $extendArea = ['新疆维吾尔自治区','西藏自治区'];

    public function _initialize() {
        parent::_initialize();
        $token = I('post.token');
        if (!$user = $this->checkToken($token)) {
            //returnJson('999','登录超时'); 
            $this->user = ['id'=>0];
        }else{
            $this->user = $user;
        }

        $agentID = I('agentid');
        if ($agentID=='' || !is_numeric($agentID)) {die;}
        $map['id'] = $agentID;
        //$map['show'] = 1;
        $agent = M('Agent')->where($map)->find();        
        if (!$agent) {die;}
        $this->agent = $agent;
    }

    public function getMain(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $banner = $this->getAd(1);
            $quick = $this->getAd(2);
            $ad = $this->getAd(3);
            if ($this->agent['hotkey']!='') {
                $hotkey = explode(",", $this->agent['hotkey']);
            }

            unset($map);
            $map['fid'] = 0;
            $map['comm'] = 1;
            $map['agentID'] = $this->agent['id'];
            $indexCate = M("DgCate")->field('id,path,name')->where($map)->select();
            foreach ($indexCate as $key => $value) {
                unset($map);
                $map['comm'] = 1;
                $map['show'] = 1;
                $map['agentID'] = $this->agent['id'];
                $map['path|path1'] = array('like',$value['path'].'%');
                $goods = M("DgGoodsIndex")->where($map)->order('sort asc,id desc')->select();
                foreach ($goods as $k => $val) {
                    $goods[$k]['picname'] = getRealUrl($val['picname']);
                    $goods[$k]['num'] = 0;
                    $goods[$k]['cartShow'] = true;
                    $goods[$k]['rmb'] = number_format($this->agent['huilv']*$val['price'],1);
                    if ($val['tag']>0) {
                        $goods[$k]['tagImg'] = C('site.domain').'/static/tag/tag'.$val['tag'].'.png';   
                    }
                }
                $indexCate[$key]['goods'] = $goods;
            }
            returnJson(0,'success',['banner'=>$banner,'ad'=>$ad,'quick'=>$quick,'hotkey'=>$hotkey,'notice'=>$this->agent['notice'],'goods'=>$indexCate]);      
        } 
    }

    public function getAd($type){
        $map['type'] = $type;
        $map['agentID'] = $this->agent['id'];
        $list = M('AgentAd')->cache(true)->where($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['image'] = getRealUrl($value['image']);
        }
        return $list;
    }

    public function goods(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $path = I('post.path');
            $cid = I('post.cid');
            $keyword = I('post.keyword');
            $brandID = I('post.brandID');
            $page = I('post.page',1);

            $map['show'] = 1;
            $map['agentID'] = $this->agent['id'];
            if ($keyword!='') {
                $map['name|short|keyword'] = array('like','%'.$keyword.'%');
            }

            if ($brandID!='') {
                $map['brandID'] = $brandID;
            }

            if ($cid!='') {
                $map['cid'] = $cid;
            }

            if ($path!='') {
                $map['path'] = array('like',$path.'%');
            }

            $pagesize =10;
            $firstRow = $pagesize*($page-1); 

            $obj = M('DgGoodsIndex');
            $count = $obj->where($map)->count();
            $totalPage = ceil($count/$pagesize);
            if ($page < $totalPage) {
                $next = 1;
            }else{
                $next = 0;
            }

            $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('sort asc,id desc')->select();
            foreach ($list as $k => $value) {
                $list[$k]['picname'] = getRealUrl($value['picname']);
                $list[$k]['num'] = 0;
                $list[$k]['cartShow'] = true;
                $list[$k]['rmb'] = number_format($this->agent['huilv']*$value['price'],1);
                if ($value['tag']>0) {
                    $list[$k]['tagImg'] = C('site.domain').'/static/tag/tag'.$value['tag'].'.png';   
                }
            }
            returnJson(0,'success',['next'=>$next,'data'=>$list]);
        }
    }

    public function cateGoods(){
        if (IS_POST) {
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $cid = I('post.cid');

            if($cid==''){
                returnJson('-1','参数错误');
            }

            $cid = I('post.cid');
            $path = I('post.path');
            $map['id'] = $cid;
            $map['agentID'] = $this->agent['id'];
            $thisCate = M("DgCate")->where($map)->find();
            if(!$thisCate){
                returnJson('-1','分类不存在');
            }

            unset($map);
            $map['fid'] = $thisCate['id'];
            $cate = M("DgCate")->where($map)->select();

            if (!$cate) {
                $cate = [$thisCate];
            }

            foreach ($cate as $key => $value) {
                unset($map);
                $map['show'] = 1;
                $map['agentID'] = $this->agent['id'];
                $map['cid'] = $value['id'];
                $goods = M('DgGoodsIndex')->where($map)->limit($firstRow.','.$pagesize)->order('sort asc,id desc')->select();

                foreach ($goods as $k => $val) {
                    $goods[$k]['picname'] = getRealUrl($val['picname']);
                    $goods[$k]['num'] = 0;
                    $goods[$k]['cartShow'] = true;
                    $goods[$k]['rmb'] = number_format($this->agent['huilv']*$val['price'],1);
                    if ($val['tag']>0) {
                        $goods[$k]['tagImg'] = C('site.domain').'/static/tag/tag'.$val['tag'].'.png';   
                    }
                }

                $cate[$key]['goods'] = $goods;
            }
            returnJson(0,'success',['cate'=>$thisCate,'data'=>$cate]);            
        }
    }

    public function getCateName(){
        if(IS_POST){
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $cid = I('post.cid');
            $path = I('post.path');
            if ($cid!='') {
                $map['cid'] = $cid;
            }

            if ($path!='') {
                $map['path'] = $path;
            }
            $map['agentID'] = $this->agent['id'];
            $cate = M("DgCate")->where($map)->find();
            unset($map);
            $map['fid'] = $cate['id'];
            $child = M("DgCate")->where($map)->select();
            foreach ($child as $key => $value) {
                $child[$key]['index'] = $key+1;
            }
            returnJson('0','success',['cate'=>$cate,'child'=>$child]);
        }
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
            $list['rmb'] = number_format($this->agent['huilv']*$list['price'],1);
            returnJson(0,'success',['goods'=>$list,'server'=>$server,'spec'=>$spec,'thisSpec'=>$thisSpec]);
        }
    }

    public function cart(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $map['memberID'] = $this->user['id'];
            $list = M("DgCart")->where($map)->order('typeID asc,number desc')->select();
            $total = 0;
            $weight = 0;
            foreach ($list as $key => $value) {
                $goods = M('DgGoodsIndex')->where('id='.$value['itemID'])->find(); 
                $goods['picname'] = getRealUrl($goods['picname']);
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
            $serverMoney = $serverMoney * $value['goodsNumber'] * $goods['number'];
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
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
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

    //设置购物车数量
    public function setCartNumber(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');

            $number = I('post.number');
            $map['id'] = I('post.cartID');
            $map['memberID'] = $this->user['id'];
            $obj = M('DgCart');
            $list = $obj->where($map)->find();
            
            $data['goodsNumber'] = ($list['goodsNumber']/$list['number'])*$number;
            $data['number'] = $number;
            $obj->where($map)->save($data); 
            $heji = fix_number_precision($this->getCartNumber($this->user),2); 
            returnJson(0,'success',['heji'=>$heji]);
        }
    }

    //购物车数量
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
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            if(!checkFormDate()){returnJson('-1','ERROR');}       
            $count = M("DgCart")->where(array('memberID'=>$this->user['id']))->delete();
            returnJson(0,'success'); 
        }
    }

    //删除购物车
    public function cartDel(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            if(!checkFormDate()){returnJson('-1','ERROR');}           
            $id = I('post.id');

            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }

            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $res = M('DgCart')->where($map)->delete();
            if ($res) {
                $heji = $this->getCartNumber($this->user);
                returnJson(0,'success',['heji'=>$heji]);
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    public function getYunfei(){
        if (IS_POST) {
            $kid = I("post.kid");
            $data = $this->getYunfeiJson($this->user,$kid);
            returnJson(0,'success',['data'=>$data]);
        }
    }

    public function getYunfeiJson($user,$kid,$province=null){
        $kuaidi = M('Wuliu')->where('id',$kid)->find();
        if (!$kuaidi) {
            returnJson('-1','快递公司不存在');
        }
        $baoguoArr1 = [];
        $map['memberID'] = $user['id']; 
        $list = M("DgCart")->where($map)->order('typeID asc,number desc')->select();
        foreach ($list as $key => $value) {
            $goods = M('DgGoodsIndex')->where('id='.$value['itemID'])->find(); 

            $list[$key]['goodsID'] = $goods['goodsID'];
            $list[$key]['name'] = $goods['name'];
            $list[$key]['short'] = $goods['short'];
            $list[$key]['wuliuWeight'] = $goods['wuliuWeight'];            
            $list[$key]['weight'] = $goods['weight'];            
            $list[$key]['price'] = $goods['price'];            
            $list[$key]['singleNumber'] = $goods['number'];             
            if ($goods['wuliu']!='') { //套餐类的先处理掉
                for ($i=0; $i < $value['number']; $i++) { 
                    $brandName = getBrandName($goods['typeID']);
                    $list[$key]['goodsNumber'] = $goods['number'];

                    $danjia = getDanjia($goods['typeID'],$user);

                    if ($this->inExtendArea($province)) {                        
                        $extend = $goods['wuliuWeight']*$goods['number']*$danjia['otherPrice'];
                    }else{
                        $extend = 0;
                    }
                    $sign=0;
                    if ($value['server']!=''){//包含签名
                        $ids = explode(",", $value['server']);
                        if (in_array(2,$ids)) {
                            $sign=1; 
                        }                           
                    }
                    $baoguo = [
                        'type'=>$goods['typeID'],
                        'totalNumber'=>$goods['number'],
                        'totalWeight'=>$goods['weight']*$goods['number'],
                        'totalWuliuWeight'=>$goods['wuliuWeight']*$goods['number'],
                        'yunfei'=>0,
                        'inprice'=>$goods['wuliuWeight']*$goods['number']*$danjia['inprice'],
                        'extend'=>$extend,
                        'sign'=>$sign,
                        'kuaidi'=>$brandName.'(包邮)',
                        'goods'=>array($list[$key]),
                    ];
                    array_push($baoguoArr1,$baoguo);
                }
                unset($list[$key]);
            }
        } 
        if ($list) {
            import("Common.ORG.Zhongyou");
            $cart = new \cart\Zhongyou($list,$kuaidi,$province,$user);
            $baoguoArr2 = $cart->getBaoguo();
            $baoguoArr = array_merge($baoguoArr1,$baoguoArr2);
        }else{
            $baoguoArr =$baoguoArr1;
        }        
        $totalWeight = 0;
        $totalWuliuWeight = 0;
        $totalPrice = 0;
        $totalExtend = 0;
        $totalInprice = 0;
        foreach ($baoguoArr as $key => $value) {
            $server = [];
            foreach ($value['goods'] as $k => $val) {
                if ($val['server']) {
                    $val['server'] = explode(",", $val['server']);
                    $server = array_merge($server,$val['server']);
                    $server = array_unique($server);
                }
            }
            $baoguoArr[$key]['serverIds'] = implode(",",$server);

            $totalWeight += $value['totalWeight'];
            $totalWuliuWeight += $value['totalWuliuWeight'];
            $totalPrice += $value['yunfei'];
            $totalExtend += $value['extend'];
            $totalInprice += $value['inprice'];
        }
        $data = [
            'totalWeight'=>fix_number_precision($totalWeight,2),
            'totalWuliuWeight'=>fix_number_precision($totalWuliuWeight,2),
            'totalPrice'=>fix_number_precision($totalPrice,2),
            'totalExtend'=>fix_number_precision($totalExtend,2),
            'totalInprice'=>fix_number_precision($totalInprice,2),
            'baoguo'=>$baoguoArr
        ];     
        return $data;
    }

    //判断是否在偏远地区
    private function inExtendArea($province){        
        if (in_array($province,$this->extendArea)) {
            return true;
        }else{
            return false;
        }
    }

    //创建订单
    public function create(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            $kid = I("post.kid");
            if ($kid=='' || !is_numeric($kid)) {
                returnJson('-1','参数错误');
            }

            $map['memberID'] = $this->user['id'];
            $count = M("DgCart")->where($map)->count();
            if ($count==0) {
                returnJson('-1','购物车中没有商品');
            }

            //收件信息
            $map['memberID'] = $this->user['id'];
            $address = M('Address')->where($map)->order('def desc , id desc')->find();

            //发收人信息
            $map['memberID'] = $this->user['id'];
            $sender = M('Sender')->where($map)->order('def desc , id desc')->find();

            //包裹信息
            $result = $this->getYunfeiJson($this->user,$kid,$address['province']);
            $baoguo = $result;
           
            $money = $this->getCartNumber($this->user);

            $total = $baoguo['totalPrice']+$baoguo['totalExtend']+$money['total'];
       
            //是否包含签名
            $flag = 0;//货物签名
            foreach ($baoguo['baoguo'] as $key => $value) {
                foreach ($value['goods'] as $k => $val) {
                    if ($flag==0 && $val['server']!='') {
                        if (strstr($val['server'], '2')) {
                            $flag = 1;
                            break;
                        }
                    }
                }
                if ($flag==1) {
                    break;
                }
            }        
            returnJson(0,'success',['address'=>$address,'sender'=>$sender,'baoguo'=>$baoguo,'money'=>$money,'total'=>$total,'flag'=>$flag]);
        }
    }

    //保存订单
    public function doSubmit(){
        if (!IS_POST) {die;}
        if($this->user['id']==0){
            returnJson('999','请先登录');
        }

        $rate = $this->agent['huilv'];
        if ($rate=='') {
            returnJson('-1','无法获得当前汇率');
        }

        $sender = I("post.sender");
        if ($sender=='') {
            returnJson('-1','请选择寄件人');
        }
        $map['memberID'] = $this->user['id'];
        $list = M("DgCart")->where($map)->select();
        if (!$list) {
            returnJson('-1','没有选择任何商品');
        }

        $hongjiu = 0;
        $chengben = 0;//商品总成本
        foreach ($list as $key => $value) {
            $goods = M('DgGoodsIndex')->where(array('id'=>$value['itemID']))->find();
            $goodsInprice = M("DgGoods")->where(array('id'=>$value['goodsID']))->getField("inprice");
            $chengben += $goodsInprice * $value['goodsNumber'];
            if ($goods) {
                if ($goods['empty']==1) {
                    returnJson('-1','商品【'.$goods['name'].'】库存不足');
                }
            }else{
                returnJson('-1','商品【'.$goods['name'].'】已经下架');
            }
            if ($value['typeID']==12) {
                $hongjiu += $value['goodsNumber'];
            }
        }

        if ($hongjiu%2 != 0) {
            returnJson('-1',"商品中包含红酒，红酒数量必须为偶数");
        }

        //创建订单
        $data = I('post.');

        $cart = $this->getCartNumber($this->user);
        $totalPrice = $cart['total'];
        $serverMoney = $cart['serverMoney'];

        if ($data['kid']>0) { 
            $result = $this->getYunfeiJson($this->user,$data['kid'],$data['province']);
            $baoguo = $result;
            $totalYunfei = $baoguo['totalPrice']+$baoguo['totalExtend'];
            $totalInprice = $baoguo['totalInprice'];
        }else{
            returnJson('-1',"请选择快递");
        }

        $flag = 0;
        foreach ($baoguo['baoguo'] as $key => $value) {
            $ids = explode(",", $value['serverIds']);
            if (in_array(2,$ids)) {
                $flag = 1;
                break;
            }
        }

        if ($flag==1 && $data['sign']=='') {
            returnJson('-1',"请输入签名");
        }

        $sender = explode(",", $data['sender']);
        $money = $totalPrice+$totalYunfei;

        $realMoney = $totalPrice+$totalYunfei;
        $zhekou = $this->agent['discount'];
        if($zhekou > 0){
            $total = $realMoney * $zhekou/10;
        }else{
            $total = $realMoney;
        }

        $order_no = $this->getOrderNo();
        $data['agentID'] = $this->agent['id'];
        $data['sender'] = $sender[0];
        $data['senderMobile'] = $sender[1];
        $data['memberID'] = $this->user['id'];
        $data['memberMobile'] = $this->user['mobile'];        
        $data['order_no'] = $order_no;
        $data['total'] = $total;
        $data['discount'] = $zhekou;
        $data['serverMoney'] = $serverMoney;
        $data['rmb'] = $rate * $total;
        $data['goodsMoney'] = $totalPrice;
        $data['money'] = 0;
        $data['wallet'] = 0;
        $data['inprice'] = $chengben;
        $data['payment'] = $totalYunfei;
        $data['wuliuInprice'] = $totalInprice;
        $data['payType'] = 0;
        $data['payStatus'] = 0;
        $data['createTime'] = time();
        $data['updateTime'] = time();
        $data['status'] = 0;
        $orderID = M('DgOrder')->add($data);
        if (!$orderID) {
            returnJson('-1','订单创建失败');
        }

        $data['orderID'] = $orderID;        
        $data['status'] = 0;        
        $personID = M('DgOrderPerson')->add($data);
        foreach ($baoguo['baoguo'] as $key => $value) {
            //保存详单
            $detail['agentID'] = $this->agent['id'];
            $detail['orderID'] = $orderID;
            $detail['personID'] = $personID;
            $detail['order_no'] = $data['order_no'];
            $detail['memberID'] = $this->user['id'];  
            $detail['payment'] = $value['yunfei'];
            $detail['wuliuInprice'] = $value['inprice'];//物流成本
            $detail['type'] = $value['type'];
            $detail['weight'] = $value['totalWuliuWeight'];
            $detail['kuaidi'] = $value['kuaidi'];
            $detail['serverIds'] = $value['serverIds'];
            $detail['kdNo'] = '';
            $detail['name'] = $data['name'];
            $detail['mobile'] = $data['mobile'];
            $detail['province'] = $data['province'];            
            $detail['city'] = $data['city'];
            $detail['area'] = $data['area'];
            $detail['address'] = $data['address'];
            $detail['sender'] = $data['sender'];
            $detail['senderMobile'] = $data['senderMobile'];
            if ($value['sign']==1) {
                $detail['sign'] = $data['sign'];
            }else{
                $detail['sign'] = '';
            }
            $detail['createTime'] = time();
            if ($payStatus==2) {
                $detail['status'] = 1;
            }else{
                $detail['status'] = 0;
            }
            $detail['snStatus'] = 0;
            $detail['del'] = 0;
            $baoguoID = M('DgOrderBaoguo')->add($detail);
            if ($baoguoID) {
                foreach ($value['goods'] as $k => $val) {   
                    $gData = [
                        'agentID'=>$this->agent['id'],
                        'orderID'=>$orderID,
                        'memberID'=>$this->user['id'],
                        'baoguoID'=>$baoguoID,
                        'goodsID'=>$val['goodsID'],
                        'itemID'=>$val['itemID'],
                        'name'=>$val['name'],
                        'short'=>$val['short'],
                        'number'=>$val['goodsNumber'],    
                        'trueNumber'=>$val['goodsNumber'],    
                        'price'=>$val['price'],
                        'server'=>$val['server'],
                        'extends'=>$val['extends'],
                        'del'=>0,
                        'createTime'=>time()
                    ];
                    M('DgOrderDetail')->add($gData);      
                }
            }
            unset($detail);
        }
        unset($map);
        $map['memberID'] = $this->user['id'];
        M("DgCart")->where($map)->delete();
        returnJson(0,'success',$order_no);           
    }

    public function orderInfo(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            $order_no = I('post.order_no');
            $map['order_no'] = $order_no;
            $map['memberID'] = $this->user['id'];
            $list = M('DgOrder')->where($map)->find();
            if ($list){
                if ($list['payStatus']>0) {
                    returnJson('-1','该订单已支付完成，不要重复支付');
                }
                $list['createTime'] = date("Y-m-d H:i:s",$list['createTime']);
                $shouxufei = C('site.shouxufei');
                $huilv = $this->agent['huilv'];
                returnJson(0,'success',['data'=>$list,'huilv'=>$huilv,'shouxufei'=>$shouxufei]);
                return view();
            }else{  
                returnJson('-1','没有该订单');
            }
        }
    }

    public function getOrderNo(){
        $order_no = getStoreOrderNo();
        $map['order_no'] = $order_no;
        $count = M("DgOrder")->where($map)->count();
        if ($count>0) {
            return $this->getOrderNo();
        }
        return $order_no;
    }

    public function getCard(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            $map['agentID'] = $this->agent['id'];
            $list = M('Card')->where($map)->select();
            returnJson(0,'success',$list);
        }
    }

    public function cardPay(){
        if (IS_POST) {
            if($this->user['id']==0){
                returnJson('999','请先登录');
            }
            $data['image'] = I("post.image");
            $id = I("post.id");
            $data['cardID'] = I("post.cardID");
            if ($id=='' || !is_numeric($id)) {
                returnJson('-1','参数错误');
            }
            if ($data['cardID']=='') {
                returnJson('-1','请选择收款银行卡');
            }
            if ($data['image']=='') {
                returnJson('-1','请上传支付截图');
            }

            $result = $this->base64_upload($data['image']);
            if ($result) {
                $data['image'] = $result['url'];
            }else{
                returnJson('-1','图片保存失败');
            }

            $data['payType'] = 3;
            $data['payStatus'] = 1;

            $map['memberID'] = $this->user['id'];
            $map['payStatus'] = 0;
            $map['id'] = $id;
            $res = M("DgOrder")->where($map)->save($data);
            if ($res) {
                returnJson(0,'success');
            }else{
                returnJson('-1','操作失败');
            }
        }
    }

    public function category(){
        if(IS_POST){
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $fid = I('post.fid');
            $map['fid'] = $fid;
            $map['agentID'] = $this->agent['id'];
            $list = M('DgCate')->where($map)->order('sort asc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['index'] = $key+1;
                $list[$key]['picname'] = getRealUrl($value['picname']);
            }
            returnJson('0','success',$list);
        }
    }

    public function brand(){
        if(IS_POST){
            if(!checkFormDate()){returnJson('-1','ERROR');}
            $map['agentID'] = $this->agent['id'];
            $brand = M('Brand')->where($map)->order('sort asc,id desc')->select();
            returnJson('0','success',$brand);
        }
    }
}