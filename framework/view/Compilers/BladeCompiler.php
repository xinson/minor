<?php
namespace App\Framework\View\Compiler;

class BladeCompiler extends Compiler
{
    protected $cachePath;

    protected $path;

    protected $forelseCounter = 0;

    protected $footer = [];

    protected $compilers = [
        'Statements',
        'Comments',
        'Echos',
    ];

    protected $rawTags = ['{!!','!!}'];

    protected $contentTags = ['{{','}}'];

    protected $escapedTags = ['{{{','}}}'];

    protected $customDirectives = [];

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath(){
        return $this->path;
    }

    public function compiler($path = null)
    {
        if($path){
            $this->setPath($path);
        }

        $contents = $this->compilerString($this->getFile($this->getPath()));

        if(!is_null($this->cachePath)){
            $this->setFile($this->getCompiledPath($path),$contents);
        }

    }

    public function compilerString($value)
    {
        $result = '';
        $this->footer = [];

        foreach (token_get_all($value) as $tokon){
            $result .= is_array($tokon)?$this->parseTokon($tokon):$tokon;
        }

        if(count($this->footer) > 0){
            $result = ltrim($result, PHP_EOL).PHP_EOL.implode(PHP_EOL,array_reverse($this->footer));
        }

        return $result;
    }

    public function parseTokon($token)
    {
        list($id, $content) = $token;

        if($id == T_INLINE_HTML){
            foreach ($this->compilers as $type){
                $content = $this->{"compile{$type}"}($content);
            }
        }
        return $content;
    }


    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/',$this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern,'<?php /*$1*/ ?>',$value);
    }

    protected function compileEchos($value)
    {
        foreach($this->getEchoMethods() as $method => $lenght)
        {
            $value = $this->$method($value);
        }

        return $value;
    }

    protected function getEchoMethods()
    {
        $methods = [
            'compileRawEchos' => strlen(stripcslashes($this->rawTags[0])),
            'compileEscapedEchos' => strlen(stripcslashes($this->escapedTags[0])),
            'compileRegularEchos' => strlen(stripcslashes($this->contentTags[0])),
        ];

        uksort($methods, function ($method1, $method2) use ($methods) {
            if($methods[$method1] > $methods[$method2]){
                return -1;
            }

            if($methods[$method1] < $methods[$method2]){
                return 1;
            }

            if ($method1 === 'compileRawEchos') {
                return -1;
            }

            if ($method2 === 'compileRawEchos') {
                return 1;
            }

            if ($method1 === 'compileEscapedEchos') {
                return -1;
            }

            if ($method2 === 'compileEscapedEchos') {
                return 1;
            }
        });

        return $methods;
    }

    protected function compileStatements($value)
    {
        $callback = function ($match){
            $expression = isset($match[3])?$match[3]:$match[3];

            if(strpos($match[1],'@') !== false){
                $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
            } elseif (isset($this->customDirectives[$match[1]])) {
                $match[0] = call_user_func($this->customDirectives[$match[1]], $expression);
            } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
                $match[0] = $this->$method($expression);
            }
            return isset($match[3]) ? $match[0] : $match[0].$match[2];
        };

        return preg_replace_callback('/\B@(@?\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);

    }

    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$this->compileEchoDefaults($matches[2]).'; ?>'.$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            $wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));

            return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$wrapped.'; ?>'.$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }


    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1] ? $matches[0] : '<?php echo e('.$this->compileEchoDefaults($matches[2]).'); ?>'.$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }


    public function compileEchoDefaults($value)
    {
        return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
    }

    protected function compileEach($expression)
    {
        return "<?php echo \$__env->renderEach{$expression}; ?>";
    }

    protected function compileYield($expression)
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    protected function compileShow($expression)
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    protected function compileSection($expression)
    {
        return "<?php \$__env->startSection{$expression}; ?>";
    }

    protected function compileAppend($expression)
    {
        return '<?php $__env->appendSection(); ?>';
    }

    protected function compileEndsection($expression)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    protected function compileStop($expression)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    protected function compileOverwrite($expression)
    {
        return '<?php $__env->stopSection(true); ?>';
    }

    protected function compileUnless($expression)
    {
        return "<?php if ( ! $expression): ?>";
    }

    protected function compileEndunless($expression)
    {
        return '<?php endif; ?>';
    }


    protected function compileElse($expression)
    {
        return '<?php else: ?>';
    }

    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    protected function compileForeach($expression)
    {
        preg_match('/\( *(.*) +as *([^\)]*)/i', $expression, $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();';

        return "<?php {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} ?>";
    }

    protected function compileBreak($expression)
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }


    protected function compileContinue($expression)
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    protected function compileForelse($expression)
    {
        $empty = '$__empty_'.++$this->forelseCounter;

        preg_match('/\( *(.*) +as *([^\)]*)/', $expression, $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getFirstLoop();';

        return "<?php {$empty} = true; {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} {$empty} = false; ?>";
    }

    protected function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    protected function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }


    protected function compileEmpty($expression)
    {
        $empty = '$__empty_'.$this->forelseCounter--;

        return "<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getFirstLoop(); if ({$empty}): ?>";
    }

    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    protected function compileEndwhile($expression)
    {
        return '<?php endwhile; ?>';
    }

    protected function compileEndfor($expression)
    {
        return '<?php endfor; ?>';
    }

    protected function compileEndforeach($expression)
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getFirstLoop(); ?>';
    }

    protected function compileEndif($expression)
    {
        return '<?php endif; ?>';
    }

    protected function compileEndforelse($expression)
    {
        return '<?php endif; ?>';
    }

    protected function compileExtends($expression)
    {
        if (strpos($expression, '(') === 0) {
            $expression = substr($expression, 1, -1);
        }

        $data = "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";

        $this->footer[] = $data;

        return '';
    }

    protected function compileInclude($expression)
    {
        if (strpos($expression, '(') === 0) {
            $expression = substr($expression, 1, -1);
        }

        return "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
    }

    protected function compileStack($expression)
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    protected function compilePush($expression)
    {
        return "<?php \$__env->startSection{$expression}; ?>";
    }

    protected function compileEndpush($expression)
    {
        return '<?php $__env->appendSection(); ?>';
    }


    public function directive($name, callable $handler)
    {
        $this->customDirectives[$name] = $handler;
    }

    public function getCustomDirectives()
    {
        return $this->customDirectives;
    }

    public function getRawTags()
    {
        return $this->rawTags;
    }

}