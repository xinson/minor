<?php
namespace App\Framework;

class Router
{
	// 控制器
	public static $controller = array();
	// 路由
	public static $routes = array();
	// 请求方式
	public static $methods = array();
	// 正则匹配规则
	public static $patterns = array(
		':any' => '[^/]+',
		':num' => '[0-9]+',
		':all' => '.*'
	);

	/**
	 * 设置路由
	 * @param $name
	 * @param $arguments
	 */
	public static function __callStatic($name, $arguments)
	{
		array_push(self::$controller, $arguments[1]);
		array_push(self::$methods, strtoupper($name));
		array_push(self::$routes, dirname($_SERVER['PHP_SELF']) . $arguments[0]);
	}

	/**
	 * 路由分发
	 * @throws \Exception
	 */
	public static function dicpatch()
	{
		//获取当前URL
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		//获取当前method
		$method = $_SERVER['REQUEST_METHOD'];
		$searches = array_keys(static::$patterns);
		$replaces = array_values(static::$patterns);
		foreach (self::$routes as $key => $value) {
			//查找路由是否使用正则
			if (strpos($value, ':')) {
				$value = str_replace($searches, $replaces, $value);
			}
			//界定查找
			if (preg_match('#^' . $value . '$#', $uri, $match)) {
				array_shift($match);
				if (self::$methods[$key] == $method || self::$methods[$key] == 'ANY') {
					if (!is_object(self::$controller[$key])) {
						$parts = explode('@', self::$controller[$key]);
						if (isset($parts[0]) && class_exists($parts[0])) {
							$controller = new $parts[0]();
							if (method_exists($controller, $parts[0])) {
								throw new \Exception('controller and action not found');
							} else {
								call_user_func_array(array($controller, $parts[1]), $match);
								return;
							}
						} else {
							throw new \Exception('controller and action not found');
						}
					} else {
						call_user_func_array(self::$controller[$key], $match);
						return;
					}
				}
			}
		}
		//404
		call_user_func(function () {
			header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
			echo '404 Not Found';
		});
	}

}