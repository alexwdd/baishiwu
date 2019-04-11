<?php 
namespace Adminx\Controller;

class UserController extends AdminController{
	#列表
    public function index(){ 
        if (IS_POST) {
            $obj = M('User');
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

    #添加
    public function add(){
        if($_POST){
            $obj = D('User');
            if ($data = $obj->create()) {
                if ($list=$obj->add($data)) {

                    $obj = M('roleUser');
                    $tmp['role_id']=$data['group'];
                    $tmp['user_id']=$list;
                    $obj->add($tmp);

                    $url = U('User/index');
                    $this->success('管理员添加成功',$url);
                } else {
                    $this->error('管理员添加失败');
                }
            } else {
                $this->error($obj->getError());
            }
        }else{
            $obj = M('Role');
            $this->assign('group',$obj->select());
        	$this->display();
        }
    }

    #编辑
    public function edit(){
        if($_POST){
            $obj = D('User');
            if ($data = $obj->create()) {
                if($data['password']==''){
                    unset($data['password']);
                }else{
                    $data['password']=md5($data['password']);
                }
                if ($obj->save($data)) {
                    $role = M('roleUser');
                    $role->where('user_id='.$data['id'])->setField('role_id',$data['group']);

                    $url = U('User/index');
                    $this->success('管理员编辑成功',$url);
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
            $obj = M('User');
            $list = $obj->where('id=' . $id)->find();
            $this->assign('list', $list);
            $obj = M('Role');
            $this->assign('group',$obj->select());

            $city = M('OptionItem')->field('id,name')->where("cate=1")->select();;
            $this->assign('city',$city);
            $this->display();
        }
    }

    //删除
    public function del() {
    	$id = $_POST['selectedids'];
        if(!isset($id)){
            $this->error('您没有选择任何信息！');
        }else{
            $where['id']=array('in',$id);
            $where['username']=array('neq','admin');
            $list = M('User')->where($where)->delete();
            if($list){
            	unset($map);
            	$map['uid']=array('in',$id);
        		M('UserLog')->where($map)->delete();

        		unset($map);
            	$map['user_id']=array('in',$id);
        		M('roleUser')->where($map)->delete();

                $url = U('User/index');
                $this->success('管理员编辑成功',$url);
            }else{
                $this->error('删除失败');
            }
        }        
    }

    //管理员登陆日志
    public function log(){
        if (IS_POST) {
            $uid = I('get.uid');
            if($uid!=''){
                $map['uid']=$uid;
            }
            $obj = M('UserLog');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
                $list[$key]['loginTime'] = date("Y-m-d H:i:s",$value['loginTime']);
                $list[$key]['username'] = M('User')->where('id='.$value['uid'])->getField('username');
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
            $uid = I('get.id');
            $this->assign('uid',$uid);
            $this->display();
        }
    }

    //删除日志
    public function delog(){
        $id = explode(",",I('post.id'));
        $this->all_del('UserLog',$id);       
    }

    //修改密码
    function password(){
        if(isset($_POST['dosubmit'])) {
            $oldpassword = I('post.oldpwd');
            $password = I('post.password');
            $condition['id']=$_SESSION['adminID'];
            $obj = M('User');
            $userpwd = $obj->where($condition)->field('password')->find();
            if($userpwd['password']!=md5($oldpassword)){
                $this->error('原始密码错误');
            }else{
                $data['password']=md5($password);
                $obj->where($condition)->save($data);
                $url = U('Index/index');
                $this->success('密码修改成功',$url);
            }
        }else{
            $this->display();
        }        
    }
}
?>