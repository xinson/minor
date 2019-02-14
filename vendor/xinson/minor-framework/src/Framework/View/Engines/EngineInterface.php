<?php

namespace Minor\Framework\View\Engines;

interface EngineInterface
{
    public function get($path, array $data = []);
}
