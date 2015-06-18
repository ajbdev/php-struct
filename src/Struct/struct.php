<?php

namespace Struct;

function struct($name, $properties, $strict) {
    return new struct($name, $properties, $strict);
}

class struct
{
    protected $name;
    protected $properties;
    protected $strict;

    protected $src;

    public function __construct(string $name, array $properties, bool $strict = true) {
        $this->name = $name;
        $this->properties = $properties;
        $this->strict = $strict;
        $this->src = '';

        if (!preg_match('/^[A-Z]\w+/', $name)) {
            throw new \InvalidArgumentException('Invalid struct name: ' . $name);
        }

        $this->struct();

        eval($this->src);

        return new $name();
    }

    protected function struct() {
        $this->classHeader();
        $this->properties();
        $this->classFooter();
    }

    protected function properties() {
        foreach ($this->properties as $property => $type) {
            if (!in_array($type, array('int','string','float','bool')) && !class_exists($type)) {
                throw new \InvalidArgumentException('Unknown property type for ' . $property . ': ' . $type);
            }
            if (!preg_match('/^[A-Za-z]\w+/', $property)) {
                throw new \InvalidArgumentException('Invalid property name: ' . $property);
            }
            $this->property($property, $type);
        }
    }

    protected function property($name, $type) {
        $this->src .= <<<PROPERTY
    private \${$name};

    private function set_{$name}({$type} \$val) {
        \$this->{$name} = \$val;
    }

    private function get_{$name}():{$type} {
        return \$this->{$name};
    }
PROPERTY;

    }

    protected function classFooter() {
        $this->src .= PHP_EOL . '}';
    }

    protected function classHeader() {
        $prepend = '';
        if ($this->strict) {
            $prepend = 'declare(strict_types=1);' . PHP_EOL . PHP_EOL;
        }
        $this->src .= sprintf('
%sclass %s implements \ArrayAccess {', $prepend, $this->name) . PHP_EOL . PHP_EOL;
        $this->src .= <<<BOILERPLATE

    public function offsetSet(\$key, \$value) {
        if (!\$this->offsetExists(\$key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . \$key . '`');
        }
        \$setter = 'set_' . \$key;

        \$this->{\$setter}(\$value);
    }

    public function offsetExists(\$key) {
        return property_exists(\$this, \$key);
    }

    public function offsetUnset(\$key) {
        if (!\$this->offsetExists(\$key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . \$key . '`');
        }

        \$this->{\$key} = null;
    }

    public function offsetGet(\$key) {
        if (!\$this->offsetExists(\$key)) {
            throw new \InvalidArgumentException('Struct does not contain property `' . \$key . '`');
        }

        \$getter = 'get_' . \$key;

        return \$this->{\$getter};
    }
BOILERPLATE;

    }
}