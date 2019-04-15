layui.use(['upload','layer','fsConfig','fsCommon'], function(){
	var upload = layui.upload,
	 	fsConfig = layui.fsConfig,
	  	layer = layui.layer,
	  	fsCommon = layui.fsCommon,
		statusName = $.result(fsConfig,"global.result.statusName","errorNo"),
  		msgName = $.result(fsConfig,"global.result.msgName","errorInfo"),
		successNo = $.result(fsConfig,"global.result.successNo","0"),
  		uploadUrl = $.result(fsConfig,"global.uploadUrl",""),
  		dataName = $.result(fsConfig,"global.result.dataName","results.data");
  	//多图
	if($("#muploud").length>0){
		var btns = $("#muploud");
		var urls = btns.attr("url");
		var tags = btns.attr("tag");
		var fileExtss = btns.attr("exts");
		var fileSizes = btns.attr("size");
		var fileAccepts = btns.attr("accept");
		upload.render({
		    elem: '#muploud',
		    url: urls,
		    accept :fileAccepts,
		    exts :fileExtss,
		    size :fileSizes,
		    before: function(obj){    	
    			//layer.load(); //上传loading
		    },
		    done: function(result){
		    	//layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				_html = '<li><img src="'+data.url+'" /><input type="hidden" name="image[]" value="'+data.url+'" /><i class="layui-icon" onclick="removeLi(this)">&#x1006;</i> <a href="javascript:void(0)" onclick="setFace(this,\'thumb\',\''+data.url+'\')">设为封面</a></li>';
				$("#"+tags).append(_html);
		    }
		});
	}

	//单图
	if($("#uploud").length>0){
		var btn = $("#uploud");
		var url = btn.attr("url");
		var tag = btn.attr("tag");
		var fileExts = btn.attr("exts");
		var fileSize = btn.attr("size");
		var fileAccept = btn.attr("accept");

		upload.render({
		    elem: '#uploud',
		    url: url,
		    accept :fileAccept,
		    exts :fileExts,
		    size :fileSize,
		    before: function(obj){ 
    			//layer.load(); //上传loading
		    },
		    done: function(result){
		    	//layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				$("#"+tag+"_src").attr("src",data.url);
				$("#"+tag).val(data.url);
		    }
		});
	}	

	if($("#uploud1").length>0){
		var btn1 = $("#uploud1");
		var url1 = btn1.attr("url");
		var tag1 = btn1.attr("tag");
		var fileExts1 = btn1.attr("exts");
		var fileSize1 = btn1.attr("size");
		var fileAccept1 = btn1.attr("accept");
		upload.render({
		    elem: '#uploud1',
		    url: url1,
		    accept :fileAccept,
		    exts :fileExts1,
		    size :fileSize1,
		    before: function(obj){    	
    			//layer.load(); //上传loading
		    },
		    done: function(result){
		    	//layer.closeAll(); //关闭loading
		    	if(result[statusName] != successNo){
					var filters = fsConfig["filters"];
				   	if(!$.isEmpty(filters)){
			     	  var otherFunction = filters[result[statusName]];
			      	  if($.isFunction(otherFunction)){
			       	     otherFunction(result);
			      	      return;
			     	   }
				    }
			   		fsCommon.errorMsg(result[msgName]);
			   		return;
				}
				
				//上传成功后，返回文件路径
				var data = $.result(result,dataName);
				$("#"+tag1+"_src").attr("src",data.url);
				$("#"+tag1).val(data.url);
		    }
		});
	}
})

function setFace(obj,to,str){
	$("#"+to).val(str);
	$(".insert-img li a").removeClass("active");
	$(obj).addClass("active");
}

function removeLi(obj) {
	console.info($(obj).parent());
	$(obj).parent().remove();
}

function delImage(domid){
	$("#"+domid+"_url").val("");
	$("#"+domid+"_src").attr('src','/tpl/static/image/image.jpg');
}