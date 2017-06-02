<?php

namespace App\Framework;

use App\Framework\Db\MysqlPdo;

class Model
{
    protected $pdo;
    protected $table;
    protected $primaryKey;
    protected $exists;
    protected $original = array();
    protected $data = array();

    public function __construct()
    {
        $this->pdo = new MysqlPdo();
    }

    public function setData($key, $value = '')
    {
        if(!empty($key) && !empty($value)){
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function getData($key = '')
    {
        if(!empty($key)){
            return array_key_exists($key,$this->data)?$this->data[$key]:'';
        }else{
            return $this->data;
        }
    }

    public function setTable($table)
    {
        if(!empty($table)){
            $this->table = $table;
        }
        return $this;
    }

    public function getPrimary($primaryKey)
    {
        return $this->primaryKey = $primaryKey;
    }

    public function setPrimary($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function find($id)
    {
        if(!empty($id)) {
            $sql = " SELECT * FROM {$this->getTable()} WHERE {$this->getPrimary()} = :{$this->getPrimary()} ";
            $array = $this->pdo->query($sql, array($this->getPrimary() => $id));
            if(!empty($array) && !is_array($array)){
                foreach ($array as $d => $v){
                    $this->setData($d,$v);
                }
                $this->exists = true;
                $this->original = $this->getData();
            }
        }
        return $this->getData();
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