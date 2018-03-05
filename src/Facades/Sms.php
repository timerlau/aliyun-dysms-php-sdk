<?php

namespace Timerlau\AliyunSms\Facades;
use Illuminate\Support\Facades\Facade;

class Sms extends Facade {
    
    protected static function getFacadeAccessor()
    { 
        return 'sms'; 
    }
}