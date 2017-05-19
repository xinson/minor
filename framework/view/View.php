<?php

namespace App\Framework\View;

use ArrayAccess;
use BadMethodCallException;
use App\Framework\View\Engines\EngineInterface;

class View implements ArrayAccess
{

    protected $factory;

    protected $engine;

    protected $view;

    protected $data;

    protected $path;

    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = [])
    {
        $this->view = $view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = (array) $data;
    }

    public function render(callable $callback = null)
    {
        $contents = $this->renderContents();

        $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;

        $this->factory->flushSectionsIfDoneRendering();

        return !is_null($response) ? $response : $contents;
    }

    protected function renderContents()
    {
        $this->factory->incrementRender();

        $contents = $this->getContents();

        $this->factory->decrementRender();

        return $contents;
    }

    public function renderSections()
    {
        return $this->render(function () {
            return $this->factory->getSections();
        });
    }

    protected function getContents()
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    protected function gatherData()
    {
        $data = array_merge($this->factory->getShared(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function nest($key, $view, array $data = [])
    {
        return $this->with($key, $this->factory->make($view, $data));
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function name()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->view;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    public function &__get($key)
    {
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function __call($method, $parameters)
    {
        if (strpos($method, 'with') === 0) {
            $value = substr($method, 4);

            if (!ctype_lower($value)) {
                $value = preg_replace('/\s+/', '', $value);
                $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.'_', $value));
            }

            return $this->with($value, $parameters[0]);
        }

        throw new BadMethodCallException("Method [$method] does not exist on view.");
    }

    public function __toString()
    {
        return $this->render();
    }
}
