<?php
namespace App\Framework\Logger\Handler;

use App\Framework\Logger\Formatter\LineFormatter;

abstract class AbstractHandler
{
    protected $level;

    protected $formatter;

    protected $processors = array();


    public function __construct($level = \App\Framework\Logger::DEBUG)
    {
        $this->level = $level;
    }

    /**
     * 判断是否当前的handle
     * @param array $record
     * @return int
     */
    public function isHandling(array $record)
    {
        return $record['level'] = $this->level;
    }

    /**
     * 操作
     * @param array $record
     * @return bool
     */
    public function handle(array $record)
    {
        if(!$this->isHandling($record)){
            return false;
        }
        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

    }


    /**
     * Process 处理
     * @param $record
     * @return mixed
     */
    public function processRecord($record)
    {
        if($this->processors){
            if(is_array($record)){
                foreach ($record as $process){
                    $record = call_user_func($process,  $record);
                }
            }
        }
        return $record;
    }

    /**
     * 添加处理
     * @param $callback
     * @return $this
     */
    public function pushProcess($callback)
    {
        if(!is_callable($callback)){
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method ),'.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * 删除处理
     * @return mixed
     */
    public function popProcessor()
    {
        if(!$this->processors){
            throw new \LogicException('You tried to pop from an empty processor stack');
        }

        return array_shift($this->processors);
    }

    abstract public function write(array $record);

    /**
     * 设置格式
     * @param $formatter
     * @return $this
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * 获取格式
     * @return LineFormatter
     */
    public function getFormatter()
    {
        if(!$this->formatter){
            $this->formatter = new LineFormatter();
        }
        return $this->formatter;
    }

    public function close(){}

    public function __destruct()
    {
        try{
            $this->close();
        }catch (\Exception $exception){

        }
    }

}