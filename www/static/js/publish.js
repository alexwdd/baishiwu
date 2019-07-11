mui.ready(function(){
    mui.showLoading('数据加载中');
    var request = GetRequest();
    $("#cityID").val(request.cityID);
    $("#type").val(request.type);
    $("#sort").val(request.sort);
    str = request.sortName;
    $("#sortName").html(unescape(str.replace(/\\u/g, '%u')));
    //$("#sortName").html(request.sortName);

    if (request.cityID==39){
        $("#phone").attr("placeholder","联系电话：87654321（选填）");
    }
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
                        window.location.href = '../account/auth.html?userid='+data.userid+"&cityID="+data.cityID;
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
            if (data.cityID==2){
                data.detail = data.detail+"\n联系时请说明是在堪城圈看到的";
            }
            if (data.cityID==9){
                data.detail = data.detail+"\n记得关注微信公众号阿德莱德眼, 微信分享信息请加adleye3";
            }            
            $('#submitBtn').attr("disabled","disabled");
            mui.post(config.publish,data,function(r){
                if (r.code=='0'){
                    mui.alert('发帖成功！', '系统消息', function(){
                        window.location.href = 'app://goback';
                    })          
                }else{
                    $('#submitBtn').removeAttr('disabled');
                    mui.toast(r.desc);
                }
            },'json');
        }
    });
})

var xhr;
function uploadMultiFile(o){
	$("#uploadfile").click();
	$("#uploadfile").unbind().bind("change", function(){
		if ($(this).val() != "") {
			mui.showLoading('上传中');
			var fileObj = document.getElementById("uploadfile").files[0]; // js 获取文件对象
            var url = config.upload; // 接收上传文件的后台地址 

            var form = new FormData(); // FormData 对象            
            if(fileObj.size/1024 > 1025) { //大于1M，进行压缩上传
                photoCompress(fileObj, {
                    quality: 0.2
                }, function(base64Codes){
                    //console.log("压缩后：" + base64Codes.length / 1024 + " " + base64Codes);
                    var bl = convertBase64UrlToBlob(base64Codes);
                    form.append("file", bl, "file_"+Date.parse(new Date())+".jpg"); // 文件对象
                    xhr = new XMLHttpRequest();  // XMLHttpRequest 对象
                    xhr.open("post", url, true); //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。
                    xhr.onload = uploadComplete; //请求完成
                    xhr.onerror =  uploadFailed; //请求失败                    
                    xhr.upload.onloadstart = function(){//上传开始执行方法
                        ot = new Date().getTime();   //设置上传开始时间
                        oloaded = 0;//设置上传开始时，以上传的文件大小为0
                    };
                    xhr.send(form); //开始上传，发送form数据
                });
            }else{ //小于等于1M 原图上传
                form.append("file", fileObj); // 文件对象
                xhr = new XMLHttpRequest();  // XMLHttpRequest 对象
                xhr.open("post", url, true); //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。
                xhr.onload = uploadComplete; //请求完成
                xhr.onerror =  uploadFailed; //请求失败
                xhr.upload.onloadstart = function(){//上传开始执行方法
                    ot = new Date().getTime();   //设置上传开始时间
                    oloaded = 0;//设置上传开始时，以上传的文件大小为0
                };
                xhr.send(form); //开始上传，发送form数据
            }

			/*var formdata = new FormData();
			formdata.append("file", $('#uploadfile')[0].files[0]);
			$.ajax({
				url: config.upload,
				type: 'POST',
				cache: false,
				data: formdata,
				dataType:'json',
				timeout: 5000,
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
                        	if (index==0){
                        		imgpath += $(this).attr('src');
                        	}else{
                        		imgpath += ';' + $(this).attr('src');
                        	}                        	
                        });
                        $("#imgpath").val(imgpath);
					}else{
						mui.toast(res.desc);
					}
				}
 			})*/
        }
	});
}

/*
三个参数
file：一个是文件(类型是图片格式)，
w：一个是文件压缩的后宽度，宽度越小，字节越小
objDiv：一个是容器或者回调函数
photoCompress()
 */
function photoCompress(file,w,objDiv){
    var ready=new FileReader();
    /*开始读取指定的Blob对象或File对象中的内容. 当读取操作完成时,readyState属性的值会成为DONE,如果设置了onloadend事件处理程序,则调用之.同时,result属性中将包含一个data: URL格式的字符串以表示所读取文件的内容.*/
    ready.readAsDataURL(file);
    ready.onload=function(){
        var re=this.result;
        canvasDataURL(re,w,objDiv)
    }
}
function canvasDataURL(path, obj, callback){
    var img = new Image();
    img.src = path;
    img.onload = function(){
        var that = this;
        // 默认按比例压缩
        var w = that.width,
            h = that.height,
            scale = w / h;
        w = obj.width || w;
        h = obj.height || (w / scale);
        var quality = 0.7;  // 默认图片质量为0.7
        //生成canvas
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        // 创建属性节点
        var anw = document.createAttribute("width");
        anw.nodeValue = w;
        var anh = document.createAttribute("height");
        anh.nodeValue = h;
        canvas.setAttributeNode(anw);
        canvas.setAttributeNode(anh);
        ctx.drawImage(that, 0, 0, w, h);
        // 图像质量
        if(obj.quality && obj.quality <= 1 && obj.quality > 0){
            quality = obj.quality;
        }
        // quality值越小，所绘制出的图像越模糊
        var base64 = canvas.toDataURL('image/jpeg', quality);
        // 回调函数返回base64的值
        callback(base64);
    }
}
/**
 * 将以base64的图片url数据转换为Blob
 * @param urlData
 *            用url方式表示的base64图片数据
 */
function convertBase64UrlToBlob(urlData){
    var arr = urlData.split(','), mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while(n--){
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {type:mime});
}

//上传成功响应
function uploadComplete(evt) {
    //服务断接收完文件返回的结果
    mui.hideLoading();
    var res = JSON.parse(evt.target.responseText);
    console.log(res);
	if(res.code=='0'){
		$("#uploadfile").unbind("change");
		$("#uploadfile").val("");
		data = res.body;
		_html = '<li><img src="'+data.thumb_s+'" /><i class="mui-icon mui-icon-closeempty" onclick="removeLi(this)"></i><a href="javascript:void(0)" onclick="setFace(this,\'thumb\',\''+data.thumb_s+'\',\''+data.thumb_b+'\')">设为封面</a></li>';
        $("#insert-btn").before(_html);
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
//上传失败
function uploadFailed(evt) {
    alert("上传失败！");
}
//取消上传
function cancleUploadFile(){
    xhr.abort();
}

function removeLi(obj) {
    img = $(obj).parent().find('img');
    oldUrl = img.attr('src');
    if (oldUrl == $("#thumb_s").val()){
        $("#thumb_s").val("");
        $("#thumb_b").val("");
    }
    
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
