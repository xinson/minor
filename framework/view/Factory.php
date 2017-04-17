<?php
namespace App\Framework\View;

use App\Framework\View\Engines\EngineInterface;
use App\Framework\View\ViewFinderInterface;
use App\Framework\View\View;


class Factory
{

    protected $engine;

    protected $finder;

    protected $shared = [];

    protected $aliases = [];

    protected $names;

    protected $sections;

    protected $sectionsStack = [];

    protected $renderCount = 0;

    public function __construct(EngineInterface $engine, ViewFinderInterface $finder)
    {
        $this->engine = $engine;
        $this->finder = $finder;

        $this->share('__env', $this);
    }

    public function file($path, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $data);
        $view = new View($this, $this->engine, $path. $path, $data);
        return $view;
    }

    public function make($view, $data = [], $mergeData)
    {
        if(isset($this->aliases[$view])){
            $view = $this->aliases[$view];
        }

        $view = $this->normalizeName($view);

        $path = $this->finder->find($view);

        $data = array_merge($data,$mergeData);

        $view = new View($this, $this->engine, $view, $path, $data);

        return $view;
    }

    public function normalizeName($name)
    {
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if(strpos($name,$delimiter)===false){
            return str_replace('/', '.', $name);
        }

        list($namespace,$name) = explode($delimiter,$name);

        return $namespace.$delimiter.str_replace('/','.',$name);
    }


    public function share($key, $value = 'null')
    {
        if(! is_array($key) && !empty($this->shared[$key])){
            return $this->shared[$key] = $value;
        }else {
            foreach ($key as $innerKey => $innerValue) {
                return $this->share($innerKey, $innerValue);
            }
        }
    }

    public function incrementRender(){
        $this->renderCount++;
    }

    public function decrementRender()
    {
        $this->renderCount--;
    }

    public function denoeRender()
    {
        $this->renderCount = 0;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function hasSections($name)
    {
        return array_key_exists($name,$this->sections);
    }

    public function appendSections()
    {
        $last = array_pop($this->sectionsStack);

        if(isset($this->sections[$last])){
            $this->sections[$last] .= ob_get_clean();
        }else{
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    public function stopSections($overwrite = false)
    {
        $last = array_pop($this->sectionsStack);
        if($overwrite){
            $this->sections[$last] = ob_get_clean();
        }else{
            $this->extendSection($last, ob_get_clean());
        }
        return $this;
    }

    protected function extendSection($section, $content)
    {
        if(isset($this->sections[$section])){
            $content = str_replace('@parent',$content,$this->sections[$section]);
        }

        $this->sections[$section] = $content;
    }


}
