<?php


use Core\Router;

//renders file from :App/View/welcome.html and give that route a name
Router::get('')->view('welcome')->callAs('home');
