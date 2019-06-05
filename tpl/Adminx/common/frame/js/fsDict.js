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
	//注册方式
	oauth : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"邮箱"},
			{"value":1,"name":"手机"},
			{"value":2,"name":"微信"}
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

	//会员状态
	lanjian : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"开启"},
			{"value":1,"name":"关闭","style":"color:#F00;"}
		]
	},

	//会员认证
	authentication : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"未认证"},
			{"value":1,"name":"已认证","style":"color:blue;"}
		]
	},

	//包裹状态
	baoguo : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"等待包裹"},
			{"value":1,"name":"包裹到齐","style":"color:#f00;"},
			{"value":2,"name":"等待发货","style":"color:blue;"},
			{"value":3,"name":"成功发货","style":"color:green;"}
		]
	},

	//用户组
	group : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getGroup",
		method : "post",
		labelField : "name",
		valueField : "id"
	},
  	
  	//支付类型
	payType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":1,"name":"支付宝"},
	
			{"value":2,"name":"微信"}
		]
	},

	//城市
	city : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getOptionItem&cate=1",
		method : "post",
		labelField : "name",
		valueField : "id"
	},
  
  	phone : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getPhoneType",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	phoneCate : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getPhoneCate",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	//物流公司
	company : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getOptionItem&cate=5",
		method : "post",
		labelField : "name",
		valueField : "name"
	},

	//团类型
	tuanType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"个人团"},
	
			{"value":2,"name":"常驻团"}
		]
	},

	//货物类型
	goodstype : {
		formatType : "local",
		labelField : "name",
		valueField : "id",
		//静态数据
		data:[
			{"id":1,"name":"普通货"},
			{"id":2,"name":"一级敏感货"},
			{"id":3,"name":"二级敏感货"} 
		]
	},

	houseType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":0,"name":"出租"},
			{"value":1,"name":"出售"}
		]
	},

	remarkType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":'短租',"name":"短租"},
			{"value":'长租',"name":"长租"}
		]
	},

	csType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":'中介',"name":"中介"},
			{"value":'卖家',"name":"卖家"}
		]
	},

	singleType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":'整屋出租',"name":"整屋出租"},
			{"value":'单间出租',"name":"单间出租"},
			{"value":'主人房',"name":"主人房"},
			{"value":'保姆房',"name":"保姆房"},
			{"value":'工作室',"name":"工作室"},
			{"value":'车库',"name":"车库"},
			{"value":'储藏室',"name":"储藏室"},
			{"value":'厅房/阳光房',"name":"厅房/阳光房"},
			{"value":'合租',"name":"合租"}
		]
	},

	//财务类型
	finance : {
		formatType : "server",
		loadUrl : "/index.php?m=Adminx&c=Admin&a=getFinance",
		method : "post",
		labelField : "name",
		valueField : "id"
	},

	storeType : {
		formatType : "local",
		labelField : "name",
		valueField : "value",
		//静态数据
		data:[
			{"value":'1',"name":"普通商城"},
			{"value":'2',"name":"代购商城"}
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
	}

};
