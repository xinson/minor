<?php
namespace App\Framework\Db;

use App\Framework\Application;
use App\Framework\Logger\Logger;
use PDO;
use PDOException;

class MysqlPdo
{
    protected static $dbConfig;
    protected static $instance = null;
    public $dbh;
    public $log;

    public function __construct()
    {
        $configObj = Application::getShare('config');
        $dbConfig = $configObj->get('mysql');
        /** @var Logger $log */
        $this->log = Application::getShare('logger');
        if (!empty($dbConfig)) {
            $dsn = 'mysql:dbname=' . $dbConfig['database'] . ';host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . '';
            try {
                $this->dbh = new PDO("$dsn", $dbConfig['username'], $dbConfig['password']);
            } catch (PDOException $exception) {
                //记录错误
                $this->log->alert($exception->errorInfo.$exception->getMessage());
                throw new PDOException($exception);
            }
        }
    }

    public function insert($sql, array $data = array())
    {
        $stmt = $this->dbh->prepare($sql);
        if(!empty($data)) {
            foreach ($data as $key => $val) {
                $stmt->bindParam(':'.$key,$val);
            }
        }
        $rs = $stmt->execute();
        if($rs==FALSE){
            $this->log->alert($stmt->errorInfo());
            return false;
        }
        $lastId = $this->dbh->lastInsertId();
        if(!$lastId==0){
            return true;
        }
        return $lastId;
    }

    public function query($sql, array $data = array())
    {
        $stmt = $this->dbh->prepare($sql);
        if(!empty($data)){
            foreach ($data as $key => $val){
                $stmt->bindParam(':'.$key,$val);
            }
        }
        $rs = $stmt->execute();
        if($rs==FALSE){
            $this->log->alert($stmt->errorInfo());
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}