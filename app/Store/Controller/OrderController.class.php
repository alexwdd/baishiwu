<?php
namespace Store\Controller;

class OrderController extends UserController
{
    public function index()
    {   
        $payStatus = I('payStatus');
        $auth = I('auth');

        switch ($payStatus) {
            case '0':
                $pageName = '待付款订单';
                break;
            case '1':
                $pageName = '待审核订单';
                break;
            case '2':
                $pageName = '待发货订单';
                break;
            case '3':
                $pageName = '已发货订单';
                break;
            default:
                $pageName = '全部订单';
                break;
        }                

        //查询数据
        /*$list = M('Order')->where($map)->order('id desc')->paginate(10,true)->each(function($item, $key){
            $order_no = $item["order_no"]; //获取数据集中的id
            $goods = M('OrderDetail')->where("order_no='$order_no'")->select(); //根据ID查询相关其他信息
            $item['goods'] = $goods; //给数据集追加字段num并赋值
            return $item;
        });
        $page = $list->render();
         */ 
        $this->assign('auth',$auth);  
        $this->assign('payStatus',$payStatus);  
        $this->assign('pageName',$pageName);
        $this->display();
    }

    public function ajaxOrder(){
        $payStatus = I('payStatus');
        $auth = I('auth');
        $page = I('post.page',1);

        if ($auth==1) {
            $map = "(front='' or back='' or sn='') and del=0 and memberID=".$this->user['id'];
        }else{
            if ($payStatus!='' && is_numeric($payStatus)) {
                $map['payStatus'] = $payStatus;
            }
            $map['memberID'] = $this->user['id'];
            $map['del'] = 0;
        }

        $pagesize = 10;
        $firstRow = $pagesize*($page-1); 

        $obj = M('Order');
        $count = $obj->where($map)->count();
        $totalPage = ceil($count/$pagesize);
        if ($page < $totalPage) {
            $next = 1;
        }else{
            $next = 0;
        }
        $list = $obj->where($map)->limit($firstRow.','.$pagesize)->order('id desc')->select();
        foreach ($list as $key => $value) {
            $order_no = $value["order_no"]; //获取数据集中的id
            $goods = M('OrderDetail')->where("order_no='$order_no'")->select(); //根据ID查询相关其他信息
            $list[$key]['goods'] = $goods; //给数据集追加字段num并赋值
        }
        $this->assign('list',$list);  
        $res = $this->fetch();
        echo json_encode(['next'=>$next,'data'=>$res]);
    }

    public function detail(){
        $id = I('id');
        $map['id'] = $id;
        $map['del'] = 0;
        $map['memberID'] = $this->user['id'];
        $list = M('Order')->where($map)->find();
        if ($list) {
            $list['baoguo'] = M('OrderBaoguo')->where(array('order_no'=>$list['order_no']))->select();
            foreach ($list['baoguo'] as $key => $value) {

                $goods = M('OrderDetail')->where(array('baoguoID'=>$value['id']))->select();
                foreach ($goods as $k => $v) {
                    if ($v['server']!='') {
                        $serverID = explode(",",$v['server']);
                        unset($map);
                        $map['id'] = array('in',$serverID);
                        $server = M("server")->where($map)->select();           
                        $goods[$k]['serverArr'] = $server;
                    }
                }
                $list['baoguo'][$key]['goods'] = $goods;
                $wuliu = M('OrderWuliu')->where(array('baoguoID'=>$value['id']))->select();      
                foreach ($wuliu as $k => $v) {
                    if ($v['image']!='') {
                        $wuliu[$k]['image'] = explode(",", $v['image']);
                    }
                    if ($v['wuliu']!='') {
                        $wuliu[$k]['wuliuUrl'] = M('Wuliu')->where(array('name'=>$v['wuliu']))->getField('url');
                    }
                }
                $list['baoguo'][$key]['wuliu'] = $wuliu;               
            }
            if ($list['card']>0) {
                $list['pay'] = M("Card")->where('id='.$list['card'])->getField("name");
            }else{
                if ($list['payType']==1) {
                    $list['pay'] = '支付宝';
                }else{
                    $list['pay'] = '微信支付';
                }
            }
            
            $this->assign('list',$list);
            $this->display();
        }else{
            $this->error("没有该订单");
        }        
    }

    public function shouhuo(){
        $id = I('id');
        $map['id'] = $id;
        $map['memberID'] = $this->user['id'];
        $map['del'] = 0;
        M('OrderBaoguo')->where($map)->setField('status',2);
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }

    public function cancel(){
        $id = I('id');
        $map['id'] = $id;
        $map['memberID'] = $this->user['id'];
        $map['del'] = 0;
        M('Order')->where($map)->setField('del',1);
        header('Location:'.$_SERVER['HTTP_REFERER']);
    }

    public function save(){
        if (IS_POST) {
            $front = I('post.front');
            $back = I('post.back');
            $sn = I('post.sn');
            $id = I('post.id');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }
            if ($front=='' || $back=='') {
                $this->error('请上传身份证正反面');
            }
            if ($sn=='') {
                $this->error('请输入身份证号');
            }

            //保存地址
            $mobile = I('post.mobile');
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
                $map['mobile'] = $mobile;
                $map['memberID'] = $this->user['id'];
                M("Address")->where($map)->save($address);
            }

            unset($map);
            $data = ['front'=>$front,'back'=>$back,'sn'=>$sn];
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $res = M('Order')->where($map)->save($data);
            if ($res) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    public function pay(){
        $id = I('orderID');
        $map['id'] = $id;
        $map['del'] = 0;
        $map['memberID'] = $this->user['id'];
        $list = M('Order')->where($map)->find();
        if ($list) {
            $pay = M('Card')->where('id='.$list['card'])->find();
            $this->assign('pay',$pay);

            $agent = M('Agent')->where("id=".$list['agentID'])->find();

            $shareUrl = 'http://' . $_SERVER['HTTP_HOST'] . U('share/pay',['orderID'=>$id]);

            $this->assign('shareUrl',$shareUrl);

            $this->assign('list',$list);
            if ($agent['pay']==0) {
                $this->display('card');
            }else{
                $this->display();
            }
            
        }else{
            $this->error("没有该订单");
        }        
    }

    public function dopay(){
        if (IS_POST) {
            $jietu = I('post.jietu');
            $id = I('post.id');
            if ($id=='' || !is_numeric($id)) {
                $this->error('参数错误');
            }
            if ($jietu=='') {
                $this->error('请上传付款截图');
            }

            $data['jietu'] = $jietu;
            $data['payType'] = 3;
            $data['status'] = 1;
            /*if ($payStatus==0) {
                $data['payStatus'] = 1;
            }*/
            $map['id'] = $id;
            $map['memberID'] = $this->user['id'];
            $res = M('Order')->where($map)->save($data);
            if ($res) {
                $this->success('操作成功',U('order/index',array('token'=>I('token'))));
            }else{
                $this->error('操作失败');
            }
        }
    }

    public function wuliu(){
        $this->assign('url',I('get.url'));
        $this->display();
    }
}
