<?php
namespace App\Controllers;

use App\Framework\Application;

class demo
{
    public function index()
    {
		$configObj = Application::getShare('config');
		print_r($configObj->get());
        echo '1111';
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