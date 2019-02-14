<?php
use Minor\Framework\Router;

Router::get('/', 'App\Controllers\Demo@index');
Router::get('/test.html', 'App\Controllers\Demo@test');
Router::get('/view/(:num)', 'App\Controllers\Demo@view');
/*Router::get('/(:any)', function($slug) {
	echo 'The slug is: ' . $slug;
});*/
Router::post('/login.html', 'App\Controllers\Demo@login');