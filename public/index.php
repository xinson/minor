<?php

// 项目根目录
define('PATH',dirname(__DIR__));

// Autoload 自动载入 加载composer
require '../vendor/autoload.php';

// 加载函数库
require '../app/common/function.php';

$app = new \Minor\Framework\Application();

// 加载路由
require '../app/common/router.php';

try {
    Minor\Framework\Router::dicpatch();
}catch (\Exception $e){
	echo $e->getMessage();exit();
}