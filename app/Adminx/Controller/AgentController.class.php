<?php
namespace Adminx\Controller;

class AgentController extends AdminController {

	#列表
	public function index() {
		if (IS_POST) {
            $disable  = I('disable');
            $authentication  = I('authentication');
            $oauth  = I('oauth');
            $keyword  = I('keyword');            

            if($keyword!=''){
                $map['phone|email|nickname|name'] = array('like','%'.$keyword.'%');
            }   

            if($disable!=''){
                $map['disable'] = $disable;
            }
            if($authentication!=''){
                $map['authentication'] = $authentication;
            }
            if($oauth!=''){
                $map['oauth'] = $oauth;
            }

            if (!$_SESSION['administrator']) {
                $map['cityID'] = $this->user['cityID'];
            }
            
            $obj = M('Agent');
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
            $this->display();
        }
	}

    #添加
    public function add() {
        if($_POST){
            $obj = D('Agent');
            if ($data = $obj->create()) {
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
    public function edit() {
        if($_POST){
            $obj = D('Agent');
            if ($data = $obj->create()) {
                $map['username'] = $data['username'];
                $map['id'] = array('neq',$data['id']);
                $res = M('Agent')->where($map)->find();
                if ($res) {
                    $this->error("用户名重复");
                }
                if($data['password']==''){
                    unset($data['password']);
                }else{
                    $data['password']=md5($data['password']);
                }
                if ($obj->save($data)) {
                    $this->success('操作成功',U('Agent/index'));
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error($obj->getError());
            }
        }else{
            $id = (int) $_GET['id'];
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $list = M('Agent')->where("id=$id")->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                $this->assign('list', $list);
                $this->display();
            }
        }
    }

    public function go(){
        $id = i('get.id');
        if (empty($id)) {$this->error('缺少参数');}
        $user = M('Agent');
        $map['id'] = $id;
        $rs = $user->where($map)->find();
        if ($rs) {
            $crypt = new \Think\Crypt;
            $cryptStr = $rs['id'].','.get_client_ip();
            $flag = $crypt->encrypt($cryptStr,C('DATA_CRYPT_KEY'),0);
            session('flag', $flag);
            session('isadmin', '9999');
            $this->redirect('Agent/Index/index'); 
        }else{
            $this->error('您访问的会员不存在！');      
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
        $obj = M('Agent');
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
        $this->all_del('Agent','reload');
    }
}
?>