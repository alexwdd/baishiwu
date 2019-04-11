<?php
namespace Adminx\Controller;

class TuanController extends AdminController {

	#文章列表
	public function index() {		
        if (IS_POST) {
            $keyword = I('keyword');
            $status = I('status');
            $auth = I('auth');
            $goodstype = I('goodstype');
            $type = I('type');
            $cityID = I('cityID');

            if ($goodstype!='') {
                $map['goodstype'] = $goodstype;
            }

            if ($cityID!='') {
                $map['cityID'] = $cityID;
            }

            if ($type!='') {
                $map['type'] = $type;
            }

            if ($status!='') {
                $map['status'] = $status;
            }

            if ($keyword!='') {
                $map['order_no|contact|phone'] = $keyword;
            }

            $startDate  = I('startDate');
            $endDate  = I('endDate');
            if($startDate!='' && $endDate!=''){
                $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
            }

            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            
            $obj = M('Tuan');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','articleid');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
                $localWeight = M('TuanDetail')->where(array('articleid'=>$value['articleid']))->sum('weight');
                $localWeight = $localWeight?$localWeight:0;
                $list[$key]['localWeight'] = $localWeight;

                $currentWeight = 0;
                $num = 0;
                $baoguo = M('TuanDetail')->field('weight_a')->where(array('articleid'=>$value['articleid']))->select();
                for ($i=0; $i < count($baoguo); $i++) { 
                    $currentWeight += $baoguo[$i]['weight_a'];
                    $num++;
                }
                $list[$key]['currentWeight'] = $currentWeight;
                $list[$key]['num'] = $num;
            }

            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>$pageNum,
                    "pageSize"=>$pageSize,
                    "pages"=>$pageSize,
                    "total"=>$total
                )
            );
            echo $this->return_json($result);
        }else{
            $this->display();
        }
	}	

    public function add(){
        if($_POST){
            $obj = D('Tuan');
            if ($data = $obj->create()) {
                $data['auth'] = 1;
                $data['order_no'] = getOrderNo('P');
                $data['title'] = '管理员发起拼邮，包裹下限'.$data['maxWeight'].'公斤';
                if ($list = $obj->add($data)) { 
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error($obj->getError());
            }            
        }else{
            $this->display();
        }
    }

    public function edit(){
        if($_POST){
            $obj = D('Tuan');
            if ($data = $obj->create()) {
                if ($list = $obj->save($data)) { 
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error($obj->getError());
            }            
        }else{
            $articleid = (int) I('get.id');
            if (!isset ($articleid)) {
                $this->error('参数错误');
            }
            $obj = M('Tuan');
            $list = $obj->where('articleid=' . $articleid)->find();
            $this->assign('list', $list);

            $this->display();
        }
    }

    public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $articleid = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($articleid)) {
            $this->error('ID不能为空！');
        }
        $user = M('Tuan');
        $map['articleid'] = $articleid;
        $rs=$user->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $user->where(array('articleid'=>$articleid))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

	#查看订单
    public function view() {
        if ($_POST) {
            $articleid = I('get.articleid');           
            $map['articleid'] = $articleid;
            $obj = M('TuanDetail');
            $field = I('post.field','goodsid');
            $order = I('post.order','desc'); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
            }
            $result = array(
                'data'=>$list
            );
            echo $this->return_json($result);
        }else{
            $articleid = I('get.id');
            $map['articleid'] = $articleid;
            $list = M('Tuan')->where($map)->find();
            if ($list) {
                $this->assign('list',$list);
                $this->display();
            }else{
                $this->error("没有该订单");
            }
        }
    }

    public function goodsStatus(){
        if (!IS_POST) {E('页面不存在！');}
        $goodsid = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($goodsid)) {
            $this->error('ID不能为空！');
        }
        $user = M('TuanDetail');
        $map['goodsid'] = $goodsid;
        $rs=$user->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $user->where(array('goodsid'=>$goodsid))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

    public function delGoods(){
        $goodsid = explode(",",I('post.goodsid'));
        if($goodsid==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['goodsid'] = array('in',$goodsid);
            $obj = M('TuanDetail');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    //包裹移动
    public function move(){
        if ($_POST) {
            $map['status'] = array('lt',2);
            if ($keyword!='') {
                $map['order_no|contact|phone'] = $keyword;
            }
            $obj = M('Tuan');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','articleid');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
                $localWeight = M('TuanDetail')->where(array('articleid'=>$value['articleid']))->sum('weight');
                $localWeight = $localWeight?$localWeight:0;
                $list[$key]['localWeight'] = $localWeight;
            }

            $result = array(
                'data'=>array(
                    'list'=>$list,
                    "pageNum"=>$pageNum,
                    "pageSize"=>$pageSize,
                    "pages"=>$pageSize,
                    "total"=>$total
                )
            );
            echo $this->return_json($result);
        }else{
            $goodsid = I('get.goodsid');
            $this->assign('goodsid',$goodsid);
            $this->display();
        }        
    }

    public function movesave(){
        if (!IS_POST) {E('页面不存在！');}
        $articleid = I('post.articleid');
        $goodsid = I('post.goodsid');

        if ($articleid=='' || !is_numeric($articleid)) {
            $this->error('参数错误');
        }

        $tuan = M('Tuan')->where(array('articleid'=>$articleid))->find();
        if (!$tuan) {
            $this->error('拼团信息不存在');
        }

        if ($tuan['status']>1) {
            $this->error('该团已截至不允许添加');
        }

        $goodsid = explode("-", $goodsid);
        if (count($goodsid)==0) {
            $this->error('请选择包裹');
        }

        $map['goodsid'] = array('in',$goodsid);
        $data['articleid'] = $tuan['articleid'];
        $data['createID'] = $tuan['createID'];
        $data['tuan_order_no'] = $tuan['order_no'];
        $r = M('TuanDetail')->where($map)->save($data);
        if ($r) {
            $notComeNumber =  M('TuanDetail')->where(array('articleid'=>$tuan['articleid'],'status'=>0))->count();
            if ($notComeNumber==0) {//都已入库
                //拼团总重量是否达到
                $localWeight = M('TuanDetail')->where(array('articleid'=>$tuan['articleid']))->sum('weight_a');
                $localWeight = $localWeight?$localWeight:0;
                if ($localWeight >= $tuan['maxWeight']) {
                    M('Tuan')->where(array('articleid'=>$tuan['articleid']))->setField("status",1);
                }
            }

            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }        
    }

    #删除废弃订单
    public function del() {
        $id = explode(",",I('post.articleid'));
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            foreach ($id as $key => $value) {
                $map['articleid'] = $value;
                $list = M('Tuan')->where($map)->find();
                if ($list) {
                    M('TuanDetail')->where(array('articleid'=>$list['articleid']))->delete();
                }
                M('Tuan')->where($map)->delete();
            }

            $this->success('操作成功');
        }
    }

    public function export(){
        $articleid = I('get.articleid');
        $danjia = I('get.danjia',0);
        $huilv = I('get.huilv',1);
        $map['articleid'] = $articleid;
        $list = M('TuanDetail')->where($map)->select();
        foreach ($list as $key => $value) {
            $list[$key]['money'] = round($value['weight_a']*$danjia,2);
            $list[$key]['aoyuan'] = round($list[$key]['money']/$huilv,2);
            $list[$key]['contact'] = $this->replaceEmoji($list[$key]['contact']);
            $list[$key]['title'] = $this->replaceEmoji($list[$key]['title']);
            $list[$key]['detail'] = $this->replaceEmoji($list[$key]['detail']);
        }

        import("Common.ORG.PHPExcel");
        $objPHPExcel = new \PHPExcel();       
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '包裹编号')                 
            ->setCellValue('B1', '标题')                 
            ->setCellValue('C1', '预估重量')
            ->setCellValue('D1', '实际重量')
            ->setCellValue('E1', '应收RMB')
            ->setCellValue('F1', '应收澳元')
            ->setCellValue('G1', '汇率')
            ->setCellValue('H1', '是否收费')
            ->setCellValue('I1', '团员')
            ->setCellValue('J1', '电话')
            ->setCellValue('K1', '微信')
            ->setCellValue('L1', '国内物流公司')
            ->setCellValue('M1', '物流单号')
            ->setCellValue('N1', '留言')
            ->setCellValue('O1', '发布日期');
        foreach($list as $k => $v){
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['goodsid'])                
                ->setCellValue('B'.$num, $v['title'])                
                ->setCellValue('C'.$num, $v['weight'])
                ->setCellValue('D'.$num, $v['weight_a'])                 
                ->setCellValue('E'.$num, $v['money'])                 
                ->setCellValue('F'.$num, $v['aoyuan'])                 
                ->setCellValue('G'.$num, $huilv)                 
                ->setCellValue('H'.$num, '')                 
                ->setCellValue('I'.$num, $v['contact'])
                ->setCellValue('J'.$num, $v['phone'])
                ->setCellValue('K'.$num, $v['wechat'])
                ->setCellValue('L'.$num, $v['company'])
                ->setCellValue('M'.$num, ' '.$v['order'])
                ->setCellValue('N'.$num, $v['detail'])
                ->setCellValue('O'.$num, date("Y-m-d H:i:s",$v['createTime']));
        }

        $objPHPExcel->getActiveSheet()->setTitle('团单');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="团单'.$articleid.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); 
    }

    public function make(){
        $articleid = I('post.articleid');
        $danjia = I('post.danjia',0);
        $huilv = I('post.huilv',1);

        $map['articleid'] = $articleid;
        M('TuanBill')->where($map)->delete();//删除旧账单
        
        $userArr = M('TuanDetail')->where($map)->group('userid')->getField("userid",true);

        foreach ($userArr as $key => $value) {
            unset($map);
            $map['articleid'] = $articleid;
            $map['userid'] = $value;
            $weight = M('TuanDetail')->where($map)->sum('weight_a');
            $money = round($weight*$danjia,2);
            $aoyuan = round($money/$huilv,2);
            $data = [
                'order_no'=>getOrderNo("B"),
                'articleid'=>$articleid,
                'weight'=>$weight,
                'money'=>$money,
                'aoyuan'=>$aoyuan,
                'danjia'=>$danjia,
                'huilv'=>$huilv,
                'userid'=>$value,
                'status'=>0,
                'createTime'=>time()
            ];
             M('TuanBill')->add($data);
        }
        $this->success("操作成功");
    }

    #查看订单
    public function bill() {
        $articleid = I('get.id');
        $map['articleid'] = $articleid;
        $list = M('Tuan')->where($map)->find();
        if ($list) {
            $obj = M('TuanBill');
            $bill = $obj->where($map)->order('id asc')->select();
            foreach ($bill as $key => $value) {
                $bill[$key]['nickname'] = M('Member')->where(array('id'=>$value['userid']))->getField('nickname');
                if ($value['payType']==1) {
                    $list[$key]['pay'] = '支付宝';
                }elseif($value['payType']==2){
                    $list[$key]['pay'] = '微信支付';
                }else{
                    $list[$key]['pay'] = '线下支付';
                }
              
            }
            $this->assign('bill',$bill);
            $this->assign('list',$list);
            $this->display();
        }else{
            $this->error("没有该订单");
        }
    }

    //账单状态
    public function billStatus(){
        if (!IS_POST) {E('页面不存在！');}
        $id = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = M('TuanBill');
        $map['id'] = $id;
        $rs=$obj->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $obj->where(array('id'=>$id))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

    public function setBill(){
        if (!IS_POST) {E('页面不存在！');}
        $articleid = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($articleid)) {
            $this->error('ID不能为空！');
        }
        $user = M('Tuan');
        $map['articleid'] = $articleid;
        $rs=$user->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $user->where(array('articleid'=>$articleid))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

    public function replaceEmoji($value){
        $value = json_encode($value);
        $value = preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","*",$value);//替换成*
        $value = json_decode($value);
        return $value;
    }
}
?>