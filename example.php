<?php

require 'vendor/autoload.php';

Struct\Struct::$strict = false;

struct('User', [
    'name'      =>  'string',
    'age'       =>  'int',
    'active'    =>  'bool',
]);

$user = new User();
$user->fromArray(array(
    'name'      =>  'George',
    'age'       =>  '36',
    'active'    =>  false,
));

var_dump($user->toArray());
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




echo $user;