<?php
namespace Adminx\Controller;

class ServerController extends AdminController {
	#列表
	public function index() {
		if (IS_POST) {
			$map['agentID'] = 0;
            $obj = M('Server');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','sort');
            $order = I('post.order','asc');

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
    public function add() {
        if($_POST){
            $obj = D('Server');
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
            $obj = D('Server');
            if ($data = $obj->create()) {
                if ($obj->save($data)) {
                    $this->success('操作成功',U('Server/index'));
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
            $list = M('Server')->where("id=$id")->find();
            if (!$list) {
                $this->error('信息不存在');
            } else {
                $this->assign('list', $list);
                $this->display();
            }
        }
    }

    public function del(){
        $id = explode(",",I('post.id'));
        $this->all_del('Server',$id);
    }
}
?>