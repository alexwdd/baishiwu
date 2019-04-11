<?php
namespace Adminx\Controller;

class ClearController extends AdminController {

	#文章列表
	public function index() {
		if (IS_POST) {
            $dbconn = m();  
            $sql = 'truncate table '.C('DB_PREFIX').'smscode';
            $dbconn->execute($sql);
            $sql = 'truncate table '.C('DB_PREFIX').'info';
            $dbconn->execute($sql);
            $sql = 'truncate table '.C('DB_PREFIX').'coupon';
            $dbconn->execute($sql);
            /*$sql = 'truncate table '.C('DB_PREFIX').'lottery';
            $dbconn->execute($sql);*/
            /*$sql = 'truncate table '.C('DB_PREFIX').'member';
            $dbconn->execute($sql);*/
            $sql = 'truncate table '.C('DB_PREFIX').'login_log';
            $dbconn->execute($sql);
            $this->success('清空完成',U('Clear/index'));
        }else{
            $this->display();
        }		
	}	
}
?>