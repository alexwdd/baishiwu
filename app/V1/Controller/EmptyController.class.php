<?php 
namespace V1\Controller;
use Think\Controller;

class EmptyController extends Controller{
    public function _empty(){
    	returnJson('-1','非法的请求');
    }
}
?>