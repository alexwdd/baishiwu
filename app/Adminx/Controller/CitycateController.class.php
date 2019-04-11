<?php
namespace Adminx\Controller;

class CitycateController extends AdminController {
    public function index(){
    	if (IS_POST) {
            if (!$_SESSION['administrator']) {
                $cityID = $this->user['cityID'];
            }else{                
                $cityID = I('cityID');
            }

            if ($cityID=='' || $cityID=='') {
                $this->error('您没有选择城市');
            }

    		$big = I('post.big');
    		$small = I('post.small');
    		if ($big=='' || $small=='') {
    			$this->error('您没有选择任何信息');
    		}

    		$obj = M('CityCate');
    		$map['cityID'] = $cityID;
    		$obj->where($map)->delete();

    		$bigArr = [];
    		foreach ($big as $key => $value) {
    			$bigArr[] = [
    				'cid' => $value,
    				'cityID' => $cityID,
    				'fid' => 0,
                    'name' => I('post.name'.$value),                    
                    'icon' => I('post.icon'.$value),    				
    				'sort' => I('post.sort'.$value)
    			];
    		}
			$obj->addAll($bigArr);

			$smallArr = [];
    		foreach ($small as $key => $value) {
    			$temp = explode("-", $value);
    			$smallArr[] = [
    				'cid' => $temp[1],
    				'cityID' => $cityID,
    				'fid' => $temp[0],
                    'name' => I('post.name'.$temp[1]),
    				'icon' => I('post.icon'.$temp[1]),
    				'sort' => I('post.sort'.$temp[1])
    			];
    		}
			$obj->addAll($smallArr);
			$this->success('操作成功','reload');
    	}else{
            if (!$_SESSION['administrator']) {
                $cityID = $this->user['cityID'];
            }else{                
                $city = M('OptionItem')->field('id,name')->where(array('cate'=>1))->select();
                $cityID = I('get.cityID',$city[0]['id']);                
                $this->assign('city',$city);
            }
            $map['cityID'] = $cityID;
    		$map['fid'] = 0;
	    	$map['model'] = 2;
	    	$list = M('Category')->where($map)->select();
	    	foreach ($list as $key => $value) {
	    		$list[$key]['child'] = M('Category')->where(array('fid'=>$value['id']))->select();
	    	}
	    	$this->assign('list',$list);

	    	$myArr = M('CityCate')->where(array('cityID'=>$cityID))->getField('cid',true);
            $mySort = M('CityCate')->where(array('cityID'=>$cityID))->getField('cid,sort',true);
	    	$myIcon = M('CityCate')->where(array('cityID'=>$cityID))->getField('cid,icon',true);
	    	$this->assign('myArr',$myArr);
            $this->assign('mySort',$mySort);
            $this->assign('myIcon',$myIcon);
	    	$this->assign('cityID',$cityID);
	    	$this->display();
    	}    	
    }

   
}