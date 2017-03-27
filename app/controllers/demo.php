<?php
namespace App\Controllers;

use App\Framework\Application;
use App\Framework\Config;
use App\Framework\Model;


class demo
{
    public function index()
    {
        /** @var Config $config */
        //$config = Application::getShare('config');
        //$data = $config->get('config');
        //$demo = new Model();

        /*
        $log = Application::getShare('logger');
        $log->warning('Foo');
        $log->error('Bar');
        */

        $path = [PATH.'/app/templates'];         // 视图文件目录，这是数组，可以有多个目录
        $cachePath = PATH.'/storage/framework/cache';     // 编译文件缓存目录

        $compiler = new \Xiaoler\Blade\Compilers\BladeCompiler($cachePath);

        // 如过有需要，你可以添加自定义关键字
        $compiler->directive('datetime', function($timestamp) {
            return preg_replace('/(\(\d+\))/', '<?php echo date("Y-m-d H:i:s", $1); ?>', $timestamp);
        });

        $engine = new \Xiaoler\Blade\Engines\CompilerEngine($compiler);
        $finder = new \Xiaoler\Blade\FileViewFinder($path);

        // 如果需要添加自定义的文件扩展，使用以下方法
        $finder->addExtension('tpl');

        // 实例化 Factory
        $factory = new \Xiaoler\Blade\Factory($engine, $finder);

        // 渲染视图并输出
        echo $factory->make('index', ['a' => 1, 'b' => 2])->render();


    }

    public function test()
    {
        echo 'test';
    }

    public function view($id)
    {
        echo $id;
    }

    public function login()
    {

    }

}