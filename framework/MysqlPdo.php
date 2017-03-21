<?php

namespace App\Framework;


class MysqlPdo
{
	protected static $dbConfig;
	protected static $instance = null;

	public function __construct()
	{
		$configObj = Application::getShare('config');
		$dbConfig = $configObj->get('config');
		if(!empty($dbConfig)) {
			$dsn = 'mysql:dbname=' . $dbConfig['database'] . ';host=' . $dbConfig['username'] . ';port=' . $dbConfig['port'] . '';
			try {
                $this->pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            }catch (\PDOException $exception){
                //记录错误
                $exception->errorInfo;
                $exception->getMessage();
            }
		}
	}

	/**
	 * 单例获取mysql
	 * @return MysqlPdo|null
	 */
	public function getInstance()
	{
		if(self::$instance==null){
             self::$instance = new MysqlPdo();
		}
		return self::$instance;
	}

	public function query()
	{

	}


}