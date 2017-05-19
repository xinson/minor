<?php

namespace App\Framework\View\Compilers;

interface CompilerInterface
{

    public function getCompiledPath($path);

    public function isExpired($path);

    public function compile($path);
}
