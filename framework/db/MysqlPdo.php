<?php
namespace App\Framework\Db;

use App\Framework\Application;

class MysqlPdo
{
    protected static $dbConfig;
    protected static $instance = null;

    public function __construct()
    {
        $configObj = Application::getShare('config');
        $MysqlConfig = $configObj->get('config');
        $dbConfig = $MysqlConfig['mysql'];
        if (!empty($dbConfig)) {
            $dsn = 'mysql:dbname=' . $dbConfig['database'] . ';host=' . $dbConfig['username'] . ';port=' . $dbConfig['port'] . '';
            try {
                $this->pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            } catch (\PDOException $exception) {
                //记录错误
                //$exception->errorInfo;
                //$exception->getMessage();
                throw new \PDOException($exception);
            }
        }
    }

    public function query()
    {
    }


}