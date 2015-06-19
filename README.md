Structs for PHP
===============
Structs for PHP7 inspired by golang

Usage
-----
    struct('User', [
        'name'      =>  'string',
        'age'       =>  'int',
        'active'    =>  'bool',
    ]);

    $user = new User();

    $user['name'] = 'Andy';
    $user['age'] = 13;
    $user['active'] = true;

    $user['email'] = 'andybaird@gmail.com';

    // Fatal error: Uncaught InvalidArgumentException: Struct does not contain property `email`

    $user['age'] = '22';

    // Fatal error: Uncaught TypeException: Argument 1 passed to User::set_age() must be of the type integer, string given

Turn off strict type checking and allow variables to be coerced into types by simply calling:

    Struct\Struct::$strict = false;
    $user['age'] = '22';
    var_dump($user['age']);

    // int(22)

Under the hood, structs are simply classes implementing ArrayAccess and Iterable generated at run time. They have generated getter and setters for all fields that allow them to do the type checking.

Filling a struct from an array:

    $row = $db->fetchArray('select * from user where id=1');
    $user->fromArray($row);    

You can extend structs further by giving them their own methods.

    struct('User', [
        'firstName'         =>  'string',
        'lastName'          =>  'string',
        'active'            =>  'bool',
        'age'               =>  'int'
    ],[
        'fullName'          =>  function() {
            return $this['firstName'] . ' ' . $this['lastName'];
        }
    ]);

    $user['firstName'] = 'Andy';
    $user['lastName'] = 'Baird';

    echo $user->fullName();
    // Andy Baird

Add magic methods simply:

    struct('User', [
        'firstName'         =>  'string',
        'lastName'          =>  'string',
        'active'            =>  'bool',
        'age'               =>  'int'
    ],[
        '__toString'          =>  function() {
            return $this['firstName'] . ' ' . $this['lastName'] . ' is a ' . $this['age'] . ' year old ' . ($this['active'] ? 'active' : 'inactive') . ' user';
        }
    ]);

    echo $user;
    // Andy Baird is a 13 year old inactive user


But... why?
-----------
Just for my own experimentation. I would love to see structs implemented as a core feature of PHP, as I can see them being very appropriate for a more procedural or functional style of programming.


Benchmarks
----------
SOON!