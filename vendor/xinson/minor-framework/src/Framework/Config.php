<?php
namespace Minor\Framework;

/**
 * 配置类
 * Class Config
 * @package App\Framework
 */
class Config
{
	protected $config = array();

	/**
	 * 加载配置
	 * Config constructor.
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->config = $data;
	}

	/**
	 * 获取配置
	 * @param $key
	 * @param null $default
	 * @return array|mixed|null
	 */
	public function get($key = null, $default = null)
	{
		if (array_key_exists($key, $this->config)) {
			return $this->config[$key];
		} elseif (!empty($default)) {
			return $default;
		}
		return $this->config;
	}

	/**
	 * 设置配置
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->config[$key] = $value;
		return $this;
	}

	/**
	 * 判断是否配置
	 * @param $key
	 * @return bool
	 */
	public function has($key)
	{
		if (array_key_exists($this->config, $key)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取所有配置
	 * @return array
	 */
	public function all()
	{
		return $this->config;
	}
}