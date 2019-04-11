<?php
namespace Adminx\Controller;

class JiucuoController extends AdminController {

    public function index(){

        $keyword  = I('keyword');

        if($keyword!=''){
            $map['qName'] = array('like', '%'.$keyword.'%');
        }       


        $obj = M('Jiucuo');
        $count = $obj->where($map)->count();
        import("Common.ORG.Page");
        $page = new \Page($count, 15);
        $show = $page->show();
        $list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    public function view(){

        $id = (int) $_GET['id'];

        if (!isset ($id)) {
            $this->error('参数错误');
        }
        $obj = M('Jiucuo');
        $list = $obj->where("id=$id")->find();

        if (!$list) {
            $this->error('信息不存在');
        } else {
            $this->assign('list', $list);
            $this->display();
        }
          
    }

    public function del(){  
        $this->all_del('Jiucuo','reload');
    }
}
?>