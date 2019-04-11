<?php
namespace Adminx\Controller;

class GroupController extends AdminController {
    
    #用户组列表
    public function index(){ 
        if (IS_POST) {
            $obj = M('Role');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','id');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
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

    #添加用户组
    public function add(){
        if($_POST){
            $this->all_add('Role',U('Group/index'));
        }else{
            $this->display();  
        }
    }

    #编辑用户组
    public function edit(){
        if($_POST){
            $this->all_save('Role',U('Group/index'));
        }else{
            $id = (int) I('get.id');
            if (!isset ($id)) {
                $this->error('参数错误');
            }
            $obj = M('Role');
            $list = $obj->where('id=' . $id)->find();
            $this->assign('list', $list);
            $this->display();
        }
    }

    //删除组
    public function del() {
        $id = $_POST['id'];
        $id = explode(",", $id);
        if(!isset($id)){
            $this->error('您没有选择任何信息！');
        }else{
            $where['id']=array('in',$id);
            $list = M('Role')->where($where)->delete();
            if($list){
                unset($map);
                $map['role_id']=array('in',$id);
                M('access')->where($map)->delete();
                $url = U('Group/index');
                $this->success('操作成功',$url);
            }else{
                $this->error('删除失败');
            }
        }        
    }

    #用户组权限管理
    public function access() {
        if($_POST){
            $mod = I('post.mod');
            $role_id = I('post.role_id');
            
            if(!isset($role_id)||$role_id==''){
                $this->error('参数错误！');
            }
            
            if(!isset($mod)||$mod==''){
                $this->error('您没有选择任何信息！');
            }
            
            $obj = M('access');
            $obj->where('role_id='.$role_id)->delete();
            
            $data['role_id'] = $role_id;        
            $data['node_id'] = 1;   
            $data['level'] = 1;
            $data['pid'] = 0;
            $obj->add($data);
            
            foreach ($mod as $v){
                $arr = explode("-",$v);         
                $data['role_id'] = $role_id;
                $data['node_id'] = $arr[0];
                $data['level'] = $arr[1];
                $data['pid'] = $arr[2];
                $obj->add($data);
            }
            $url = U('Group/index');
            $this->success('操作成功',$url);  
        }else{ 
            $id = $_GET['id'];
            if (!isset ($id)) {
                $this->error("参数错误！");
            }
            
            $obj = M('Role');
            $list = $obj->field('name')->where('id='.$id)->find();

            $this->assign('name',$list['name']);
            
            $obj = M('Access');
            $node = $obj->field('node_id')->where('role_id='.$id)->select();

            for($i=0; $i<count($node); $i++){
                $nodeArr[$i]=$node[$i]['node_id'];      
            }
                    
            $obj = M('node');
            $list = $obj->where('level=1')->order('sort asc , id asc')->select();

            foreach ($list as $key => $value) {
                $action = $obj->where('pid='.$value['id'])->order('sort asc , id asc')->select();
                foreach ($action as $key2 => $value2) {
                    $action[$key2]['child']=$obj->where('pid='.$value2['id'])->order('sort asc , id asc')->select();
                }
                $list[$key]['child'] = $action;
            }

            $this->assign('nodeArr',$nodeArr);
            $this->assign('id',$id);
            $this->assign('list',$list);
            $this->display();
        }
    }
}