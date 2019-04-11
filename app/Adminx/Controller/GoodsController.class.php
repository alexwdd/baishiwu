<?php
namespace Adminx\Controller;

class GoodsController extends AdminController {
	#列表
	public function index() {
		if (IS_POST) {
			$keyword = I('keyword');
            $type = I('type');
            $order_no = I('order_no');
            $goodsid = I('goodsid');

            if ($order_no!='') {
                $map['order'] = $order_no;
            }

            if ($goodsid!='') {
                $map['goodsid'] = $goodsid;
            }

            if ($type!='') {
                $map['status'] = $type;
            }

            if ($keyword!='') {
                $map['contact|phone'] = $keyword;
            }

            $startDate  = I('startDate');
            $endDate  = I('endDate');
            if($startDate!='' && $endDate!=''){
                $map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
            }
            
            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }

            $obj = M('TuanDetail');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','status');
            $order = I('post.order','asc');

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
			$this->display();
		}		
	}
	

	#编辑
	public function edit(){
        if($_POST){
        	$status = I('post.status');
        	$weight_a = I('post.weight_a');
            $goodsid = I('post.goodsid');
        	$articleid = I('post.articleid');
        	if ($goodsid=='' || !is_numeric($goodsid)) {
        		$this->error('参数错误');
        	}
        	if ($status=='' || $weight_a=='') {
        		$this->error('信息不完整');
        	}
        	$data['updateTime'] = time();
        	$data['status'] = $status;
        	$data['weight_a'] = $weight_a;
        	$status = I('post.status');
            $obj = M('TuanDetail');
            $list = $obj->where(array('goodsid'=>$goodsid))->save($data);
            if ($list) { 
                $tuan = M('Tuan')->where(array('articleid'=>$articleid))->find();
                $this->setTuanStatus($tuan,$status);  
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }            
        }else{
            $goodsid = (int) I('get.id');
            if (!isset ($goodsid)) {
                $this->error('参数错误');
            }
            $obj = M('TuanDetail');
            $list = $obj->where('goodsid=' . $goodsid)->find();
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $goodsid = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($goodsid)) {
            $this->error('ID不能为空！');
        }
        $obj = M('TuanDetail');
        $map['goodsid'] = $goodsid;
        $list=$obj->where($map)->find();
        if(!$list){
            $this->error('信息不存在！');
        }        
        $tuan = M('Tuan')->where(array('articleid'=>$list['articleid']))->find();
        if (!$tuan) {
            $this->error('拼团信息不存在');
        }

        $rs = $obj->where(array('goodsid'=>$goodsid))->save(array($field=>$value));
        if ($rs) {   
            $this->setTuanStatus($tuan,$value);     
            $this->success('状态更新成功');
        }
    }

	
	public function del(){
        $goodsid=I('post.goodsid');
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

    //$tuan团信息 $goods包裹信息
    public function setTuanStatus($tuan,$value){
        if ($value==0) {
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
?>