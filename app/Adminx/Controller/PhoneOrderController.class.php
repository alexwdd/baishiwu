<?php
namespace Adminx\Controller;

class PhoneOrderController extends AdminController {
	#列表
	public function index() {
		if (IS_POST) {
			$keyword = I('keyword');
            $status = I('status');
          	$orderStatus = I('orderStatus');
            $payType = I('payType');

            if ($payType!='') {
                $map['payType'] = $payType;
            }

            if ($status!='') {
                $map['status'] = $status;
            }
          
          	if ($orderStatus!='') {
                $map['orderStatus'] = $orderStatus;
            }

            if ($keyword!='') {
                $map['mobile|order_no'] = $keyword;
            }

            $obj = M('PhoneOrder');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
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
    
    public function del(){
        $id = explode(",",I('post.id'));
        $this->all_del('PhoneOrder',$id);
    }
}
?>