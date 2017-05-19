<?php
namespace App\Framework\View\Compiler;

abstract class Compiler
{
    protected $cachePath;

    public function __construct($cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.md5($path);
    }

    public function isExpired($path)
    {
        $compiler = $this->getCompiledPath($path);

        if(empty($this->cachePath) || !$this->isFileExpired($compiler)){
            return true;
        }

        $lastModified = $this->lastModified($path);

        return $lastModified > $this->lastModified($compiler);
    }

    abstract public function compiler();

    /**
     * 判断文件是否存在
     * @param $name
     * @return bool
     */
    public function isFileExpired($name)
    {
        return file_exists($name);
    }

    /**
     * 获取文件内容
     * @param $path
     * @return string
     * @throws \Exception
     */
    public function getFile($path)
    {
        if(is_file($path)){
            return file_get_contents($path);
        }

        throw new \Exception('File dose no exist at path {'.$path.'}');
    }

    /**
     * 保存文件内容
     * @param $path
     * @param $contents
     * @param bool $lock
     * @return int
     */
    public function setFile($path, $contents, $lock = true)
    {
        return file_put_contents($path. $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * 获取文件最后修改时间
     * @param $path
     * @return int
     */
    public function lastModified($path)
    {
        return filectime($path);
    }


}