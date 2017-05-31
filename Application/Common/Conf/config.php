<?php
return array(
    // 配置数据库连接
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '192.168.16.72', // 服务器地址
    'DB_NAME'               =>  'an_db',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',          // 密码
    'DB_PORT'               =>  3306,        // 端口
    'DB_PREFIX'             =>  'an_',    // 数据库表前缀

    'SEND_EMAIL_HOST' => '',
    'SEND_EMAIL_USER' => '',
    'SEND_EMAIL_PWD' => '',
    'SEND_EMAIL_SECURE' => 'ssl',
    'SEND_EMAIL_PORT' => 465,
    'SEND_EMAIL_SENDER' => '阿纳巴里官方',

    // 通常在实际项目中会选择使用URL重写模式
    'URL_MODEL' => 2,

    // 页面调试功能
    //'SHOW_PAGE_TRACE' => true,

    'TMPL_PARSE_STRING' => [
        '__CSS__' =>  '/Public/css',
        '__JS__' =>  '/Public/js',
        '__IMG__' => '/Public/images',
        '__ZTREE__' => '/Public/ztree',
        '__PUBLIC__'=>'/Public',
    ],

    'TOKEN_ON'      =>    false,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'    =>    '__hash__',    //令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true

    // 指定缓存的存储方式
   // 'DATA_CACHE_TYPE' =>  'Redis',

    'TMPL_EXCEPTION_FILE'=>'/Public/404.html', // 定义错误跳转页面URL地址

    // 发送短信配置
    'PHONE_API_APP_KEY'=>'a7cacd2316e124a3bc0d07d97bf969eb',
    'VERIFY_CODE_TPL'=>'【阿纳巴里】您的验证码是{验证码}',

    // 绑定上传方式
    'FILE_UPLOAD_TYPE'    =>    'Qiniu',
    'UPLOAD_TYPE_CONFIG'  =>    array(
        'secretKey'      => 'V9vJxZ7Wc5AeZKXq0XbnJStPDovpLQsKX8qCUeQr', //七牛密码
        'accessKey'      => 'Z5oNrz5L2D_XZXW4sEAv_KHOVflPgUKaAXukAKvB', //七牛用户
        'domain'         => 'oomv52gxr.bkt.clouddn.com', //域名
        'bucket'         => 'macarin', //空间名称
        'timeout'        => 300, //超时时间
    ),

        //支付宝配置参数
    'alipay_config'=>array(
        'partner' =>'2017020605533444',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'jtyVebPHJ1WRNq/2PU/S+w==',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
    ),
        //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'15828562743',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://www.a.com/Pay/notifyurl',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://www.a.com/Pay/returnurl',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'Pay/successPage?ordtype=payed',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'Pay/failedPage?ordtype=unpay',
    ),
);