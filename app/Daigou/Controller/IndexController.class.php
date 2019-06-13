<?php
namespace Daigou\Controller;
use Common\Controller\BaseController;

class IndexController extends CommonController {
    public function index() {   
        $this->display();
    }    

    public function main(){
    	$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y')); 
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map['createTime'] = array('between',array($beginToday,$endToday));
        $order1 = M('DgOrder')->where($map)->sum('total');

        unset($map);
        $beginDate = date("Y-m-01");
        $endDate = date('Y-m-d H:i:s', strtotime("$beginDate +1 month -1 second"));
        $beginDate=strtotime($beginDate);
        $endDate=strtotime($endDate);
        $map['createTime'] = array('between',array($beginDate,$endDate));
        $map['payStatus'] = array('in',[2,3,4]);
        $order2 = M('DgOrder')->where($map)->sum('total'); 
 
        //本月销量
        $dayNumber = date('t', strtotime(date("Y-m")));
        $dateArr = [];
        $moneyArr = [];
        for ($i=1; $i <= $dayNumber ; $i++) { 
            unset($map);
            $start = date("Y-m").'-'.$i;
            $end = date('Y-m-d H:i:s', strtotime("$start +1 day -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);
            $map['createTime'] = array('between',array($start,$end));
            $map['payStatus'] = array('in',[2,3,4]);
            $money = M('DgOrder')->where($map)->sum('total');
            array_push($dateArr, '"'.date("m-d",$start).'"');
            array_push($moneyArr, $money);
        } 
        $dateArr = implode(",",$dateArr);
        $moneyArr = implode(",",$moneyArr);
        $monthData = [
            'date'=>$dateArr,
            'money'=>$moneyArr
        ];
        $this->assign('monthData',$monthData);

        $dateArr = [];
        $moneyArr = [];
        for ($i=1; $i <= 12 ; $i++) { 
            unset($map);
            $start = date("Y").'-'.$i.'-01';
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month -1 second")); 
            $start=strtotime($start);
            $end=strtotime($end);            
            $map['createTime'] = array('between',array($start,$end));
            $map['payStatus'] = array('in',[2,3,4]);
            $money = M('DgOrder')->where($map)->sum('total');
            array_push($dateArr, '"'.date("m月",$start).'"');
            array_push($moneyArr, $money);
        } 
        $dateArr = implode(",",$dateArr);
        $moneyArr = implode(",",$moneyArr);
        $yearData = [
            'date'=>$dateArr,
            'money'=>$moneyArr
        ];
        $this->assign('yearData',$yearData);

        $count = [        
            'order1'=>$order1,
            'order2'=>$order2
        ];
        $this->assign("count",$count);

    	$this->display();
    }
}