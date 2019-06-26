<?php
namespace Adminx\Controller;

class IndexController extends AdminController {
    public function index(){
        $date = time();
        $loginInfo = M('UserLog')->where(array('uid'=>session("adminID")))->order('id desc')->limit("1,1")->select();
        $this->assign('date',$date);
        $this->assign('loginInfo',$loginInfo[0]);
    	$this->display();
    }

    public function menu(){
        if ($_SESSION['administrator']) {
            //超级管理员菜单
            $menu = C('leftMenu');
            foreach ($menu as $key => $value) {
                $child = M('Node')->field('id as menuId,title as menuName,icon as menuIcon,pid as parentMenuId,level,name')->where(array('status'=>1,'display'=>1,'level'=>2,'data'=>$value['menuName']))->order('sort asc, id asc')->select();
                foreach ($child as $j => $val) {
                    $val['menuHref'] = U($val['name'].'/index');
                    $val['parentMenuId'] = $value['menuId'];
                    $val['menuIcon']='';
                    array_push($menu,$val);
                }
            }
        }else{
            //普通用户组菜单
            $nodeArr = M('Access')->where(array('role_id'=>$this->user['group']))->getField('node_id',true);
            $menu = C('leftMenu');
            foreach ($menu as $key => $value) {
                $map['id'] = array('in',$nodeArr);
                $map['data'] = $value['menuName'];
                $map['status'] = 1;
                $map['display'] = 1;
                $map['level'] = 2;
                $child = M('Node')->field('id as menuId,title as menuName,icon as menuIcon,pid as parentMenuId,level,name')->where($map)->order('sort asc, id asc')->select();
                if ($child) {
                    foreach($child as $j => $val) {
                        $val['menuHref'] = U($val['name'].'/index');
                        $val['parentMenuId'] = $value['menuId'];
                        $val['menuIcon']='';
                        array_push($menu,$val);
                    }
                }elseif($value['parentMenuId']!=0){
                    unset($menu[$key]);
                }         
            }
        }
        echo json_encode(array(
            'status'=>1,
            'results'=>array(
                'data'=>$menu
                )
        ));
    }

    public function main(){
    	$info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            '主机名'=>$_SERVER['SERVER_NAME'],
            'WEB服务端口'=>$_SERVER['SERVER_PORT'],
            'ThinkPHP版本'=>THINK_VERSION,
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '用户的IP地址'=>$_SERVER['REMOTE_ADDR'],
        );
        $this->assign("info",$info);

        $date=date("Y-m-d");  //当前日期
        $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $w=date('w',strtotime($date));  //获取当前周的第几天 周日是0周一到周六是1-6
        $week_start=date('Y-m-d',strtotime("$date -".($w==0 ? 6 : $w - $first).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去6天
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));  //本周结束日期

        $map['createTime'] = array('between',array(strtotime($week_start),strtotime($week_end)+86399));
        $city = M('OptionItem')->where(array('cate'=>1))->select();
        foreach ($city as $key => $value) {
            $map['cityID'] = $value['id'];
            $number = M('CityVisit')->where($map)->count();
            if(!$number){
                $number=0;
            }
            $city[$key]['number'] = $number;
        }
        $this->assign('city',$city);
        $this->display();
    }

    //左侧导航菜单
    public function nav(){
        //获取用户菜单
        if ($_SESSION['administrator']) {
            //超级管理员菜单
            $menu = C('leftMenu');
            foreach ($menu as $key => $value) {
                $child = M('Node')->field('title,name')->where(array('status'=>1,'display'=>1,'level'=>2,'data'=>$value['title']))->order('sort asc, id asc')->select();
                foreach ($child as $j => $val) {
                    $child[$j]['href'] = U($val['name'].'/index');
                    if ($val['icon']=='') {
                        $child[$j]['icon']='&#xe602;';
                    }
                }
                $menu[$key]['children'] = $child;
            }
        }else{
            //普通用户组菜单
            $nodeArr = M('Access')->where(array('role_id'=>$this->user['group']))->getField('node_id',true);
            $menu = C('leftMenu');
            foreach ($menu as $key => $value) {
                $map['id'] = array('in',$nodeArr);
                $map['data'] = $value['title'];
                $map['status'] = 1;
                $map['display'] = 1;
                $map['level'] = 2;
                $child = M('Node')->field('title,name')->where($map)->order('sort asc, id asc')->select();
                if ($child) {
                    foreach ($child as $j => $val) {
                        $child[$j]['href'] = U($val['name'].'/index');
                        if ($val['icon']=='') {
                            $child[$j]['icon']='&#xe602;';
                        }
                    }
                    $menu[$key]['children'] = $child;
                }else{
                    unset($menu[$key]);
                }              
            }
        }

        echo json_encode($menu);
    }

    //清除缓存
    public function clearcache(){
    	$this->delDirAndFile($_SERVER['DOCUMENT_ROOT']."/runtime");
    	$this->success("操作成功");
    }

    public function delDirAndFile($path){
    	$path=str_replace('\\',"/",$path);
    	if (is_dir($path)) {
	        $handle = opendir($path);
	        if ($handle) {
	            while (false !== ( $item = readdir($handle) )) {
	                if ($item != "." && $item != "..")
	                    is_dir("$path/$item") ? $this->delDirAndFile("$path/$item") : unlink("$path/$item");
	            }
	            closedir($handle);
	        }
	    } else {
	        return false;
	    }
    }
}