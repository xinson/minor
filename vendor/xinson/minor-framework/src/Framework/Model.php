<?php

namespace Minor\Framework;

use Minor\Framework\Db\MysqlPdo;

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

    public function getId()
    {
        return $this->getData($this->getPrimary());
    }

    /**
     * @param $id
     * @return $this
     */
    public function find($id)
    {
        if(!empty($id)) {
            $sql = " SELECT * FROM {$this->getTable()} WHERE {$this->getPrimary()} = :{$this->getPrimary()}  LIMIT 1";
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

    /**
     * @param $where
     * @param array $bind
     * @return $this
     *
     * findAndWhere(array('name' => ':name'), array('name' => 'test'))
     * findAndWhere(array('name' => ':name', 'id' => 1), array('name' => 'test'))
     * findAndWhere('name = :name', array('name' => 'test'))
     * findAndWhere('name = :name and id = 3', array('name' => 'test'))
     */
    public function findAndWhere($where, $bind = array())
    {
        $whereSql = ' 1 ';
        if(!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $k => $v) {
                    $whereSql .= ' and ' . $k . ' = ' . $v;
                }
            } else {
                $whereSql = $where;
            }
        }
        $sql = " SELECT * FROM {$this->getTable()} WHERE $whereSql LIMIT 1";
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

    /**
     * @param string $where
     * @param array $bind
     * @param string $orderStr
     * @param int $limit
     * @return array|bool
     */
    public function getList($where = '', $bind = array(),  $orderStr = '', $limit = 10)
    {
        $whereSql = ' 1 ';
        if(!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $k => $v) {
                    $whereSql .= ' and ' . $k . ' = ' . $v;
                }
            } else {
                $whereSql = $where;
            }
        }
        $sql = " SELECT * FROM {$this->getTable()} WHERE $whereSql";
        if(empty($orderStr)){
            $sql .= " ORDER BY $this->getPrimary() DESC";
        }else{
            $sql .= $orderStr;
        }
        $sql .= " LIMIT  $limit";
        $array = $this->pdo->query($sql,$bind);
        return $array;
    }

    /**
     * @return array|bool
     */
    public function save()
    {
        $data = $this->getData();
        if(!empty($this->exists)){
            $different = array();
            $bind = array();
            if(!empty($data)) {
                foreach ($data as $d => $v) {
                    if (array_key_exists($d, $this->original)) {
                        //查找update的数据 是否有更新
                        if ($this->original[$d] != $v && !strcmp((string)$this->original[$d], (string)$v) !== 0) {
                            $different[$d] = $v;
                            $bind[$d] = $d . ' = :' . $d;
                        }
                    }
                }
            }
            if(!empty($different) && is_array($different)){
                $where = $this->getPrimary() .' = '.(int)$this->getId();
                $fields = implode(',', $bind);
                $sql = "UPDATE {$this->getTable()} SET $fields WHERE $where";
                return $this->pdo->update($sql,$different);
            }else{
                return false;
            }
        } else {
            if (!empty($data) && is_array($data)) {
                $fieldKeys= array_keys($data);
                $field = implode(',', $fieldKeys);
                $values = ':' . implode(',:', $fieldKeys);
                $sql = "INSERT INTO {$this->getTable()} ({$field}) VALUES ({$values})";
                $this->pdo->insert($sql, $this->getData());
            }
        }
    }

    public function delete()
    {
        if($this->getData()) {
            $where = $this->getPrimary() . ' = :' . $this->getPrimary();
            $sql = "DELETE FROM {$this->getTable()} WHERE $where";
            $data = array($this->getPrimary() => $this->getId());
            $this->pdo->query($sql,$data);
        }
    }

    public function __set($key, $value)
    {
        $this->setData($key,$value);
        return $this;
    }

    public function __get($key)
    {
        return $this->getData($key);
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