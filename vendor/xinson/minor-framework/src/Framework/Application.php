<?php
namespace Minor\Framework;

use Minor\Framework\Container;
use Minor\Framework\Logger\Logger;
use Minor\Framework\Logger\Handler\StreamHandler;
use Minor\Framework\View\Compilers\BladeCompiler;
use Minor\Framework\View\Engines\CompilerEngine;
use Minor\Framework\View\FileViewFinder;
use Minor\Framework\View\Factory;

class Application
{
    public static $share;

    public function __construct()
    {
        // 创建一个IOC容器
        $container = new Container;

        $configs = glob(PATH . "/app/config/*.php");
        $configArray = array();
        if (!empty($configs)) {
            foreach ($configs as $d => $v) {
                if (is_file($v)) {
                    $filename = basename($v, '.php');
                    $configArray[$filename] = include $v;
                }
            }
        }
        //注入配置
        //以匿名函数 传入
        /*$container->bind('config', function () use ($configArray){
            return new Config($configArray);
        });*/
        //以类 传入
        $configObj = new Config($configArray);
        $container->bind('config', $configObj);
        $config = $container->make('config', array('config'));
        $this->setShare('config', $config);


        //日志操作类
        $log = new Logger('local');
        $log->pushHandler(new StreamHandler(PATH . $configArray['config']['log']['log_path'], Logger::WARNING));
        $container->bind('logger', $log);
        $logger = $container->make('logger', array('logger'));
        $this->setShare('logger', $logger);


        $view_config = !empty($configArray['config']['view'])?$configArray['config']['view']:'';
        if(!empty($view_config['templates_path'])) {
            $path = [PATH . $view_config['templates_path']];         // 视图文件目录，这是数组，可以有多个目录
        }
        if(!empty($view_config['cache_path'])){
            $cachePath = PATH.$view_config['cache_path'];     // 编译文件缓存目录
        }
        $compiler = new BladeCompiler($cachePath);
        $engine = new CompilerEngine($compiler);
        $finder = new FileViewFinder($path);
        // 如果需要添加自定义的文件扩展，使用以下方法
        $finder->addExtension('tpl');
        // 实例化 Factory
        $factory = new Factory($engine, $finder);
        //绑定到容器
        $container->bind('view', $factory);
        $view= $container->make('view', array('view'));
        $this->setShare('view', $view);
    }

    /**
     * @param $objectname
     * @return bool
     */
    public static function getShare($objectname)
    {
        if (isset(self::$share[$objectname])) {
            return self::$share[$objectname];
        }
        return false;
    }

    /**
     * @param $objectname
     * @param $class
     */
    public function setShare($objectname, $class)
    {
        self::$share[$objectname] = $class;
    }
}