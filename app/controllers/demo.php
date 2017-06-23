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
        //$new->findAndWhere('name = :name', array('name' => 'name4'));
        $new->find(1);
        //$new->name = 'test11';
        print_r($new->getData());
        exit();


        //$new->findAndWhere(array('id' => 1, 'name' => ':name'),array('name'=>'test'));
        //print_r($new->getData());exit();
        /*
        $list = $new->getList();
        echo '<pre>';
        print_r($list);exit();
        */

        $view= Application::getShare('view');
        // 渲染视图并输出
        echo $view->make('index', ['list' => array()])->render();

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