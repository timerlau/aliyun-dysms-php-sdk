<?php
/**
 * 捕获 send 错误信息
 *
 * @author  songmw<imphp@qq.com>
 * @since   2018.03.05
 */
namespace Timerlau\AliyunSms;

class SmsException extends \Exception
{
    private $errorMessage;
    private $errorCode;

    public function  __construct($errorMessage, $errorCode)
    {
        parent::__construct($errorMessage);
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}