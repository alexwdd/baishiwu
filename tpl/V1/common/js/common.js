// JavaScript Document
$(document).ready(function() {
   
    //ajaxform
    var options = { 
        beforeSubmit:showRequest,//表单验证方式
        success:showResponse  // post-submit callback 
    }; 

    $('#ajaxForm').ajaxForm(options); 

    function showResponse(responseText, statusText)  {
        $('#ajaxForm button').removeAttr('disabled');
        $('#ajaxForm input').removeAttr('disabled');
        layer.close(load);
        if (statusText == 'success') {
            layer.close(load);
            if (responseText.status==1) {
                if (responseText.info==''){
                    if(responseText.url!='' && responseText.url!=undefined && responseText.url!="undefined"){
                        if (responseText.url=='reload') {
                            window.location.reload();
                        }else{
                            window.location.href = responseText.url;
                        }                                   
                    }
                }else{
                    layer.open({
                        content:responseText.info,
                        time: 2, //2秒后自动关闭
                        end: function(){ 
                            if(responseText.url!='' && responseText.url!=undefined && responseText.url!="undefined"){
                                if (responseText.url=='reload') {
                                    window.location.reload();
                                }else{
                                    window.location.href = responseText.url;
                                }                                   
                            }
                        }
                    });
                }                
            }else{
                layer.open({content: responseText.info});
                if ($("#verify_img").length > 0) {
                    $("#verify_img").click();
                    $("#veriCode").val("");
                }                
            }
        }else{
            layer.open({content: '服务器错误'});
        }
    } 

    function showRequest(){
        FormStatus = $("#ajaxForm").validator('isFormValid');
        if (FormStatus){
            $('#ajaxForm button').attr("disabled","disabled");
            $('#ajaxForm input').attr("disabled","disabled");
            load = layer.open({type: 2,content:'处理中...'});
        }
        return FormStatus;
    }

    $('#ajaxForm').validator({
        //通过时回调
        onValid: function(validity) {
            $(validity.field).closest('.my-form-group').find('.form-msg').hide();
        },

        //异常时回调
        onInValid: function(validity) {   

            var $field = $(validity.field);
            var $group = $field.closest('.my-form-group');
            var $alert = $group.find('.form-msg');

            // 使用自定义的提示信息 或 插件内置的提示信息
            var msg = $field.data('validationMessage') || this.getValidationMessage(validity);
            $alert.html(msg).show();
        },
    });
});

// JavaScript Document
(function($) {
    if ($.AMUI && $.AMUI.validator) {
        // 增加单个正则
        $.AMUI.validator.patterns.mobile = /^1[3|4|5|7|8][0-9]{9}$/;
        $.AMUI.validator.patterns.password = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/;
        $.AMUI.validator.patterns.name = /^[\u4e00-\u9fa5]+$/;
        $.AMUI.validator.patterns.sn = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}[0-9Xx]$)/;

    }
})(window.jQuery);


function checkMobile(v){
    if(v==""){
        return false;
    }else{
        var pattern = /^1[3|4|5|7|8][0-9]{9}$/;
        flag = pattern.test(v);
        if(!flag){
            return false;
        }else{
            return true;
            }
    }
}

function uploadFile(url, obj) {
    $("#fileupload").click();
    //文件上传js
    var btn = $(".uploadbtn");
    var upload = "";
    $("#myupload").attr("action", url);
    $("#fileupload").bind("change", function() {  
        str = obj;      
        if ($(this).val() != "") {
            $("#myupload").ajaxSubmit({
                dataType: 'json',
                beforeSend: function() {
                    load = layer.open({type: 2,content:'上传中...'});
                },
                success: function(data) {
                    $("#fileupload").unbind("change");
                    layer.close(load);
                    $("#myupload")[0].reset();      
                    if (data.state == "SUCCESS") {
                        $("#" + str).val(data.url);
                        $("#" + str + "_src").html("<img src='"+data.url+"'/>");
                    } else {
                          layer.open({content: data.state});
                    }
                },
                error: function(xhr) {
                    btn.val("上传失败");
                }
            })
        }
    })
}

function uploadMultiFile(url, o) {

    if ($(".insert-img li").length>=5){
        layer.open({content: '最多上传5张',skin: 'msg',time: 2});
        return;
    }

    $("#fileupload").click();
    //文件上传js
    var btn = $(".uploadbtn");
    var upload = "";
    $("#myupload").attr("action", url);
    $("#fileupload").bind("change", function() {
        if ($(this).val() != "") {
            $("#myupload").ajaxSubmit({
                dataType: 'json',
                beforeSend: function() {
                    load = layer.open({type: 2,content:'上传中...'});
                },
                success: function(data) {
                    $("#fileupload").unbind("change");
                    layer.close(load);
                    if (data.state == "SUCCESS") {
                        _html = '<li><img src="'+data.url+'" /><input type="hidden" name="image[]" value="'+data.url+'" /><i class="am-icon-close" onclick="removeLi(this)"></i></li>';
                        $(".insert-btn").before(_html);
                    } else {
                        layer.msg(data.state, {
                            time: 2000,
                            icon: 2
                        });
                    }
                },
                error: function(xhr) {
                    btn.val("上传失败");
                }
            })
        }
    })
}

function removeLi(obj) {
    console.info($(obj).parent());
    $(obj).parent().remove();
}

function bindMobile(){
    layer.open({
        content: '请先绑定手机号码',
        btn: '去绑定',
        end: function(index){
            window.location.href='/index.php/Home/Member/mobile';
        }
    });
}