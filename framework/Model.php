<?php
namespace App\Framework;

use App\Framework\Db\MysqlPdo;

class Model
{
    protected $pdo;
    protected $data = array();

    public function __construct()
    {
        try {
            $pdo = new MysqlPdo();
            $pdo->query();
        }catch (\PDOException $exception){
            echo '<pre>';
            echo $exception->errorInfo;
            echo $exception->getMessage();
        }
    }

    public function setData()
    {

    }

    public function getData()
    {

    }

    public function setTable()
    {

    }

    public function getPrimary()
    {

    }

    public function setPrimary()
    {

    }

    public function find()
    {

    }

    public function findAndWhere()
    {

    }

    public function getList()
    {

    }

    public function save()
    {

    }

    public function delete()
    {

    }

    public function query()
    {

    }

    public function __destruct()
    {

    }

}