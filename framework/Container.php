<?php
namespace App\Framework;

/**
 * IOC容器
 * Class Container
 * @package App\Framework
 */
class Container
{
	protected $binds;
	protected $instances;

	/**
	 * 绑定
	 * @param $abstact
	 * @param $concrete
	 */
	public function bind($abstact, $concrete)
	{
		if ($concrete instanceof \Closure) {
			$this->binds[$abstact] = $concrete;
		} else {
			$this->instances[$abstact] = $concrete;
		}
	}

	/**
	 * 启动类
	 * @param $abstact
	 * @param array $parameters
	 * @return mixed
	 */
	public function make($abstact, $parameters = array())
	{
		if (isset($this->instances[$abstact])) {
			return $this->instances[$abstact];
		}
		array_unshift($parameters, $this);
		return call_user_func_array($this->binds[$abstact], $parameters);
	}
}

