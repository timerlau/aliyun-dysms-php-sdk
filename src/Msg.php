<?php
/**
 * 封装 阿里云的短信消息
 *
 * @author  songmw<imphp@qq.com>
 * @since   2018.03.07
 */
namespace Timerlau\AliyunSms;

use Aliyun\Msg\TokenGetterForAlicom;

use Aliyun\Core\Config;
use AliyunMNS\Exception\MnsException;

// 加载区域结点配置
Config::load();

class Msg
{
    private $exception = null;  // 异常

    /**
     * @var TokenGetterForAlicom
     */
    static $tokenGetter = null;

    public static function getTokenGetter() {

        $accountId = "1943695596114318"; // 此处不需要替换修改!

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)

        $accessKeyId = config('sms.ACCESS_KEY_ID'); // AccessKeyId

        $accessKeySecret = config('sms.ACCESS_KEY_SECRET'); // AccessKeySecret

        if(static::$tokenGetter == null) {
            static::$tokenGetter = new TokenGetterForAlicom(
                $accountId,
                $accessKeyId,
                $accessKeySecret);
        }
        return static::$tokenGetter;
    }

    /**
     * 获取消息
     *
     * @param string $messageType 消息类型
     * @param string $queueName 在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName<br/>(e.g. Alicom-Queue-xxxxxx-xxxxxReport)
     * @param callable $callback <p>
     * 回调仅接受一个消息参数;
     * <br/>回调返回true，则工具类自动删除已拉取的消息;
     * <br/>回调返回false,消息不删除可以下次获取.
     * <br/>(e.g. function ($message) { return true; }
     * </p>
     */
    public function receiveMsg($messageType, $queueName, callable $callback)
    {
        $i = 0;
        // 取回执消息失败3次则停止循环拉取
        while ( $i < 3)
        {
            try
            {
                // 取临时token
                $tokenForAlicom = static::getTokenGetter()->getTokenByMessageType($messageType, $queueName);

                // 使用MNSClient得到Queue
                $queue = $tokenForAlicom->getClient()->getQueueRef($queueName);

                // 接收消息，并根据实际情况设置超时时间
                $res = $queue->receiveMessage(2);

                // 计算消息体的摘要用作校验
                $bodyMD5 = strtoupper(md5(base64_encode($res->getMessageBody())));

                // 比对摘要，防止消息被截断或发生错误
                if ($bodyMD5 == $res->getMessageBodyMD5())
                {
                    // 执行回调
                    if(call_user_func($callback, json_decode($res->getMessageBody())))
                    {
                        // 当回调返回真值时，删除已接收的信息
                        $receiptHandle = $res->getReceiptHandle();
                        $queue->deleteMessage($receiptHandle);

                        // 获取成功，则清掉之前的异常数据
                        unset($this->exception);
                    }
                }

                return; // 整个取回执消息流程完成后退出
            }
            catch (MnsException $e)
            {
                $i++;
                $this->exception = $e;
            }
        }
    }
    
    public function getException()
    {
        return isset($this->exception) ? $this->exception: False;
    }
}