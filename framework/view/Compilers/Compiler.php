<?php

namespace App\Framework\View\Compilers;

use App\Framework\View\Filesystem;

abstract class Compiler
{

    protected $files;

    protected $cachePath;

    public function __construct($cachePath)
    {
        $this->files = new Filesystem;
        $this->cachePath = $cachePath;
    }

    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.md5($path);
    }

    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        if (!$this->cachePath || !$this->files->exists($compiled)) {
            return true;
        }

        $lastModified = $this->files->lastModified($path);

        return $lastModified >= $this->files->lastModified($compiled);
    }
}
