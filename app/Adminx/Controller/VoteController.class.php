<?php
namespace Admin\Controller;

class VoteController extends AdminController{

    public function view(){
    	$list = M('Subject')->select();
    	if ($list) {            
            foreach ($list as $key => $value) {
            	$list[$key]['sum'] = M('Question')->where('sid='.$value['id'])->sum('poll');
            	$list[$key]['option'] = M('Question')->where('sid='.$value['id'])->select();
            	foreach ($list[$key]['option'] as $j => $opt) {
            		$list[$key]['option'][$j]['bfb'] = round(($opt['poll']/$list[$key]['sum'])*100,2);
            	}
            }
            $this->assign('list',$list);
            $this->display();
        }else{
            $this->error('没有任何投票主题');
        }		
    }

    #设置投票
	public function setting() {
        if ($_POST) {
            $this->all_save('VoteConfig',U('Vote/setting'));
        }else{
            $list = M('VoteConfig')->find();
            $this->assign('list',$list);
            $this->display();
        }		
	}
}