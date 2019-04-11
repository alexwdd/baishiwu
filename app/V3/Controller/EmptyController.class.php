<?php 
namespace V3\Controller;
use Think\Controller;

class EmptyController extends Controller{
    public function _empty(){
    	returnJson('-1','非法的请求');
    }
}
?>