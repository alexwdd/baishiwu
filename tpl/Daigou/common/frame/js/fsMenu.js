/**
 * @Description: 菜单配置
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.define(['element',"fsConfig","fsCommon"], function(exports){

	var menuConfig = {
			dataType : "local" , //获取数据方式，local本地获取，server 服务端获取
			loadUrl : "/index.php/Adminx/Index/menu", //加载数据地址
			method : "post",//请求类型，默认post
			rootMenuId : "0", //根目录菜单id
			defaultSelectTopMenuId : "1", //默认选中头部菜单id
			defaultSelectLeftMenuId : "", //默认选中左边菜单id
			menuIdField : "menuId", //菜单id
			menuNameField : "menuName", //菜单名称
			menuIconField : "menuIcon" , //菜单图标，图标必须用css
			menuHrefField : "menuHref" , //菜单链接
			parentMenuIdField : "parentMenuId" ,//父菜单id
			data : [	
				{"menuId":"1","menuName":"控制台","menuIcon":"fa-cog","menuHref":"","parentMenuId":"0"},	
				{"menuId":"10","menuName":"内容管理","menuIcon":"fa-desktop","menuHref":"","parentMenuId":"1"},
				{"menuId":"11","menuName":"商城管理","menuIcon":"fa-shopping-cart","menuHref":"","parentMenuId":"1"},
				{"menuId":"12","menuName":"订单管理","menuIcon":"fa-rmb","menuHref":"","parentMenuId":"1"},

				{"menuId":"101","menuName":"商城设置","menuIcon":"","menuHref":"/index.php?m=Daigou&c=setting&a=index","parentMenuId":"10"},
				{"menuId":"102","menuName":"广告管理","menuIcon":"","menuHref":"/index.php?m=Daigou&c=ad&a=index","parentMenuId":"10"},
				{"menuId":"103","menuName":"帮助中心","menuIcon":"","menuHref":"/index.php?m=Daigou&c=article&a=index","parentMenuId":"10"},
				{"menuId":"104","menuName":"商城留言","menuIcon":"","menuHref":"/index.php?m=Daigou&c=feedback&a=index","parentMenuId":"10"},

				{"menuId":"111","menuName":"商品管理","menuIcon":"","menuHref":"/index.php?m=Daigou&c=goods&a=index","parentMenuId":"11"},
				{"menuId":"112","menuName":"商品分类","menuIcon":"","menuHref":"/index.php?m=Daigou&c=category&a=index","parentMenuId":"11"},
				{"menuId":"113","menuName":"品牌管理","menuIcon":"","menuHref":"/index.php?m=Daigou&c=brand&a=index","parentMenuId":"11"},
				/*{"menuId":"114","menuName":"商品模型","menuIcon":"","menuHref":"/index.php?m=Daigou&c=goodsType&a=index","parentMenuId":"11"},
				{"menuId":"115","menuName":"商品属性","menuIcon":"","menuHref":"/index.php?m=Daigou&c=goodsAttribute&a=index","parentMenuId":"11"},
				{"menuId":"116","menuName":"商品规格","menuIcon":"","menuHref":"/index.php?m=Daigou&c=goodsSpec&a=index","parentMenuId":"11"},
				{"menuId":"117","menuName":"物流管理","menuIcon":"","menuHref":"/index.php?m=Daigou&c=wuliu&a=index","parentMenuId":"11"},*/
				{"menuId":"118","menuName":"收款方式","menuIcon":"","menuHref":"/index.php?m=Daigou&c=card&a=index","parentMenuId":"11"},
				/*{"menuId":"119","menuName":"包裹类型","menuIcon":"","menuHref":"/index.php?m=Daigou&c=baoguo&a=index","parentMenuId":"11"},
				{"menuId":"120","menuName":"贴心服务","menuIcon":"","menuHref":"/index.php?m=Daigou&c=server&a=index","parentMenuId":"11"},*/

				{"menuId":"121","menuName":"待审核","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order1&a=index","parentMenuId":"12"},
				{"menuId":"122","menuName":"待付款","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order2&a=index","parentMenuId":"12"},
				{"menuId":"123","menuName":"已付款","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order3&a=index","parentMenuId":"12"},
				{"menuId":"124","menuName":"已发货","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order4&a=index","parentMenuId":"12"},
				{"menuId":"125","menuName":"取消订单","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order5&a=index","parentMenuId":"12"},
				{"menuId":"126","menuName":"历史订单","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order&a=index","parentMenuId":"12"},
				{"menuId":"127","menuName":"包裹定位","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order&a=index","parentMenuId":"12"},
				{"menuId":"128","menuName":"每日结算","menuIcon":"","menuHref":"/index.php?m=Daigou&c=order&a=index","parentMenuId":"12"},
			] //本地数据
	};

	var element = layui.element,
	fsCommon = layui.fsCommon,
	fsConfig = layui.fsConfig,
	statusName = $.result(fsConfig,"global.result.statusName","errorNo"),
  	msgName = $.result(fsConfig,"global.result.msgName","errorInfo"),
	successNo = $.result(fsConfig,"global.result.successNo","0"),
  	dataName = $.result(fsConfig,"global.result.dataName","results.data"),
	FsMenu = function (){

	};

	FsMenu.prototype.render = function(){
		this.loadData();
		this.showMenu();
	};

	/**
	 * 加载数据
	 */
	FsMenu.prototype.loadData = function(){
		if(menuConfig.dataType == "server"){//服务端拉取数据
			var url = menuConfig.loadUrl;
			if($.isEmpty(url)){
				fsCommon.errorMsg("未配置请求地址！");
				return;
			}

			fsCommon.invoke(url,{},function(data){
  			if(data[statusName] == successNo){
  				menuConfig.data = $.result(data,dataName);
  			}else{
  				//提示错误消息
  				fsCommon.errorMsg(data[msgName]);
  			}
  		},false,menuConfig.method);

		}
	}


	/**
	 * 获取图标
	 */
	FsMenu.prototype.getIcon = function(menuIcon){

		if(!$.isEmpty(menuIcon)){

			if(menuIcon.indexOf("<i") == 0){
				return menuIcon;
			}else if (menuIcon.indexOf("&#") == 0){
				return '<i class="layui-icon">'+menuIcon+'</i>';
			}else if (menuIcon.indexOf("fa-") == 0){
				return '<i class="fa '+menuIcon+'"></i>';
			}else {
				return '<i class="'+menuIcon+'"></i>';
			}
		}
		return "";
	};

	/**
	 * 清空菜单
	 */
	FsMenu.prototype.cleanMenu = function(){
		$("#fsTopMenu").html("");
		$("#fsLeftMenu").html("");
	}
	/**
	 * 显示菜单
	 */
	FsMenu.prototype.showMenu = function(){
		var thisMenu = this;
		var data = menuConfig.data;
		if(!$.isEmpty(data)){
			var _index = 0;
			//显示顶部一级菜单
			var fsTopMenuElem = $("#fsTopMenu");
			var fsLeftMenu = $("#fsLeftMenu");
			$.each(data,function(i,v){
				if(menuConfig.rootMenuId === v[menuConfig.parentMenuIdField]){

					var topStr = '<li class="layui-nav-item';
					if($.isEmpty(menuConfig.defaultSelectTopMenuId) && _index === 0){//为空默认选中第一个
						topStr += ' layui-this';
					}else if(!$.isEmpty(menuConfig.defaultSelectTopMenuId) && menuConfig.defaultSelectTopMenuId == v[menuConfig.menuIdField]){//默认选中处理
						topStr += ' layui-this';
					}
					_index ++ ;
					topStr += '" dataPid="'+v[menuConfig.menuIdField]+'"><a href="javascript:;">'+thisMenu.getIcon(v[menuConfig.menuIconField])+' <cite>'+v[menuConfig.menuNameField]+'</cite></a></li>';
					fsTopMenuElem.append(topStr);

					//显示二级菜单，循环判断是否有子栏目
					$.each(data,function(i2,v2){
						if(v[menuConfig.menuIdField] === v2[menuConfig.parentMenuIdField]){

							var menuRow = '<li class="layui-nav-item';
							if(!$.isEmpty(menuConfig.defaultSelectLeftMenuId) && menuConfig.defaultSelectLeftMenuId == v2[menuConfig.menuIdField]){//默认选中处理
								menuRow += ' layui-this';
							}
							//显示三级菜单，循环判断是否有子栏目
							var menuRow3 = "";
							$.each(data,function(i3,v3){
								if(v2[menuConfig.menuIdField] === v3[menuConfig.parentMenuIdField]){
									if($.isEmpty(menuRow3)){
										menuRow3 = '<dl class="layui-nav-child">';
									}
									menuRow3 += '<dd';
									if(!$.isEmpty(menuConfig.defaultSelectLeftMenuId) && menuConfig.defaultSelectLeftMenuId == v3[menuConfig.menuIdField]){//默认选中处理
										menuRow3 += ' class="layui-this"';
										menuRow += ' layui-nav-itemed';//默认展开二级菜单
									}

									menuRow3 += ' lay-id="'+v3[menuConfig.menuIdField]+'"><a href="javascript:;" menuId="'+v3[menuConfig.menuIdField]+'" dataUrl="'+v3[menuConfig.menuHrefField]+'">'+thisMenu.getIcon(v3[menuConfig.menuIconField])+' <cite>'+v3[menuConfig.menuNameField]+'</cite></a></dd>';

								}

							});

							menuRow += '" lay-id="'+v2[menuConfig.menuIdField]+'" dataPid="'+v2[menuConfig.parentMenuIdField]+'" style="display: none;"><a href="javascript:;" menuId="'+v2[menuConfig.menuIdField]+'" dataUrl="'+v2[menuConfig.menuHrefField]+'">'+thisMenu.getIcon(v2[menuConfig.menuIconField])+' <cite>'+v2[menuConfig.menuNameField]+'</cite></a>';


							if(!$.isEmpty(menuRow3)){
								menuRow3 += '</dl>';

								menuRow += menuRow3;
							}

							menuRow += '</li>';

							fsLeftMenu.append(menuRow);
						}

					});

				}
			});
		}
		element.render("nav");
	};

	var fsMenu = new FsMenu();
	exports("fsMenu",fsMenu);
});
