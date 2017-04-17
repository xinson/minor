<?php
namespace App\Framework\View\Compiler;

class BladeCompiler extends Compiler
{
    public $file;

    public $cachePath;



    public function isExpired()
    {

    }

    public function compiler($path = null)
    {
        if($path){
            $this->setPath($path);
        }

        $contents = $this->compilerString($this->file->get($this->getPath()));

        if(!is_null($this->cachePath)){
            $this->file->put($this->getCompiledPath($path),$contents);
        }

    }


}