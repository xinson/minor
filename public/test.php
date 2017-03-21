<?php

class SuperMan
{
	protected $power;

	public function __construct(array $modules)
	{
		//$this->power = new Power(999, 000);
		//$this->power = new Force(45);
		//$this->power = new Shot(99, 50, 2);
		/*$this->power = array(
			new Force(45),
			new Shot(99, 50, 2)
		);*/

		/**
		 * 使用工厂模式
		 */
		$factory = new Factory();

		//$this->power = $factory->makeModule('Fight', [9, 10]);
		//$this->power = $factory->makeModule('Force', [45]);
		//$this->power = $factory->makeModule('Shot', [99, 50, 2]);
		/*$this->power = array(
			$factory->makeModule('Force',[45]),
			$factory->makeModule('Shot',[99, 50, 2])
		);*/

		foreach ($modules as $moduleName => $moduleOptions){
			$this->power[] = $factory->makeModule($moduleName, $moduleOptions);
		}

	}
}

//创建超人
$superman = new SuperMan([
	'Fight' => [9, 100],
	'Shot' => [99, 50, 2]
]);

class Power
{
	/**
	 * 能力值
	 * @var
	 */
	protected $ability;

	/**
	 * 能力范围或距离
	 * @var
	 */
	protected $range;

	public function __construct($ability, $range)
	{
		$this->ability = $ability;
		$this->range = $range;
	}
}

class Fight
{
	protected $speed;
	protected $holdtime;

	public function __construct($speed, $holdtime)
	{
	}
}

class Force
{
	protected $force;

	public function __construct($force)
	{
	}
}

class Shot
{
	protected $atk;
	protected $range;
	protected $limit;

	public function __construct($atk, $range, $limit)
	{
	}
}

class Factory
{
	public function makeModule($moduleName, $options)
	{
		switch ($moduleName) {
			case 'Fight':
				return new Fight($options[0], $options[1]);
				break;
			case 'Force':
				return new Force($options[0]);
				break;
			case 'Shot':
				return new Shot($options[0], $options[1], $options[2]);
				break;
		}
	}
}

class Container
{
	protected $binds;

	protected $instances;

	public function bind($abstact, $concrete){
		//匿名函数
		if($concrete instanceof Closure){
			$this->binds[$abstact] = $concrete;
		}else{
			$this->instances[$abstact] = $concrete;
		}
	}

	public function make($abstact, $parameters = array())
	{
		if(isset($this->instances[$abstact])){
			return $this->instances[$abstact];
		}
		array_unshift($parameters);
		return call_user_func_array($this->binds[$abstact], $parameters);
	}
}


