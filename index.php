<?php
/**
 * @authors alex (20779512@qq.com)
 * @date    2014-03-24 16:49:33
 * @version 1.0
 */

header("Content-type: text/html; charset=utf-8");
set_time_limit(1800);

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('APP_DEBUG',true); 				//开启调试模式
define('APP_NAME', 'APP');				//应用名称
define('RUNTIME_PATH','./runtime/');	//缓存文件目录
define('TMPL_PATH','./tpl/');			//模板目录
define('APP_PATH','./app/');			//应用目录
define('HTML_PATH', '/HTML');			//静态文件目录
define('CORE','./_core');				//框架目录

require(CORE.'/_index.php');
?>