//var api = 'http://www.worldmedia.top/V1';
var api = '/V1';
var config = {
	//首页接口
	main : api+'/article/getmain',
	//获取分类
	category : api+'/Category/infocate',
	//获取用户信息
	userinfo : api+'/Account/userinfo',

   //详情页判断是否为该用户的帖子
   checkIsMy : api+'/Account/checkMyArticle',
   
	//上传图片
	upload : api+'/account/image_upload?isThumb=YES&cityID=9',
   //上传图片
   publish : api+'/article/publish',
   //获取帖子列表
   articleList : api+'/article/usergetlist',
   //获取帖子详细信息
   articleInfo : api+'/article/getinfo',
   //帖子更新
   articleEdit : api+'/article/edit',
   //帖子更新
   articleDel : api+'/article/delete',

   //帖子日期
   articleTime : api+'/article/updatetime',

   //获取评论列表
   comments : api+'/article/get_comment',   

   //获取选项列表
   carBrand : api+'/category/item',  

   //获取验证码
   getcode : api+'/account/getCode',   
   //手机认证
   verifyPhone : api+'/account/verify_phone',
   //发表评论
   sendComment : api+'/article/send_comment', 

   //发布拼邮
   pinyouSubmit : api+'/pinyou/publish', 
   //获取拼邮详情
   pinyouEdit : api+'/pinyou/edit', 
   //获取拼邮信息
   pinyouList : api+'/pinyou/getlist', 
   //获取拼邮详情
   pinyouInfo : api+'/pinyou/getinfo', 
   //获取账单信息
   pinyouBill: api+'/pinyou/getbill',
   //我的账单
   pinyouMyBill: api+'/pinyou/getmybill',
   //我的账单
   pinyouPay: api+'/pinyou/dopay',
   //我发布的
   myPinyou: api+'/pinyou/get_userlist',
   //获取包裹列表   
   goodsList : api+'/goods/getlist', 
   //添加包裹
   goodsSubmit : api+'/goods/publish', 
   //获取包裹信息
   goodsInfo: api+'/goods/getinfo',
   //编辑包裹信息
   goodsEdit: api+'/goods/edit',   
   //删除包裹
   goodsDel: api+'/goods/delete',

   //时事热点
   news: api+'/News/getlists',

   //地铁线路
   subway: api+'/Category/subway',
}


function GetRequest() {   
   var url = location.search; //获取url中"?"符后的字串   
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {
         if (strs[i]!='remark=%E4%B8%8D%E9%99%90' && strs[i]!='singleType=%E4%B8%8D%E9%99%90'){
            //theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
            theRequest[strs[i].split("=")[0]]=strs[i].split("=")[1];
         }            
      }   
   }   
   return theRequest;   
}

var request = GetRequest();
if (request.userid){
   localStorage.setItem('userid',request.userid);
};