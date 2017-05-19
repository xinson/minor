<?php
namespace App\Framework\View\Engines;

use ErrorException;

class CompilerEngine extends PhpEngine
{

    protected $compiler;

    protected $lastCompiled = [];

    public function __construct($compiler)
    {
        $this->compiler = $compiler;
    }

    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }

        $compiled = $this->compiler->getCompiledPath($path);
        $results = $this->evaluatePath($compiled, $data);

        array_pop($this->lastCompiled);

        return $results;
    }

    protected function handleViewException($e, $obLevel)
    {
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        parent::handleViewException($e, $obLevel);
    }

    protected function getMessage($e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastCompiled)).')';
    }

    public function getCompiler()
    {
        return $this->compiler;
    }
}
