<?php
namespace App\Framework;

class Model
{
	public static $instance = null;
	public $pdo;

	public function __construct()
	{
		$dsn = '';
		$username = '';
		$passwd = '';
		$options = '';
		$this->pdo = new \PDO($dsn, $username, $passwd, $options);
	}

	public function setData(){

	}

	public function getData(){

	}

	public function setTable(){

	}

	public function getPrimary(){

	}

	public function setPrimary(){

	}

	public function find(){

	}

	public function findAndWhere(){

	}

	public function save(){

	}

	public function delete(){

	}

	public function query(){

	}

}