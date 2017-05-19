<?php

namespace App\Framework\View;

use InvalidArgumentException;
use App\Framework\View\ViewFinderInterface;
use App\Framework\View\Engines\EngineInterface;

class Factory
{

    protected $engine;

    protected $finder;

    protected $shared = [];

    protected $aliases = [];

    protected $names = [];

    protected $sections = [];

    protected $sectionStack = [];

    protected $loopsStack = [];

    protected $pushes = [];

    protected $pushStack = [];

    protected $renderCount = 0;

    public function __construct(EngineInterface $engine, ViewFinderInterface $finder)
    {
        $this->finder = $finder;
        $this->engine = $engine;

        $this->share('__env', $this);
    }

    public function file($path, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $data);

        $view = new View($this, $this->engine, $path, $path, $data);

        return $view;
    }

    public function make($view, $data = [], $mergeData = [])
    {
        if (isset($this->aliases[$view])) {
            $view = $this->aliases[$view];
        }

        $view = $this->normalizeName($view);

        $path = $this->finder->find($view);

        $data = array_merge($mergeData, $data);

        $view = new View($this, $this->engine, $view, $path, $data);

        return $view;
    }

    protected function normalizeName($name)
    {
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        list($namespace, $name) = explode($delimiter, $name);

        return $namespace.$delimiter.str_replace('/', '.', $name);
    }

    public function of($view, $data = [])
    {
        return $this->make($this->names[$view], $data);
    }

    public function name($view, $name)
    {
        $this->names[$name] = $view;
    }

    public function alias($view, $alias)
    {
        $this->aliases[$alias] = $view;
    }

    public function exists($view)
    {
        try {
            $this->finder->find($view);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    public function renderEach($view, $data, $iterator, $empty = 'raw|')
    {
        $result = '';
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];

                $result .= $this->make($view, $data)->render();
            }
        }
        else {
            if (strpos($empty, 'raw|') === 0) {
                $result = substr($empty, 4);
            } else {
                $result = $this->make($empty)->render();
            }
        }

        return $result;
    }

    public function share($key, $value = null)
    {
        if (!is_array($key)) {
            return $this->shared[$key] = $value;
        }

        foreach ($key as $innerKey => $innerValue) {
            $this->share($innerKey, $innerValue);
        }
    }

    public function startSection($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content);
        }
    }

    public function inject($section, $content)
    {
        return $this->startSection($section, $content);
    }

    public function yieldSection()
    {
        return $this->yieldContent($this->stopSection());
    }

    public function stopSection($overwrite = false)
    {
        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    public function appendSection()
    {
        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    protected function extendSection($section, $content)
    {
        if (isset($this->sections[$section])) {
            $content = str_replace('@parent', $content, $this->sections[$section]);
        }

        $this->sections[$section] = $content;
    }

    public function yieldContent($section, $default = '')
    {
        $sectionContent = $default;

        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }

        $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

        return str_replace(
            '--parent--holder--', '@parent', str_replace('@parent', '', $sectionContent)
        );
    }

    public function startPush($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPush($section, $content);
        }
    }

    public function stopPush()
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->pushStack);

        $this->extendPush($last, ob_get_clean());

        return $last;
    }

    protected function extendPush($section, $content)
    {
        if (! isset($this->pushes[$section])) {
            $this->pushes[$section] = [];
        }
        if (! isset($this->pushes[$section][$this->renderCount])) {
            $this->pushes[$section][$this->renderCount] = $content;
        } else {
            $this->pushes[$section][$this->renderCount] .= $content;
        }
    }

    public function yieldPushContent($section, $default = '')
    {
        if (! isset($this->pushes[$section])) {
            return $default;
        }

        return implode(array_reverse($this->pushes[$section]));
    }

    public function flushSections()
    {
        $this->sections = [];

        $this->sectionStack = [];
    }

    public function flushSectionsIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->flushSections();
        }
    }

    public function incrementRender()
    {
        $this->renderCount++;
    }

    public function decrementRender()
    {
        $this->renderCount--;
    }

    public function doneRendering()
    {
        return $this->renderCount == 0;
    }

    public function addLoop($data)
    {
        $length = is_array($data) || $data instanceof Countable ? count($data) : null;

        $parent = end($this->loopsStack);

        $this->loopsStack[] = [
            'iteration' => 0,
            'index' => 0,
            'remaining' => isset($length) ? $length : null,
            'count' => $length,
            'first' => true,
            'last' => isset($length) ? $length == 1 : null,
            'depth' => count($this->loopsStack) + 1,
            'parent' => $parent ? (object) $parent : null,
        ];
    }

    public function incrementLoopIndices()
    {
        $loop = &$this->loopsStack[count($this->loopsStack) - 1];

        $loop['iteration']++;
        $loop['index'] = $loop['iteration'] - 1;

        $loop['first'] = $loop['iteration'] == 1;

        if (isset($loop['count'])) {
            $loop['remaining']--;

            $loop['last'] = $loop['iteration'] == $loop['count'];
        }
    }

    public function popLoop()
    {
        array_pop($this->loopsStack);
    }

    public function getFirstLoop()
    {
        return ($last = end($this->loopsStack)) ? (object) $last : null;
    }

    public function getLoopStack()
    {
        return $this->loopsStack;
    }

    public function addLocation($location)
    {
        $this->finder->addLocation($location);
    }

    public function addNamespace($namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);
    }

    public function prependNamespace($namespace, $hints)
    {
        $this->finder->prependNamespace($namespace, $hints);
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function setFinder(ViewFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    public function shared($key, $default = null)
    {
        return array_key_exists($key, $this->shared) ? $this->shared[$key] : $default;
    }

    public function getShared()
    {
        return $this->shared;
    }

    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function getNames()
    {
        return $this->names;
    }
}
