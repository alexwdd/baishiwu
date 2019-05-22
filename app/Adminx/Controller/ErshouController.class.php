<?php
namespace Adminx\Controller;

class ErshouController extends AdminController {

	public $category;
	public $cateArr;
	public function _initialize(){
    	parent::_initialize();
    	$map['fid'] = 3;
		$this->category = M('Category')->field('id,name')->where($map)->select();
		$this->cateArr = M('Category')->where($map)->getField('id,name');
		$this->assign('cateArr',$this->cateArr);
		$this->assign('category',$this->category);
    }

	#列表
	public function index() {
		if (IS_POST) {
			$createDate  = I('createDate');
			$status  = I('status');
			$cid  = I('cid');
			$keyword  = I('keyword');

			if ($cid!='') {
				$map['sort'] = $cid;
			}
			if ($status!='') {
				$map['status'] = $status;
			}
			if ($keyword!='') {
				$map['title|contact|phone'] = array('like','%'.$keyword.'%');
			}

			if ($createDate!='') {
				$date = explode(" - ", $createDate);
				$startDate = $date[0];
				$endDate = $date[1];
				$map['createTime'] = array('between',array(strtotime($startDate),strtotime($endDate)+86399));
			}
			if (!$_SESSION['administrator']) {
				$map['cityID'] = $this->user['cityID'];
			}
			$obj = M('Ershou');
            $total = $obj->where($map)->count();
            $pageSize = I('post.pageSize',20);

            $field = I('post.field','showTime');
            $order = I('post.order','desc');

            $pages = ceil($total/$pageSize);
            $pageNum = I('post.pageNum',1);
            $firstRow = $pageSize*($pageNum-1); 

            $list = $obj->where($map)->order($field.' '.$order)->limit($firstRow.','.$pageSize)->select();
            foreach ($list as $key => $value) {
            	$list[$key]['cateName']=$this->cateArr[$value['sort']];
                $list[$key]['createTime'] = date("Y-m-d H:i:s",$value['createTime']);
                $list[$key]['updateTime'] = date("Y-m-d H:i:s",$value['updateTime']);
                $list[$key]['showTime'] = date("Y-m-d H:i:s",$value['showTime']);
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

	public function add(){
		if($_POST){
	        $obj = D('Ershou');
	        if ($data = $obj->create()) {
	        	$data['status'] = 1;
	        	$image = I('post.image');	            	
            	if ($image) {
		            $data['image'] = implode(";", $image);
		            if ($data['thumb']=='') {
		            	$data['thumb'] = $image[0];
		            }
		            $data['thumb_s'] = $this->getThumb($data['thumb'], 240, 180);
		            $data['thumb_b'] = $this->getThumb($data['thumb'], 600, 450);
		        }else{
		            $data['image'] = '';
		        }
	            if ($list = $obj->add($data)) {	            	
	            	if (I('post.makeHtml')) {
	            		$html = HTML_PATH . '/'.CONTROLLER_NAME.'/'.date('ym').'/'.$list.C('HTML_FILE_SUFFIX');
	            		$obj->where(array('articleid'=>$list))->setField('html',$html);
	            		$info = $obj->where(array('articleid'=>$list))->find();
	            		$this->mk($info);
	            	}
	            	$this->success('操作成功',U('Ershou/index'));
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
		if ($_POST) {
			$obj = D('Ershou');
	        if ($data = $obj->create()) {

	        	if(!$data['status']){$data['status']=0;};

	        	if (I('post.makeHtml')) {            		
            		$html = HTML_PATH . '/'.CONTROLLER_NAME.'/'.date('ym').'/'.$data['articleid'].C('HTML_FILE_SUFFIX');
	            	$data['html'] = $html;
            	}else{
            		$data['html'] = '';
            	}

            	$image = I('post.image');	 
            	if ($image) {
		            $data['image'] = implode(";", $image);
		            if ($data['thumb']=='') {
		            	$data['thumb'] = $image[0];
		            }
		            if ($data['thumb_s']!=I('post.old_s')) {
		            	$data['thumb_s'] = $this->getThumb($data['thumb_s'], 240, 180);
		            }
		            if ($data['thumb_b']!=I('post.old_b')) {		   
		            	$data['thumb_b'] = $this->getThumb($data['thumb_b'], 600, 450);
		            }
		        }else{
		            $data['image'] = '';
		        }
	            if ($list = $obj->save($data)) {
	            	if (I('post.makeHtml')) {
	            		$info = $obj->where(array('articleid'=>$data['articleid']))->find();
	            		$this->mk($info);
	            	}
		    		$this->success('操作成功');
	            } else {
	                $this->error('操作失败');
	            }
	        } else {
	            $this->error($obj->getError());
	        }
		}else{
			$articleid = (int) $_GET['id'];
			if (!isset ($articleid)) {
				$this->error('参数错误');
			}
			$obj = M('Ershou');
			$list = $obj->where("articleid=$articleid")->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				if ($list['image']) {
	                $list['image'] = explode(";", $list['image']);
	            }
	            if ($list['thumb']=='') {
	            	$list['thumb']=$list['thumb_s'];
	            }
				$this->assign('list', $list);
				$this->display();
			}
		}
	}

	public function image() {
		$articleid = (int) $_GET['id'];
		if (!isset ($articleid)) {
			$this->error('参数错误');
		}
		$obj = M('Ershou');
		$list = $obj->where("articleid=$articleid")->find();
		if (!$list) {
			$this->error('信息不存在');
		} else {
			if ($list['image']) {
                $list['image'] = explode(";", $list['image']);
            }
            if ($list['thumb']=='') {
            	$list['thumb']=$list['thumb_s'];
            }
			$this->assign('list', $list);
			$this->display();
		}
	}

	public function mk($data){
		if ($data) {
			if ($data['image']) {
                $data['image'] = explode(";", $data['image']);
            }
			$this->assign("list", $data);			
			$this->buildHtml($data['articleid'], '.'.HTML_PATH . '/'.CONTROLLER_NAME.'/'.date('ym').'/', 'tpl');
		}
	}

	public function zhiding() {
		if ($_POST) {
			$endDate = I('post.endDate');
			$money = I('post.money');
			$picname = I('post.picname');
			$articleid = I('post.articleid');

			if ($endDate=='') {
				$this->error('请选择到期时间');
			}
			if (!isset ($articleid)) {
				$this->error('参数错误');
			}
			if ($money<0) {
				$this->error('金额错误');
			}

			$map['articleid'] = $articleid;
			$obj = M('Ershou');
			$list = $obj->where($map)->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				//保存日志
		        $payData = array(
		            'type' => 1,
		            'cityID'=> $list['cityID'],
		            'money' => $money,
		            'memberID' => 0,
		            'nickname' => '',
		            'doID' => session('adminID'),
		            'doUser' => session('adminName'),
		            'picname' => $picname,
		            'model'=> CONTROLLER_NAME,
		            'extend1' => $list['articleid'],
		            'extend1' => '二手商品',
		            'admin' => 2,
		            'msg' => '['.$list['title'].']，置顶至'.$endDate.'，收到金额：'.$money.'元',
		            'createTime' => time()
		        );
		        $finance = M('Finance');
		        $finance->startTrans();  // 开户事务，注意事务表引擎为InnoDB类型
		        $list = $finance->add($payData);
		        if ($list) {
		        	$data['isTop'] = 1;
		        	$data['showTime'] = strtotime($endDate) + 86399;
					$r = $obj->where($map)->save($data);
					if ($r) {
						$finance->commit();
						$this->success('操作成功');
					}else{
						$finance->rollback(); 
						$this->success('操作失败');
					}
		        }else{
		        	$this->error('提现失败!');
		        }
			}
		}else{
			$articleid = (int) $_GET['id'];
			if (!isset ($articleid)) {
				$this->error('参数错误');
			}
			$obj = M('Ershou');
			$list = $obj->where("articleid=$articleid")->find();
			if (!$list) {
				$this->error('信息不存在');
			} else {
				$this->assign('list', $list);
				$this->display();
			}
		}
	}

	public function status(){
        if (!IS_POST) {E('页面不存在！');}
        $articleid = I('post.id');
        $field = I('post.field');
        $value = I('post.val');
        if (empty($articleid)) {
            $this->error('ID不能为空！');
        }
        $user = M('Ershou');
        $map['articleid'] = $articleid;
        $rs=$user->where($map)->find();
        if(!$rs){
            $this->error('信息不存在！');
        }        
        $rs = $user->where(array('articleid'=>$articleid))->save(array($field=>$value));
        if ($rs) {        
            $this->success('状态更新成功');
        }
    }

	#删除
	public function del() {
		$articleid=I('post.articleid');
        if($articleid==''){
            $this->error('您没有选择任何信息！');
        }else{
            $map['articleid'] = array('in',$articleid);
            $obj = M('Ershou');
            $list = $obj->where($map)->delete();
            if ($list) {
            	unset($map);
                $map['articleid'] = array('in',$articleid);
                $map['type'] = 'sp';
                M('Commend')->where($map)->delete();
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
	}
}
?>