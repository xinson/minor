<?php
namespace App\Framework;

class Model
{
	protected $pdo;
	protected $data = array();

	public function __construct()
	{
		$pdo = new MysqlPdo();
		$this->pdo = $pdo->getInstance();
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