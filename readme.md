# minor轻量框架

minor轻量框架使用composer作自动加载，加swoole来实现php的多线程。
   
## 需要的扩展如下:
   
    mbstring
    mcrypt
    openssl
    PDO
    PDO/Mysql
    
## 路由设置（文件 app\common\router.php）

    Router::get('/', 'App\Controllers\demo@index');
    Router::get('/test.html', 'App\Controllers\demo@test');
    Router::get('/view/(:num)', 'App\Controllers\demo@view');
    Router::get('/(:any)', function($slug) {
    	echo 'The slug is: ' . $slug;
    });
    Router::post('/login.html', 'App\Controllers\demo@login');
    
get/post为method请求的方式，也支持put和delete。第一个参数为请求的url,第二个参数为请求的类和方法@后面为方法。也可以在第二个参数写方法。

## 日志 （确保storage/*目录权限为777）

    $log = Application::getShare('logger');
    //写入log  storage/log/minor.log
    $log->warning('Foo');

在控制器调用日志操作如上，日志类型有    
    
    DEBUG：详细的debug信息
    INFO：感兴趣的事件。像用户登录，SQL日志
    NOTICE：正常但有重大意义的事件。
    WARNING：发生异常，使用了已经过时的API。
    ERROR：运行时发生了错误，错误需要记录下来并监视，但错误不需要立即处理。
    CRITICAL：关键错误，像应用中的组件不可用。
    ALETR：需要立即采取措施的错误，像整个网站挂掉了，数据库不可用。这个时候触发器会通过SMS通知你，

   
## 视图设置

在控制器使用
    
      view= Application::getShare('view');
      // 渲染视图并输出
      echo $view->make('index', ['a' => 1, 'b' => 2])->render();


## 模型
    
