/**
 * @Description: 字典配置
 * @Copyright: 2017 www.fallsea.com Inc. All rights reserved.
 * @author: fallsea
 * @version 1.8.0
 * @License：MIT
 */
layui.fsDict = {

	//用户状态
	status : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"禁用","style":"color:#F00;"},
			{"value":1,"name":"启用"}
		]
	},

	//会员状态
	disable : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"启用"},
			{"value":1,"name":"禁用","style":"color:#F00;"}
		]
	},

	//结算状态
	jiesuan : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"已结算"},
			{"value":0,"name":"未结算","style":"color:red;"}
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

	//支付状态
	payStatus : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"未支付"},
			{"value":1,"name":"已支付","style":"color:green;"}
		]
	},

	//支付状态
	orderStatus : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"待付款"},
			{"value":1,"name":"待审核","style":"color:blue;"},
			{"value":2,"name":"待发货","style":"color:blue;"},
			{"value":3,"name":"已发货","style":"color:green;"},
			{"value":99,"name":"交易失败","style":"color:red;"}
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
		loadUrl : "/index.php?m=Agent&c=common&a=getPayType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//取货方式
	quhuoType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"无痕代发"},
			{"value":2,"name":"门店自提"}
		]
	},

	//商品模型
	goodsType : {
		formatType : "server",
		loadUrl : "/index.php?m=Agent&c=common&a=getGoodsType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//品牌
	brand : {
		formatType : "server",
		loadUrl : "/index.php?m=Agent&c=common&a=getBrand",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//包裹类型
	baoguo : {
		formatType : "server",
		loadUrl : "/index.php?m=Agent&c=common&a=getBaoguo",
		method : "post",
		labelField : "name",
		valueField : "id"
	}

};
