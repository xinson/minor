<?php
namespace App\Controllers;

use App\Framework\Application;
use App\Framework\Config;
use App\Framework\Model;

class demo
{
    public function index()
    {
    	/** @var Config $config */
		//$config = Application::getShare('config');
		//$data = $config->get('config');
		$demo = new Model();
    }

	public function test()
	{
		echo 'test';
	}

	public function view($id){
		echo $id;
	}

	public function login()
	{

	}

}