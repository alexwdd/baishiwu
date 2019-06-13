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
	'LOAD_EXT_CONFIG' 		=> 'db,ueditor,upfile,route,cache,finance,api,pay,aue',

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

    //包裹类型
    /*
    max 一个包裹最多几个
    same 相同商品一个包裹最多几个
    can 能和哪些商品混寄
    */
    'baoguoType' => array(         
        array('id'=>1,'name'=>'罐装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>2,'name'=>'袋装奶粉','max'=>3,'same'=>99,'can'=>[]),
        array('id'=>3,'name'=>'小罐奶粉','max'=>3,'same'=>99,'can'=>[]),
        
        array('id'=>4,'name'=>'保健品','max'=>8,'same'=>6,'can'=>[6,7,8,9,10,11]),
        array('id'=>5,'name'=>'鞋子','max'=>1,'same'=>99,'can'=>[]),

        
        array('id'=>6,'name'=>'15++蜂蜜','max'=>1,'same'=>1,'can'=>[4,9,10,11]),
        array('id'=>7,'name'=>'30元以上','max'=>1,'same'=>1,'can'=>[4,8,9,10,11]),
        array('id'=>8,'name'=>'15-30元','max'=>2,'same'=>2,'can'=>[4,7,9,10,11]),
        array('id'=>9,'name'=>'15元以下','max'=>4,'same'=>4,'can'=>[4,6,7,8,10,11]), 

        array('id'=>10,'name'=>'日用品','max'=>6,'same'=>6,'can'=>[4,6,7,8,9,11]),
        array('id'=>15,'name'=>'1个不混(保健品)','max'=>1,'same'=>1,'can'=>[]),
        array('id'=>11,'name'=>'2个可混(保健品)','max'=>2,'same'=>2,'can'=>[4,6,7,8,9,10]),

        array('id'=>12,'name'=>'红酒','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>13,'name'=>'手动面单','max'=>999,'same'=>999,'can'=>[]),
        array('id'=>14,'name'=>'生鲜','max'=>999,'same'=>999,'can'=>[])
    ),
);

?>