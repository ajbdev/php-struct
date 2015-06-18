<?php

require 'vendor/autoload.php';

Struct\Struct::$strict = false;

struct('User', [
    'name'      =>  'string',
    'age'       =>  'int',
    'active'    =>  'bool',
]);

$user = new User();

$user['name'] = 'Andy';
$user['age'] = '22';
$user['active'] = true;


var_dump($user['age']);
exit;
struct('User', [
    'firstName'         =>  'string',
    'lastName'          =>  'string',
    'active'            =>  'bool',
    'age'               =>  'int'
],[
    'fullName'          =>  function() {
        return $this['firstName'] . ' ' . $this['lastName'];
    },
    'birthYear'         =>  function() {
        $year = date('Y') - $this['age'];

        return $year;
    },
    '__toString'        =>  function() {
        return $this->fullName() . ' is a ' . $this['age'] . ' year old ' . ($this['active'] ? 'active' : 'inactive') . ' user';
    }
]);

$user = new User();

$user['firstName'] = 'Andy';
$user['lastName'] = 'Baird';
$user['age'] = '31';
$user['active'] = false;


echo $user;