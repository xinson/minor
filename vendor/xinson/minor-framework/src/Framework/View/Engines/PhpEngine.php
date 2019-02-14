<?php

namespace Minor\Framework\View\Engines;

class PhpEngine implements EngineInterface
{
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    protected function evaluatePath($__path, $__data)
    {
        $obLevel = ob_get_level();

        ob_start();

        extract($__data);

        try {
            include $__path;
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    protected function handleViewException($e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}
