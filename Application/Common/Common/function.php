<?php

/**************云片短信代码接口***********************************************/
//获得账户
function yunpian_get_user($ch, $apikey)
{
    curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/user/get.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
    return curl_exec($ch);
}

// 发送文本短信
function yunpian_send($ch, $data)
{
    curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/send.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

// 发送模板短信
function yunpian_tpl_send($ch, $data)
{
    curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/tpl_send.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

// 发送语音短信
function yunpian_voice_send($ch, $data)
{
    curl_setopt($ch, CURLOPT_URL, 'http://voice.yunpian.com/v1/voice/send.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

/**
 * 发送短信
 *
 * @param string $mobile 接收人手机号码
 * @param string $text 短信内容
 * @param mixed $systemInfo 配置信息
 * @param int $type 短信类型 1 验证码（只需要给验证码值） 2 普通短信
 *
 * @return bool 失败返回错误信息 成功返回true 判断请判断 ===
 */
function sendSMS($mobile = '', $text, $systemInfo, $type = 1)
{
    header("Content-Type:text/html;charset=utf-8");
    $apikey = $systemInfo['phone_api_app_key']; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取

    // 判断短信类型
    if ($type == 1) {
        // 短信类型
        $text = str_replace('{验证码}', $text, $systemInfo['verify_code_tpl']);
    }
//        $text = '【xxx】您的验证码是1234';
    // 初始化curl
    $ch = curl_init();

    /* 设置验证方式 */
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));

    /* 设置返回结果为流 */
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    /* 设置超时时间*/
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    /* 设置通信方式 */
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // 发送短信
    $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
    $json_data = yunpian_send($ch, $data);
    $array = json_decode($json_data, true);


    curl_close($ch);
    // 判断短信是否发送成功
    if ($array['msg'] == 'OK') {
        return true;
    } else {
        return $array['detail'];
    }

}

//////////////////////////////////////////////////////////////////////////云片短信代码
/**
 * 发送模板短信
 *
 * @param string $mobile 接收人手机号码
 * @param string $text 模板内容 列子
 * ('#code#').'='.urlencode('1234').'&'.urlencode('#company#').'='.urlencode('欢乐行')
 * @param mixed $systemInfo 配置信息
 * @param int $tpl_id 模板Id
 *
 * @return bool 失败返回错误信息 成功返回true 判断请判断 ===
 */
function sendSMSTemp($mobile = '', $text = '', $systemInfo, $tpl_id = 1)
{
    header("Content-Type:text/html;charset=utf-8");
    $apikey = $systemInfo['phone_api_app_key']; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取

    // 初始化curl
    $ch = curl_init();

    /* 设置验证方式 */
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));

    /* 设置返回结果为流 */
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    /* 设置超时时间*/
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    /* 设置通信方式 */
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


    // 发送模板短信
    // 需要对value进行编码
    $data = [
        'tpl_id'    => $tpl_id, // 模板id
        'tpl_value' => $text,
        'apikey'    => $apikey,
        'mobile' => $mobile
    ];
    $json_data = tpl_send($ch, $data);
    $array = json_decode($json_data, true);


    curl_close($ch);
    // 判断短信是否发送成功
    if ($array['msg'] == 'OK') {
        return true;
    } else {
        return $array['detail'];
    }

}

//////////////////////////////////////////////////////////////////////////云片短信代码->模板

function tpl_send($ch, $data)
{
    curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/tpl_send.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

function dataNum($data1,$data2){
    return intval(($data2-$data1)/86400);
}

/**
 * 获取用户等级名称
 *
 * @param $level
 *
 * @return string
 */
function getUserLevelsName($level)
{
    $levelName = '';
    switch ($level) {
        case 0:
            $levelName = '会员';
            break;
        case 1:
            $levelName = '支持者';
            break;
        case 2:
            $levelName = '经纪人';
            break;
        case 3:
            $levelName = '制片人';
            break;
        case 4:
            $levelName = '出品人';
            break;
    }
    return $levelName;
}

/**
 * 获取项目进度
 *
 * @param $level
 *
 * @return string
 */
function getProjectSpeed($level)
{
    $levelName = '';
    switch ($level) {
        case 1:
            $levelName = '筹备中';
            break;
        case 2:
            $levelName = '开拍';
            break;
        case 3:
            $levelName = '杀青';
            break;
        case 4:
            $levelName = '公演';
            break;
        case 5:
            $levelName = '下线';
            break;
    }
    return $levelName;
}

/**
 * 电话号
 */
function telephoneNumber($tel){

    if(empty($tel)) return false;

    $phone = substr($tel,0,3).'****'.substr($tel,7,4);

    return $phone;

}

/**
 * 记录管理员的操作日志
 */
function adminLogs($user,$type,$event,$create_time,$money,$remark,$left_money,$admin){

    $insertData = [
        'admin'=>$admin,
        'user'=>$user,
        'type'=>$type,
        'event'=>$event,
        'create_time'=>$create_time,
        'money'=>$money,
        'remark'=>$remark,
        'left_money'=>$left_money,
    ];

    //>> 将记录写入数据库
    M('AdminLogs')->add($insertData);

}

