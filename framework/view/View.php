<?php
namespace App\Framework\View;

use App\Framework\View\Engines\EngineInterface;

class View implements \ArrayAccess{

    protected $factory;

    protected $engine;

    protected $view;

    protected $path;

    protected $data;

    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = [])
    {
        $this->engine = $engine;
        $this->view = $view;
        $this->path = $path;
        $this->factory = $factory;
        $this->data = (array)$data;

    }

    public function render(callable $callback = null)
    {
        $contents = $this->renderContents();
    }

    public function renderContents()
    {
        $this->factory->incrementRender();

        $contents = $this->getContents();

        $this->factory->decrementRender();

        return $contents;

    }

    public function getContents()
    {
        return $this->engine->get($this->path, $this->getContents());
    }

    public function whth($key, $value)
    {
        if(is_array($key)){
            $this->data = array($this->data, $key);
        } else{
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function nest($key, $view, array $data = array())
    {
        return $this->whth($key, $this->factory->make($view, $data));
    }

    protected function gatherData()
    {
        $data = array_merge($this->factory->getShared(), $this->data);

        foreach($data as $key => $value)
        {
            if($value instanceof Renderable){
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    public function renderSections()
    {
        return $this->render(function(){
            return $this->factory->getSections();
        });
    }


    public function offsetExists($offset){

    }

    public function offsetGet($offset){

    }

    public function offsetSet($offset, $value){

    }

    public function offsetUnset($offset){

    }

}