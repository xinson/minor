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
        $view= Application::getShare('view');
        // 渲染视图并输出
        echo $view->make('index', ['content' => '111'])->render();
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