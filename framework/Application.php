<?php
namespace App\Framework;
use App\Framework\Container;

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