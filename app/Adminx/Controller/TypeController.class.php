<?php
namespace Adminx\Controller;

class TypeController extends AdminController {
	#列表
	public function index() {
		$obj = M('Type');
        $count = $obj->where($map)->count();
        $list = $obj->where($map)->order('sort asc')->select();
        $this->assign('list', $list); 
        $this->display();
	}

	public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $id = I('post.id');
        if (empty($id)) {
            $this->error('ID不能为空！');
        }
        $user = M('Type');
        $map['id'] = $id;
        $rs=$user->where($map)->find();
        if(!$rs){
            $this->error('该用户不存在！');
        }

        $isstop=$rs['enable']==0?1:0;  
        $rs = $user->where(array('id'=>$id))->save(array('enable'=>$isstop));

        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

}
?>