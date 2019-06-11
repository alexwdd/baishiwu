<?php
return array (
    'USER_AUTH_ON'=>true,
    'USER_AUTH_TYPE'            =>1,        // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'         =>'authId', // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'            =>'administrator',
    'USER_AUTH_MODEL'       =>'User',   // 默认验证数据表模型
    'AUTH_PWD_ENCODER'      =>'md5',    // 用户认证密码加密方式
    'USER_AUTH_GATEWAY' => '/index.php?m=Adminx&c=login&a=index',  // 默认认证网关
    'NOT_AUTH_MODULE'       =>'Upload,Editor',     // 默认无需认证模块
    'REQUIRE_AUTH_MODULE'=>'',      // 默认需要认证模块
    'NOT_AUTH_ACTION'       =>'menu,main,getGroup,getOptionItem,getFinance,getLine,getSubway',       // 默认无需认证操作
    'REQUIRE_AUTH_ACTION'=>'',      // 默认需要认证操作
    'GUEST_AUTH_ON'          => false,    // 是否开启游客授权访问
    'GUEST_AUTH_ID'           =>    0,     // 游客的用户ID
    'RBAC_ROLE_TABLE'=>'pm_role',
    'RBAC_USER_TABLE'   =>  'pm_role_user',
    'RBAC_ACCESS_TABLE' =>  'pm_access',
    'RBAC_NODE_TABLE'   => 'pm_node',
    'LOAD_EXT_CONFIG'       => 'model',


    'URL_MODEL'             =>  0,  
    'TMPL_FILE_DEPR'=>'/',


    'leftMenu' => array(
        array(
            'menuId' => "1",
            'menuName'=>'后台应用',
            'menuIcon'=>'fa-cubes',
            'menuHref'=>'',
            'parentMenuId'=>"0",
        ), 
        array(
            'menuId' => "1001",
            'menuName'=>'内容管理',
            'menuIcon'=>'fa-cubes',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId' => "1009",
            'menuName'=>'模块管理',
            'menuIcon'=>'fa-sliders',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ), 
        array(
            'menuId' => "1002",
            'menuName'=>'会员管理',
            'menuIcon'=>'fa-user',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId' => "1003",
            'menuName'=>'信息管理',
            'menuIcon'=>'fa-list',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),   
        array(
            'menuId' => "1011",
            'menuName'=>'推送',
            'menuIcon'=>'fa-comment-o',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ), 
      	array(
            'menuId' => "1012",
            'menuName'=>'话费充值',
            'menuIcon'=>'fa-phone',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ), 
        array(
            'menuId' => "1013",
            'menuName'=>'话题',
            'menuIcon'=>'fa-television',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId'=>'1004',
            'menuName'=>'拼邮',
            'menuIcon'=>'fa-paper-plane',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId'=>'1010',
            'menuName'=>'商城',
            'menuIcon'=>'fa-shopping-cart',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId' => "1005",
            'menuName'=>'财务管理',
            'menuIcon'=>'fa-rmb',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),     
        array(
            'menuId' => "1006",
            'menuName'=>'管理员设置',
            'menuIcon'=>'fa-user',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId' => "1007",
            'menuName'=>'数据备份还原',
            'menuIcon'=>'fa-database',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        ),
        array(
            'menuId' => "1008",
            'menuName'=>'系统设置',
            'menuIcon'=>'fa-cogs',
            'menuHref'=>'',
            'parentMenuId'=>"1",
        )
    )
);
?>