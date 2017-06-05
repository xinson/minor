<?php

namespace App\Framework;

use App\Framework\Db\MysqlPdo;

class Model
{
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
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

    public function getTable(){
        return $this->table;
    }

    public function getPrimary()
    {
        return $this->primaryKey;
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
            if(!empty($array[0]) && is_array($array[0])){
                foreach ($array[0] as $d => $v){
                    $this->setData($d,$v);
                }
                $this->exists = true;
                $this->original = $this->getData();
            }
        }
        return $this;
    }

    public function findAndWhere($where, $bind = array())
    {
        $whereSql = '';
        if(is_array($where)){
            foreach ($where as $k => $v){
                $whereSql .= ' and '.$k;
            }
        }else{
            $whereSql = $where;
        }
        $sql = " SELECT * FROM {$this->getTable()} WHERE $whereSql ";
        $array = $this->pdo->query($sql,$bind);
        if(!empty($array[0]) && is_array($array[0])){
            foreach ($array[0] as $d => $v){
                $this->setData($d,$v);
            }
            $this->exists = $array;
            $this->original = $this->getData();
        }
        return $this;
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
        $this->data = array();
        $this->exists = '';
        $this->original = array();
        $this->pdo = '';
        $this->table = '';
    }

}