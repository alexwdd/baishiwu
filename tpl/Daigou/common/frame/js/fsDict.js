/**
 * @Description: 字典配置
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.fsDict = {	

	//商品状态
	goodsComm : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"是","style":"color:blue;"},
			{"value":0,"name":"","style":"color:red;"}
		]
	},

	//商品状态
	goodsShow : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"显示"},
			{"value":0,"name":"隐藏","style":"color:red;"}
		]
	},

	//颜色
	color : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":"#FF5722","name":"红色","style":"color:#FF5722;"},
			{"value":"#1E9FFF","name":"蓝色","style":"color:#1E9FFF;"},
			{"value":"#009688","name":"绿色","style":"color:#009688;"}
		]
	},

	//支付状态
	payStatus : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"待付款"},
			{"value":1,"name":"待审核","style":"color:red;"},
			{"value":2,"name":"待配货","style":"color:gold;"},
			{"value":3,"name":"配货中","style":"color:blue;"},
			{"value":4,"name":"已发货","style":"color:green;"},
			{"value":99,"name":"取消订单","style":"color:red;"}
		]
	},

	//商品运费
	baoyou : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"不包邮"},
			{"value":1,"name":"包邮"}
		]
	},

	//支付方式
	payType : {
		formatType : "server",
		loadUrl : "/index.php?m=Daigou&c=common&a=getPayType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//品牌
	brand : {
		formatType : "server",
		loadUrl : "/index.php?m=Daigou&c=common&a=getBrand",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//包裹类型
	baoguo : {
		formatType : "server",
		loadUrl : "/index.php?m=Daigou&c=common&a=getBaoguo",
		method : "post",
		labelField : "name",
		valueField : "id"
	}

};
