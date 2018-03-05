# AliyunSms
<p>阿里云短信服务SDK封装 for Laravel</p>

# 系统要求
````
php >= 5.5
laravel >= 5.4
guzzlehttp/guzzle >= 6.0
````

# 说明
````
1. 没有对阿里云的SDK做任何修改！可以放心使用！
2. 目前仅集成了 api_sdk，后续增加 msg_sdk。
````

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
$state = Sms::phone('设置手机号')
                ->sign('设置签名')
                ->template('设置模板code')
                ->outId('设置流水号')
                ->assign(['code'=>'123456'])
                ->send();

if (!$state) {
    dd(Sms::getException());
} else {
    dd('hello, sms');
}
````