<?php
namespace Adminx\Controller;

class ActivityController extends AdminController {
	#列表
	public function index() {
		if (IS_POST) {			
            $obj = M('Activity');
            $total = $obj->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['startTime'] = date("Y-m-d",$value['startTime']);
                $list[$key]['endTime'] = date("Y-m-d",$value['endTime']);   
                $list[$key]['num'] = M('ActivityLog')->where('activityID='.$value['id'])->count();   
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

    #添加
    public function add() {
        if($_POST){
            $obj = D('Activity');
            if ($data = $obj->create()) {
                $data['prize'] = serialize(I('post.prize'));
                $data['image'] = serialize(I('post.image'));
                $data['number'] = serialize(I('post.number'));
                $data['probability'] = serialize(I('post.probability'));

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
	

	#编辑
	public function edit(){
        if($_POST){
            $obj = D('Activity'); //创建对象
            if ($data = $obj->create()) {//创建数据
                $data['prize'] = serialize(I('post.prize'));
                $data['image'] = serialize(I('post.image'));
                $data['number'] = serialize(I('post.number'));
                $data['probability'] = serialize(I('post.probability'));
                if (!I('post.status')) {
                    $data['status'] = 0;
                }     
                if ($obj->save($data)) { //保存到数据库
                    $this->success('操作成功',U('Activity/index'));
                } else {
                    $this->error('编辑失败');
                }
            } else {
                $this->error($obj->getError());
            }
        }else{
            $id = (int) I('get.id');
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('Activity');
            $list = $obj->where('id=' . $id)->find();
            $list['prize'] = unserialize($list['prize']);
            $list['image'] = unserialize($list['image']);
            $list['number'] = unserialize($list['number']);
            $list['probability'] = unserialize($list['probability']);
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $id = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $obj = M('Activity');
        $map['id'] = $id;
        $list=$obj->where($map)->find();
        if(!$list){
            $this->error('信息不存在！');
        }        
        $rs = $obj->where(array('id'=>$id))->save(array($field=>$value));
        if ($rs) {
            $this->success('状态更新成功');
        }
    }
	
	public function del(){
        $id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $obj = M('Activity');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
	}

    public function view(){
        if (IS_POST) {
            $aID = I('get.aID');
            $map['activityID'] = $aID;
            $obj = M('ActivityLog');
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
            $id = I('get.id');
            $map['id'] = $id;
            $activity = M('Activity')->where($map)->find();
            if ($activity) {
                $this->assign('activity',$activity);
                $this->display();
            }else{
                $this->error('信息不存在');
            }
        }        
    }
  
  	public function dellog(){
        $id=I('post.id');
        if($id==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['id'] = array('in',$id);
            $obj = M('ActivityLog');
            $list = $obj->where($map)->delete();
            if ($list) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
	}
}
?>