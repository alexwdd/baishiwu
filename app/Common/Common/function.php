<?php
/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item) 
    {
        $result[] = array($item);
    }       

    foreach($data as $key=>$item) 
    {                                
        $result = combineArray($result,$item);
    }
    return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
*/
function combineArray($arr1,$arr2) {         
    $result = array();
    foreach ($arr1 as $item1) 
    {
        foreach ($arr2 as $item2) 
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}

function getStatus($v){
    switch ($v) {
        case 0:
            return '处理中';
            break;
        case 1:
            return '已发货';
            break;
        case 2:
            return '已收货';
            break;
        default:
            # code...
            break;
    }
}

function getPayStatus($v){
    switch ($v) {
        case 0:
            return '<span class="red">待付款</span>';
            break;
        case 1:
            return '<span class="green">已付款</span>';
            break;
    }
}

function getOrderStatus($v){
    switch ($v) {
        case 0:
            return '<span class="red">待付款</span>';
            break;
        case 1:
            return '<span class="blue">待审核</span>';
            break;
        case 2:
            return '<span class="green">待发货</span>';
            break;
        case 3:
            return '<span class="green">已发货</span>';
            break;
        case 99:
            return '<span class="red">交易失败</span>';
            break;
        default:
            # code...
            break;
    }
}

function getLastTime($targetTime){
    // 今天最大时间
    $todayLast   = strtotime(date('Y-m-d 23:59:59'));
    $agoTimeTrue = time() - $targetTime;
    $agoTime     = $todayLast - $targetTime;
    $agoDay      = floor($agoTime / 86400);

    if ($agoTimeTrue < 60) {
        $result = '刚刚';
    } elseif ($agoTimeTrue < 3600) {
        $result = (ceil($agoTimeTrue / 60)) . '分钟前';
    } elseif ($agoTimeTrue < 3600 * 12) {
        $result = (ceil($agoTimeTrue / 3600)) . '小时前';
    } elseif ($agoDay == 0) {
        $result = '今天 ' . date('H:i', $targetTime);
    } elseif ($agoDay == 1) {
        $result = '昨天 ' . date('H:i', $targetTime);
    } elseif ($agoDay == 2) {
        $result = '前天 ' . date('H:i', $targetTime);
    } elseif ($agoDay > 2 && $agoDay < 16) {
        $result = $agoDay . '天前 ' . date('H:i', $targetTime);
    } else {
        $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
        $result = date($format, $targetTime);
    }
    return $result;
}

function n2br($str){
	if ($str!='') {
		return str_replace("\n","<br />",$str);
	}	
}

function getFace($v){
    if ($v=='') {
        return RES.'/image/face.jpg';
    }else{
        return $v;
    }
}

function returnJson($code,$msg,$data=null){
    echo json_encode(array(
        'ver'=>'1.0.0',
        'code'=>$code,
        'desc'=>$msg,
        'body'=>$data
    ));
    /*dump(array(
        'ver'=>'1.0.0',
        'code'=>$code,
        'desc'=>$msg,
        'body'=>$data
    ));*/
    die;
}

function getRealUrl($value){
    $first = substr($value,0,1);
    if ($first == '/') {
        return C('site.domain').$value;
    }else{
        return $value;
    }
}

function zero($v){
    return strlen($v)==1?'0'.$v:$v;
}

function getSex($v){
    if ($v==1) {
        return '男';
    }elseif($v==2){
        return '女';
    }else{
        return '未知';
    }
}

//会员日期格式化
function dataformat($num) {
    if ($num<0) {
        $num=0;
    }
    $hour = floor($num/3600);
    $minute = floor(($num-$hour*3600)/60);
    $second = floor(($num-60*$minute)%60);
    $str = '';
    if ($hour>0) {
        $str .= $hour.'小时';
    }
    if ($minute>0) {
        $str .= $minute.'分';
    }
    $str .= $second.'秒';
    return $str;
}

// 手机号检测
function check_mobile($mobile){
    $mobilepreg = '/^1[3|4|5|7|8][0-9]{9}$/';
    if (!preg_match($mobilepreg, $mobile)) {
        return false;
    }else {
        return true;
    }
}

function check_email($email){
    $emailpreg = '/^([a-zA-Z0-9_-])+(\.([a-zA-Z0-9_-])+)*@([a-zA-Z0-9_-])+(\.([a-zA-Z0-9_-])+)+$/';
    if (!preg_match($emailpreg, $email)) {
        return false;
    }else{
        return true;
    }
}

function getCateName($v){
    if (!is_numeric($v)) {
      return;die;
   }

   $map['id'] = $v;
   $name = M('Category')->where($map)->getField('name');
   if ($name) {
      return $name;
   }else{
      return 'error';
   }
}

function jsonName($str) {
    if($str){
        $tmpStr = json_encode($str);
        $tmpStr2 = preg_replace("#(\\\ud[0-9a-f]{3})#ie","",$tmpStr);
        $return = json_decode($tmpStr2);
        if(!$return){
            return jsonName($return);
        }
    }else{
        $return = '微信用户';
    }    
    return $return;
}

//检查提交数据
function checkRequest(){
    $url = $_SERVER["HTTP_REFERER"]; 
    $str = str_replace("http://","",$url);
    $strdomain = explode("/",$str); 
    $port = $_SERVER['SERVER_PORT']; 
    if ($port=="80") {
        $server = $_SERVER['SERVER_NAME'];
    }else{
        $server = $_SERVER['SERVER_NAME'].":".$port;
    }
    $domain = $strdomain[0]; 
    if ($domain != $server){ //站外提交
        return false;
    }else{
        return checkFormDate();
    }
}

//过滤数据
function checkFormDate(){
    foreach ($_REQUEST as $key => $value){
        if (inject_check($value) && $key!='images') {   
            return false;
            break;
        } 
    }
    return true;
}

function inject_check($sql_str) {  
    return preg_match("/select|inert|script|update|delete|UNION|into|load_file|outfile/i", $sql_str);
} 

function inject_replace($sql_str) {  
    return preg_replace("/select|inert|script|update|delete|UNION|into|load_file|outfile/i","",$sql_str);
} 


//图片生成缩略图
function getThumb($path, $width, $height) {
    if(file_exists(".".$path) && !empty($path)){
        $fileArr = pathinfo($path); 
        $dirname = $fileArr['dirname']; 
        $filename = $fileArr['filename']; 
        $extension = $fileArr['extension']; 

      if ($width > 0 && $height > 0) { 
          $image_thumb =$dirname . "/" . $filename . "_" . $width . "_" .$height. "." . $extension; 
          if (!file_exists(".".$image_thumb)) { 
              $image = new \Think\Image(); 
              $image->open(".".$path); 
              $image->thumb($width, $height,3)->save(".".$image_thumb);
          } 
          $image_rs = $image_thumb; 
      } else { 
          $image_rs = $path; 
      } 
      return $image_rs;
    }else{
      return false;
    } 
}


/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($config_key,$data = array()){
    $param = explode('.', $config_key);
    if(empty($data)){
        //如$config_key=shop_info则获取网站信息数组
        //如$config_key=shop_info.logo则获取网站logo字符串
        $config = F($param[0],'',TEMP_PATH);//直接获取缓存文件
        if(empty($config)){
            //缓存文件不存在就读取数据库
            $res = D('Config')->where("inc_type='$param[0]'")->select();
            if($res){
                foreach($res as $k=>$val){
                    $config[$val['name']] = $val['value'];
                }
                F($param[0],$config,TEMP_PATH);
            }
        }
        if(count($param)>1){
            return $config[$param[1]];
        }else{
            return $config;
        }
    }else{
        //更新缓存
        $result =  D('Config')->where("inc_type='$param[0]'")->select();
        if($result){
            foreach($result as $val){
                $temp[$val['name']] = $val['value'];
            }
            foreach ($data as $k=>$v){
                $newArr = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
                if(!isset($temp[$k])){
                    M('Config')->add($newArr);//新key数据插入数据库
                }else{
                    if($v!=$temp[$k])
                        M('Config')->where("name='$k'")->save($newArr);//缓存key存在且值有变更新此项
                }
            }
            //更新后的数据库记录
            $newRes = D('Config')->where("inc_type='$param[0]'")->select();
            foreach ($newRes as $rs){
                $newData[$rs['name']] = $rs['value'];
            }
        }else{
            foreach($data as $k=>$v){
                $newArr[] = array('name'=>$k,'value'=>trim($v),'inc_type'=>$param[0]);
            }
            M('Config')->addAll($newArr);
            $newData = $data;
        }
        return F($param[0],$newData,TEMP_PATH);
    }
}

//发邮件
function think_send_mail($email, $name='', $title, $content) {
    $config = tpCache('email');
    import("Common.ORG.PHPMailer.PHPMailerAutoload");
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP 
    $mail->SMTPDebug = 0; // 关闭SMTP调试功能
    $mail->Host = $config['SMTP_HOST']; //smtp服务器的名称
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl"; 
    $mail->SMTPAuth = true; //启用smtp认证
    $mail->Username = $config['SMTP_USER']; //你的邮箱名
    $mail->Password = $config['SMTP_PASS']; //邮箱密码
    $mail->From = $config['FROM_EMAIL']; //发件人地址（也就是你的邮箱地址）
    $mail->FromName = $config['FROM_NAME']; //发件人姓名
    $mail->AddAddress($email, $name); //添加收件人
    $mail->WordWrap = 200; //设置每行字符长度
    $mail->IsHTML(true); // 是否HTML格式邮件
    $mail->CharSet = "utf-8"; //设置邮件编码
    $mail->Subject = $title; //主题
    $mail->Body = $content; //内容
    $mail->AltBody = $content; //正文不支持HTML的备用显示
    return $mail->Send();
}

//短信宝发短信验证码
function send_sms($mobile,$content){
    $smsapi = "http://api.smsbao.com/";
    $user = "shanguang02"; //短信平台帐号
    $pass = md5("q62466173"); //短信平台密码    
    if (strlen($mobile)==10) {
        $mobile = '+61'.$mobile;
    }elseif(strlen($mobile)==8){
        $mobile = '+65'.$mobile;
    }      
    $sendurl = $smsapi."wsms?u=".$user."&p=".$pass."&m=".urlencode($mobile)."&c=".urlencode($content);
    //$sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".urlencode($mobile)."&c=".urlencode($content);
    $result =file_get_contents($sendurl);
    return $result;
}

//阿里发短信验证码
function send_sms_code($mobile, $code) {
    import("Common.ORG.alisms.aliyun-php-sdk-core.Config");
    import("Common.ORG.alisms.SendSmsRequest");
    $config = tpCache("sms");
    //此处需要替换成自己的AK信息
    $accessKeyId = $config['sms_appkey'];
    $accessKeySecret = $config['sms_secretKey'];
    //短信API产品名
    $product = "Dysmsapi";
    //短信API产品域名
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region
    $region = "cn-hangzhou";
    
    //初始化访问的acsCleint
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
    $acsClient= new DefaultAcsClient($profile);
    
    $request = new Alisms\SendSmsRequest;
    //$request = new SendSmsRequest;
    
    //必填-短信接收号码
    $request->setPhoneNumbers($mobile);
    //必填-短信签名
    $request->setSignName($config['sms_sign']);
    //必填-短信模板Code
    $request->setTemplateCode($config['sms_template']);
    //选填-假如模板中存在变量需要替换则为必填(JSON格式)
    $request->setTemplateParam("{\"code\":\"$code\"}");
    //选填-发送短信流水号
    //$request->setOutId("1234");
    
    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);
    if ($acsResponse->Code=='OK'){        
        return true;        
    }else{
        return false;
    }
}

//生成数字订单号
function getOrderNo($fix='') {
    $curDateTime = $fix.date("YmdHis");
    $randNum = rand(1000, 9999);
    $order_no = $curDateTime . $randNum;
    return $order_no;
} 

function getStoreOrderNo($fix='') {
    $randNum = rand(10000000, 99999999);
    return $randNum;die;
} 

//生成随机字符串
function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
} 


function isMobile() { 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    } 

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 

    // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
        return true;
        } 
    } 
    return false;
}
?>