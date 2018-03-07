<?php
/**
 * 封装 阿里云的发短信方法
 *
 * @author  songmw<imphp@qq.com>
 * @since   2018.03.05
 */
namespace Timerlau\AliyunSms;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

class Sms
{
    private $phone                = '';    // 手机号
    private $sign                 = '';    // 签名名称
    private $template             = '';    // 模板编号
    private $assign               = [];    // 模板变量
    private $outId                = null;  // 流水号
    private $bizId                = null;  // 短信发送成功后，返回的发送回执ID，用来查询的
    private $extendCode           = null;  // 上行扩展码
    private $client               = null;  // 阿里云sms的客户端对象
    private $exception            = null;  // 异常对象

    public function __construct($sms_client)
    {
        $this->sign          = config('sms.default_sign_name');
        $this->template      = config('sms.default_template_code');
        $this->client        = $sms_client;
    }

    /**
     * 发送多条短信 （最多同时发送1000条）
     */
    public function multiSend()
    {
        // todo
    }

    /**
     * 获取短信接收情况
     *
     * @param callable 回调函数
     * @param str      接收类型  SmsReport or 上行短信
     * @return boolean/object 有错误，返回object，没错误，返回true
     */
    public function receive(callable $callback, $sms_type = 'SmsReport')
    {
        $msg = new Msg;
        $msg->receiveMsg(

            // 消息类型，SmsReport: 短信状态报告
            $sms_type,

            // 在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName
            config('sms.MNS_QUEUE_NAME'),

            /**
             * 回调
             * @param stdClass $message 消息数据
             * @return bool 返回true，则工具类自动删除已拉取的消息。返回false，消息不删除可以下次获取
             */
            $callback
        );
        return $msg->getException() ? $msg->getException(): True;
    }

    /**
     * 发送单条短信
     *
     * @return boolean
     */
    public function send()
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($this->phone);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($this->sign);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($this->template);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($this->assign, JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        if ($this->outId && !empty($this->outId)) {
            $request->setOutId($this->outId);
        }

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        if ($this->extendCode && !empty($this->extendCode)) {
            $request->setSmsUpExtendCode($this->extendCode);
        }

        try {

            // 发起访问请求
            $response = $this->client->getAcsResponse($request);

            // $response->Code == 'OK' 发送成功 其他失败
            if ($response->Code == 'OK') {
                $this->bizId($response->BizId);
                return True;
            }
            throw new SmsException($response->Message, $response->Code);

        } catch (SmsException $e) {
            $this->exception($e);
            return False;
        }
    }

    /**
     * 发送记录查询
     *
     * @param date 查询日期           格式 Ymd 默认当天
     * @param int  页码               默认 1
     * @param int  显示条数           默认 10
     * @return response [TotalCount=>0, SmsSendDetailDTOs=>[SmsSendDetailDTO]]
     */
    public function history($date = null, $page = 1, $limit = 10)
    {
        $date = $date ? $date: date('Ymd');

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber($this->phone);

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate($date);

        // 必填，分页大小
        $request->setPageSize($limit);

        // 必填，当前页码
        $request->setCurrentPage($page);

        // 可选，设置发送回执ID
        if ($this->bizId && !empty($this->bizId)) {
            $request->setBizId($this->bizId);
        }

        try {

            // 发起访问请求
            $response = $this->client->getAcsResponse($request);

            // $response->Code == 'OK' 发送成功 其他失败
            if ($response->Code == 'OK') return $response;

            throw new SmsException($response->Message, $response->Code);

        } catch (SmsException $e) {
            $this->exception($e);
            return False;
        }
    }

    /**
     * 设置错误对象
     */
    public function exception($exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * 获取错误对象
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * 设置手机号
     * @param str/arr   $phone  手机号   eg: 187xxxx / ['187xxx', '186xxx']
     */
    public function phone($phone)
    {
        if (is_array($phone)) {
            array_map($phone, function($phone){
                $phone = (int)$phone;
            });
            $this->phone = $phone;
        } else {
            $this->phone = (int)$phone;
        }
        return $this;
    }

    /**
     * 设置签名模板
     *
     * @param str   $sign 签名名称 （阿里云后台设置的）
     */
    public function sign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    /**
     * 设置模板号
     *
     * @param str $template 模板CODE （阿里云后台可以查看到）
     */
    public function template($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * 设置模板变量, 具体设置什么变量，需要根据阿里云的后台模板来约定
     *
     * @param arr  $assign eg: ['code'=>'123', 'msg'=>'hey,world']
     */
    public function assign($assign)
    {
        $this->assign = $assign;
        return $this;
    }

    /**
     * 设置流水号
     *
     * @param str   $out_id
     */
    public function outId($out_id)
    {
        $this->outId = $out_id;
        return $this;
    }

    /**
     * 短信发送成功后，返回的发送回执ID，用来查询的发送状态的
     *
     * @param str   $biz_id
     */
    public function bizId($biz_id)
    {
        $this->bizId = $biz_id;
        return $this;
    }

    /**
     * 设置上行短信扩展码
     */
    public function extendCode($extend_code)
    {
        $this->extendCode = $extend_code;
        return $this;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name: False;
    }
}