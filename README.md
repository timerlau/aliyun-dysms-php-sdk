# AliyunSms
<p>阿里云短信服务SDK封装 for Laravel</p>

# 系统要求
````
php >= 5.5
laravel >= 5.4
guzzlehttp/guzzle >= 6.0
````

# 说明

- 没有对阿里云的SDK做任何修改！可以放心使用！
- master分支修复了阿里云SDK的一个[BUG](https://github.com/timerlau/aliyun-dysms-php-sdk/commit/0b37c4a93b8bfc4ae59f678be6bc68da0e905c48)
- nofix分支保持阿里云SDK原样
- 集成了 api_sdk，msg_sdk。不过根据实际使用情况来说，建议设置callback地址更为方便。msg_sdk的队列消息处理不当会很乱。

# 安装
````
composer require timerlau/aliyun-dysms-php-sdk 
````

# 配置文件
````

修改 config/app.php 如下：
1. providers 增加 Timerlau\AliyunSms\AliyunSmsServiceProvider::class,
2. aliases   增加 'Sms' => Timerlau\AliyunSms\Facades\Sms::class,

php artisan vendor:publish --provider="Timerlau\AliyunSms\AliyunSmsServiceProvider"

修改 config/sms.php

// 阿里云 KEY & SECRET
'ACCESS_KEY_ID'=>env('SMS_ACCESS_KEY_ID'),
'ACCESS_KEY_SECRET'=>env('SMS_ACCESS_KEY_SECRET'),

// 默认短信签名
'default_sign_name'=>env('SMS_DEFAULT_SIGN_NAME'),

// 默认短信模板编号
'default_template_code'=>env('SMS_DEFAULT_TEMPLATE_CODE'),
````

# 使用
````
use Sms;

// 默认的发送
$state = Sms::phone('手机号')->assign(['code'=>'123456'])->send();

// 设置手机号，赋值变量，选择模板等操作
$sms = Sms::phone($mobile);
$state = $sms->outId($outid)->assign(['code'=>$code])->send();

if (!$state) {
    dd($sms->getException());
} else {
    echo '发送成功 <br>';
    dd('发送回执号: ' . $sms->bizId);
    dd('hello, sms');
}

// 短信发送状态 bizid可选
$sms = Sms::phone($mobile);
$response = $sms->bizId($biz_id)->history();

// 使用MNS消息队列，查询手机接收状况 （消息队列不太好，建议使用 HTTP批量推送模式）
// 需要开启MNS

// 发送短信
$sms = Sms::phone($mobile);
$state = $sms->outId($outid)->assign(['code'=>$code])->send();

if (!$state) {
    dd($sms->getException());
} else {
    echo '发送成功 ' . $mobile . '  <br>';
    echo '发送回执号: ' . $sms->bizId . '<br>';

    $bool = $sms->receive(function ($message) {
        dump($message);
        return true;
    });

    if ($bool !== True) {
        dump($bool);
        dd('获取短信状态异常');
    }
    dump('hello, sms');
    dd($bool);
}

// 批量发送短信
$phone = ['176xxx','186xxx','187xxx'];
$sign = ['签名1', '签名1', '签名1'];
$template = 'SMS_xxx';

$assign = [];
foreach ($phone as $mobile) {
    $assign[]['code'] = mt_rand(1000, 9999);
}

$sms = Sms::phone($phone)->sign($sign)->template($template)->assign($assign);
dd($sms->multiSend());
````
