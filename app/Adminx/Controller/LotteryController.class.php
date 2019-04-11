<?php
namespace Adminx\Controller;

class LotteryController extends AdminController {

	#列表
	public function index(){      
        $obj = M('Lottery');
        $count = $obj->where($map)->count();
        import("Common.ORG.Page");
        $page = new \Page($count, 15);
        $show = $page->show();
        $list = $obj->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        foreach ($list as $key => $value) {
            $list[$key]['prize'] = unserialize($value['prize']);
            $list[$key]['value'] = unserialize($value['value']);
            $list[$key]['number'] = M('Coupon')->where(array('lotteryID'=>$value['id'],'flag'=>0))->count();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    #编辑
    public function add(){
        if($_POST){
            $obj = D('Lottery'); //创建对象
            if ($data = $obj->create()) {//创建数据
                $data['prize'] = serialize(I('post.prize'));
                $data['value'] = serialize(I('post.value'));
                $data['probability'] = serialize(I('post.probability'));

                if (!I('post.status')) {
                    $data['status'] = 0;
                }

                if (!I('post.auto')) {
                    $data['auto'] = 0;
                }

                if ($list = $obj->add($data)) { //保存到数据库
                    if ($data['status']==1) {
                        //生成奖券
                        $prize = I('post.prize');
                        $value = I('post.value');
                        $number = I('post.probability');
                        for($i=0; $i<count($prize); $i++){
                            $coupon['prize'] = $prize[$i];
                            $coupon['value'] = $value[$i];
                            $coupon['lotteryID'] = $list;
                            $coupon['lotteryName'] = $data['name'];
                            $coupon['flag'] = 0;
                            $coupon['status'] = 0;
                            $coupon['useTime'] = 0;
                            $coupon['memberID'] = 0;
                            $coupon['memberName'] = '';
                            $coupon['memberMobile'] = '';
                            $coupon['infoID'] = 0;
                            $coupon['username'] = '';
                            $coupon['createTime'] = time();
                            $coupon['index'] = $i+1;
                            for ($j=0; $j <$number[$i]; $j++) { 
                                M('Coupon')->add($coupon);
                            }                          
                        }
                    }

                    $this->success('操作成功',U('Lottery/index'));
                } else {
                    $this->error('编辑失败');
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
            $obj = D('Lottery'); //创建对象
            if ($data = $obj->create()) {//创建数据
                $data['prize'] = serialize(I('post.prize'));
                $data['value'] = serialize(I('post.value'));
                $data['probability'] = serialize(I('post.probability'));
                if (!I('post.status')) {
                    $data['status'] = 0;
                }
                if (!I('post.auto')) {
                    $data['auto'] = 0;
                }
                if ($obj->save($data)) { //保存到数据库
                    $this->success('操作成功',U('Lottery/index'));
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
            $obj = M('Lottery');
            $list = $obj->where('id=' . $id)->find();
            $list['prize'] = unserialize($list['prize']);
            $list['value'] = unserialize($list['value']);
            $list['probability'] = unserialize($list['probability']);
            $this->assign('list', $list);            
            $this->display();
        }
    }

    
    #删除
	public function del() {
		$this->all_del('Lottery','reload');
        
	}

    public function mk(){

        $id = (int) I('get.id');
        if (!isset ($id)) {
            $this->error('参数错误');
        }
        $obj = M('Lottery');
        $list = $obj->where('id='. $id)->find();
        if ($list) {
            $prize = unserialize($list['prize']);
            $value = unserialize($list['value']);
            $number = unserialize($list['probability']);
            for($i=0; $i<count($prize); $i++){
                $coupon['prize'] = $prize[$i];
                $coupon['value'] = $value[$i];
                $coupon['lotteryID'] = $list['id'];
                $coupon['lotteryName'] = $list['name'];
                $coupon['flag'] = 0;
                $coupon['status'] = 0;
                $coupon['useTime'] = 0;
                $coupon['memberID'] = 0;
                $coupon['memberName'] = '';
                $coupon['memberMobile'] = '';
                $coupon['infoID'] = 0;
                $coupon['username'] = '';
                $coupon['createTime'] = time();
                $coupon['index'] = $i+1;
                for ($j=0; $j <$number[$i]; $j++) { 
                    M('Coupon')->add($coupon);
                }                          
            }
            $this->success('生成成功');
        }else{
            $this->error('活动不存在');
        }     
    }
}
?>