<?php

/** 项目根目录 */
define('PATH',dirname(__DIR__));

// Autoload 自动载入 加载composer
require '../vendor/autoload.php';

// 加载函数库
require '../app/common/function.php';

use \NoahBuscher\Macaw\Macaw;

Macaw::get('/', 'Controllers\demo@index');

Macaw::dispatch();