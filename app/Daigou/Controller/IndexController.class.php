<?php
namespace Daigou\Controller;
use Common\Controller\BaseController;

class IndexController extends CommonController {
    public function index() {   
        $this->display();
    }    

    public function main(){
    	$order = M('Order')->where(array('del'=>0,'agentID'=>$this->user['id']))->count();
        $order1 = M('Order')->where(array('del'=>0,'agentID'=>$this->user['id'],'payStatus'=>0))->count();
        $order2 = M('Order')->where(array('del'=>0,'agentID'=>$this->user['id'],'payStatus'=>1))->count();
        $order3 = M('Order')->where(array('del'=>0,'agentID'=>$this->user['id'],'payStatus'=>2))->count();       
        $order4 = M('Order')->where(array('del'=>0,'agentID'=>$this->user['id'],'payStatus'=>3))->count();   


        $begin=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($begin,$end));
        $map['agentID'] = $this->user['id'];
        $map['del'] = 0;
        $number1 = M('Order')->where($map)->count();

        $begin = mktime(0, 0 , 0,date("m"),1,date("Y"));
    	$end = mktime(23,59,59,date("m"),date("t"),date("Y"));
    	$map['createTime'] = array('between',array($begin,$end));
    	$number2 = M('Order')->where($map)->count();

    	$begin = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
    	$end = mktime(23,59,59,date("m") ,0,date("Y"));
    	$map['createTime'] = array('between',array($begin,$end));
    	$number3 = M('Order')->where($map)->count();

    	$begin = mktime(0, 0 , 0, 1 , 1 ,date("Y"));    	
    	$end = mktime(23,59,59,12,31,date("Y"));
    	$map['createTime'] = array('between',array($begin,$end));
    	$number4 = M('Order')->where($map)->count();

        $count = [        
            'order'=>$order,
            'order1'=>$order1,
            'order2'=>$order2,
            'order3'=>$order3,
            'order4'=>$order4,
            'number1'=>$number1,
            'number2'=>$number2,
            'number3'=>$number3,
            'number4'=>$number4,

        ];
        $this->assign("count",$count);

    	$this->display();
    }
}