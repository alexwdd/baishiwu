mui.ready(function(){
	mui.showLoading('数据加载中');
	var request = GetRequest();
	$("#cityID").val(request.cityID);
	$("#type").val(request.type);
	$("#sort").val(request.sort);
	$("#sortName").html(request.sortName);
	$.post(
		config.userinfo,
		{
	        userid:request.userid
	   	},
	   	function(res){
        	mui.hideLoading();
			if(res.code=='0'){
				data = res.body;	
				$("#userid").val(data.userid);
				$("#password").val(data.password);		
				$("#openid").val(data.openid);
				if (data.name!=''){
					$("#contact").val(data.name);
				}else{
					$("#contact").val(data.nickname);
				}				
				$("#phone").val(data.phone);
				$("#wechat").val(data.wechat);

				//用户认证
				if (data.phone=='' && (request.type=='ms' || request.type=='xp' || request.type=='sh' || (request.type =='zf' && request.houseType == 1))){
					mui.alert('请先完成手机认证，才能在本模块发布信息！', '系统消息', function(){
						window.location.href = '../account/auth.html?userid='+data.userid;
					})
				}
			}else{
				mui.toast(res.desc);
			}
    	},
    	"json"
    );
	
	$('#submitBtn').click(function(){
	    var res = muiFormCheck('#myForm');
	    //提交
	    if(res){
	    	//预处理
	    	var data = mui.getFormData('#myForm');	

	    	if (data.type=='esc' || data.type=='tc' || data.type=='sp' || data.type=='zf'){
	    		if (data.phone=='' && data.wechat==''){
	    			mui.toast('电话和微信必须填写一个');
	    			return false;
	    		}
	    	}
	    	//招聘 租房薪资
	    	if ($("#type").val()=='zp' || $("#type").val()=='zf'){
	    		unit = $("#unitBtn").html();
	    		if (unit!='' && unit!=undefined && $("#price").val()!=0){
	    			price = $("#price").val()+unit
	    			data.price = price;  
	    		};	    		
	    	}
	    	data.detail = data.detail+"\n记得关注微信公众号阿德莱德眼adleye3^-^";
	    	console.log(JSON.stringify(data));
	    	mui.post(config.publish,data,function(r){
				if (r.code=='0'){
					mui.alert('发帖成功！等待客服认证信息！', '系统消息', function(){
						window.location.href = 'app://goback';
					})          
				}else{
					mui.toast(r.desc);
				}
			},'json');
	    }
	});
})

function uploadMultiFile(o){
	$("#uploadfile").click();
	$("#uploadfile").bind("change", function(){
		if ($(this).val() != "") {
			mui.showLoading('上传中');
			var formdata = new FormData();
			formdata.append("file", $('#uploadfile')[0].files[0]);
			$.ajax({
				url: config.upload,
				type: 'POST',
				cache: false,
				data: formdata,
				dataType:'json',
				//必须false才会避开jQuery对 formdata 的默认处理 
				// XMLHttpRequest会对 formdata 进行正确的处理
				processData: false,
				//必须false才会自动加上正确的Content-Type 
				contentType: false,
				xhrFields: {
				   withCredentials: true
				},
				success: function(res) {
					mui.hideLoading();
					if(res.code=='0'){
						$("#uploadfile").unbind("change");
						$("#uploadfile").val("");
						data = res.body;
						_html = '<li><img src="'+data.thumb_s+'" /><i class="mui-icon mui-icon-closeempty" onclick="removeLi(this)"></i><a href="javascript:void(0)" onclick="setFace(this,\'thumb\',\''+data.thumb_s+'\',\''+data.thumb_b+'\')">设为封面</a></li>';
                        $("#"+o).before(_html);
                        if ($("#thumb_s").val()==''){
                        	$("#thumb_s").val(data.thumb_s);
                        	$("#thumb_b").val(data.thumb_b);
                        }
                        var imgpath = ''
                        $("#insert-img img").each(function(index){
                        	url = $(this).attr('src');
                        	url = url.replace("_240_180", "");
                        	if (index==0){
                        		imgpath += url;
                        	}else{
                        		imgpath += ';' + url;
                        	}                        	
                        });
                        $("#imgpath").val(imgpath);
					}else{
						mui.toast(res.desc);
					}
				}
 			})
        }
	});
}

function removeLi(obj) {
    $(obj).parent().remove();
    imgpath = '';
    $("#insert-img img").each(function(index){
    	url = $(this).attr('src');
    	url = url.replace("_240_180", "");
    	if (index==0){
    		imgpath += url;
    	}else{
    		imgpath += ';' + url;
    	}                        	
    });
    $("#imgpath").val(imgpath);
}

function setFace(obj,to,thumb_s,thumb_b){
	$("#"+to+"_s").val(thumb_s);
	$("#"+to+"_b").val(thumb_b);
	$(".insert-img li a").removeClass("active").html('设为封面');
	$(obj).addClass("active").html('封面');
}

function setAddress(address,lng,lat){
	$("#address").val(address);
	$("#address_str").html('<span>'+address+'</span>');
	$("#longitude").val(lng);
	$("#latitude").val(lat);
}