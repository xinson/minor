<?php
namespace App\Controllers;

use App\Framework\Application;
use App\Framework\Config;
use App\Models\News;
use App\Framework\Db\MysqlPdo;


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

        $new = new News();
        $new->findAndWhere("id = :id and ",array('id'=>1));
        print_r($new->getData());exit();


        $view= Application::getShare('view');
        // 渲染视图并输出
        echo $view->make('index', ['a' => 1, 'b' => 2])->render();

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