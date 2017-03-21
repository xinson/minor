<?php
namespace App\Framework\Logger\Handler;

class StreamHandle extends AbstractHandler
{
    protected $stream;
    protected $url;
    protected $useLock;
    protected $dirCreated;
    protected $errorMessage;

    public function __construct($stream, $useLock = false, $level = \App\Framework\Logger::DEBUG)
    {
        parent::__construct($level);
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a string');
        }
        $this->useLock = $useLock;
    }

    public function write(array $record)
    {
        if (!is_resource($this->stream)) {
            if (empty($this->url)) {
                throw new \LogicException('Missing stream url, the stream can not be opened');
            }
            $this->createDir();
            $this->errorMessage = '';
            set_error_handler(array($this, 'customErrorHandler'));
            $this->stream = fopen($this->url, 'a');
            if (!is_resource($this->stream)) {
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" can not opened:' . $this->errorMessage, $this->url));
            }
            restore_error_handler();
        }
        if ($this->useLock) {
            flock($this->stream, LOCK_EX);
        }

        fwrite($this->stream, (string)$record['formatted']);

        if ($this->useLock) {
            flock($this->stream, LOCK_UN);
        }
    }

    private function getDirFromStream($stream)
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' == substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }


        return null;
    }

    protected function createDir()
    {
        if ($this->dirCreated) {
            return;
        }

        $dir = $this->getDirFromStream($this->url);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = '';
            set_error_handler(array($this, 'customErrorHandler'));
            $rs = mkdir($dir, 0777, true);
            if (false === $rs) {
                throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable', $dir));
            }
            restore_error_handler();
        }
        $this->dirCreated = true;
    }

    private function customErrorHandler($code, $msg)
    {
        $this->errorMessage = $msg;
    }

}