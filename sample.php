<?php

declare(strict_types=1);

class UserStruct implements ArrayAccess {
    private $name;
    private $email;
    private $active;
    private $age;

    public function offsetSet($key, $value) {
        if (!$this->offsetExists($key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . $key . '`');
        }
        $setter = 'set_' . $key;

        $this->{$setter}($value);
    }

    public function offsetExists($key) {
        return property_exists($this, $key);
    }

    public function offsetUnset($key) {
        if (!$this->offsetExists($key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . $key . '`');
        }

        $this->{$key} = null;
    }

    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . $key . '`');
        }

        $getter = 'get_' . $key;

        return $this->{$getter};
    }

    private function set_name(string $val) {
        $this->name = $val;
    }

    private function get_name():string {
        return $this->name;
    }

    private function set_email(string $val) {
        $this->email = $val;
    }

    private function get_email():string {
        return $this->email;
    }

    private function set_active(bool $val) {
        $this->active = $val;
    }

    private function get_active():bool {
        return $this->active;
    }

    private function set_age(int $val) {
        $this->age = $val;
    }

    private function get_age():int {
        return $this->age;
    }

}


$user = new UserStruct();
$user['name'] = 'andybaird@gmail.com';

var_dump($user);
