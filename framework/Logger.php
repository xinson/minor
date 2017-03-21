<?php

namespace App\Framework;

use App\Framework\Logger\Contracts\LoggerInterface;

class Logger implements LoggerInterface
{

    const DEBUG = 100;

    const INFO = 200;

    const NOTICE = 250;

    const WARNING = 300;

    const ERROR = 400;

    const CRITICAL = 500;

    const ALERT = 550;

    const EMERGENCY = 600;

    protected static $levels = array(
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );

    protected $channel;

    protected $handlers = array();

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    /**
     * 加入元素$handlers
     * @param $handler
     * @return $this
     */
    public function pushHandler($handler)
    {
        array_unshift($this->handlers, $handler);

        return $this;
    }

    /**
     * 删除$handlers第一个元素
     * @return mixed
     */
    public function popHandler()
    {
        if (empty($this->handlers)) {
            throw new \LogicException('You tried to pop from an empty handler stack');
        }
        return array_shift($this->handlers);
    }

    /**
     * 设置$handlers
     * @param $handlers
     * @return $this
     */
    public function setHandlers($handlers)
    {
        $this->handlers = array();
        if (is_array($handlers)) {
            foreach (array_reverse($handlers) as $d => $v) {
                $this->pushHandler($v);
            }
        }
        return $this;
    }

    /**
     * 获取$handlers
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    public function addRecord($level, $message, array $context = array())
    {
        if(empty($this->handlers)){

        }
    }


    public function emergency($message, array $context = array())
    {

    }

    public function alert($message, array $context = array())
    {

    }

    public function critical($message, array $context = array())
    {

    }

    public function error($message, array $context = array())
    {

    }

    public function warning($message, array $context = array())
    {

    }

    public function notice($message, array $context = array())
    {

    }

    public function info($message, array $context = array())
    {

    }

    public function debug($message, array $context = array())
    {

    }

    public function write($level, $message, array $context = array())
    {

    }
}