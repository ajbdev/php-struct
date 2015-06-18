<?php

require 'vendor/autoload.php';


use Struct\struct;


new struct('User', [
    'name'      =>  'string',
    'active'    =>  'bool',
    'age'       =>  'int'
]);

$user = new User();


$user['name'] = 'Andy Baird';
$user['age'] = 13;
$user['active'] = false;

var_dump($user);
