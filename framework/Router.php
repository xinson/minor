<?php

class Router
{

    // 请求方法
    public $method = array();
    // 路由
    public $routers = array();
    // 正则匹配规则
    public $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );

    // 错误
    public $error;

    public function get($uri,$controller)
    {
        $uri =
    }

    public function post()
    {

    }

    public function put()
    {

    }

    public function delete()
    {

    }

    public function dicpatch()
    {

    }

}