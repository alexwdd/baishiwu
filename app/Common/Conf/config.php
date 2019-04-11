<?php
if(IS_WIN){
	define('BASE_PATH',$_SERVER['DOCUMENT_ROOT']);
}else{
 	define('BASE_PATH',__ROOT__);
}

 return array(

 	'SITE_PATH' => BASE_PATH,
 	
	//设置时区
	'DEFAULT_TIMEZONE' => 'Australia/Canberra',

	//默认模块
	'DEFAULT_MODULE'        =>  'V1',

	//预载入配置文件
	'LOAD_EXT_CONFIG' 		=> 'db,ueditor,upfile,route,cache,finance,api,pay',

	//输入字符预处理
	'DEFAULT_FILTER' => 'htmlspecialchars,trim,inject_replace',

	//数据库备份目录
	'DB_DIR' => './databak/', //数据库备份目录

	//自动加载类
	'APP_AUTOLOAD_PATH'     =>'@.ORG',

	/*Cookie配置*/
	'COOKIE_PATH'           => '/',     		// Cookie路径
    'COOKIE_PREFIX'         => 'alex',      		// Cookie前缀 避免冲突

    //生成静态文件后缀
    'HTML_FILE_SUFFIX' => '.html',

	/*定义模版标签*/
	'TMPL_L_DELIM'   		=>'<{',			//模板引擎普通标签开始标记
	'TMPL_R_DELIM'			=>'}>',				//模板引擎普通标签结束标记
	'TMPL_FILE_DEPR'=>'_',
	'TMPL_ACTION_ERROR'     =>  'Public:jump', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   =>  'Public:jump', // 默认成功跳转对应的模板文件
	//'TMPL_EXCEPTION_FILE'   =>  TMPL_PATH.'think_exception.tpl',// 异常页面的模板文件


	//数据库字段区分大小写，默认全部转为小写
	'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),

    //关闭日志
    'LOG_RECORD' => false,
);

?>