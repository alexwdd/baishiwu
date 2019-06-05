<?php
namespace Daigou\Controller;

class SettingController extends CommonController {

    #编辑
    public function index() {
        if ($_POST) {
            $data['name'] = I('post.name');
            $data['siteLogo'] = I('post.siteLogo');
            $data['notice'] = I('post.notice');
            $data['huilv'] = I('post.huilv');
            $data['content'] = I('post.content');
            if ($data['name']=='') {
                $this->error('店铺名称不能为空');
            }
            $map['id'] = $this->user['id'];
            M('Daigou')->where($map)->save($data);
            $this->success('操作成功');
        }else{
            $this->display();
        }
    }

    //修改密码
    function password(){
        if(isset($_POST['dosubmit'])) {
            $oldpassword = I('post.oldpwd');
            $password = I('post.password');
            $condition['id']=$this->user['id'];
            $obj = M('Daigou');
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

    //清除缓存
    public function clearcache(){
        $this->delDirAndFile($_SERVER['DOCUMENT_ROOT']."/runtime");
        $this->success("操作成功");
    }

    public function delDirAndFile($path){
        $path=str_replace('\\',"/",$path);
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..")
                        is_dir("$path/$item") ? $this->delDirAndFile("$path/$item") : unlink("$path/$item");
                }
                closedir($handle);
            }
        } else {
            return false;
        }
    }
}
?>