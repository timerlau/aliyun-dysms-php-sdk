<?php
/**
 * 配置信息
 *
 * @author 	songmw<imphp@qq.com>
 * @since 	2018.03.05
 */
return [
	
	// 阿里云 KEY & SECRET
	'ACCESS_KEY_ID'=>env('SMS_ACCESS_KEY_ID'),
	'ACCESS_KEY_SECRET'=>env('SMS_ACCESS_KEY_SECRET'),

	// 默认短信签名
	'default_sign_name'=>env('SMS_DEFAULT_SIGN_NAME'),

	// 默认短信模板编号
	'default_template_code'=>env('SMS_DEFAULT_TEMPLATE_CODE'),
];
