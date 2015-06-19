<?php
namespace {
    function struct($name, $properties, $methods = array()) {
        return new Struct\Struct($name, $properties, $methods);
    }
}

namespace Struct {

    class Struct
    {
        protected $name;
        protected $properties;
        protected $methods;
        public static $strict = true;

        protected $src;

        public function __construct(string $name, array $properties, $methods = array()) {
            $this->name = $name;
            $this->properties = $properties;
            $this->methods = $methods;
            $this->src = '';

            if (!preg_match('/^[A-Z]\w+/', $name)) {
                throw new \InvalidArgumentException('Invalid struct name: ' . $name);
            }

            $this->struct();

            echo $this->src;

            eval($this->src);

            return new $name();
        }

        public function getSource() {
            return $this->src;
        }

        protected function struct() {
            $this->classHeader();
            $this->arrayHelpers();
            $this->properties();
            $this->methods();
            $this->classFooter();
        }

        protected function arrayHelpers() {
            $this->src .= <<<TOARRAYHELPER

    public function toArray() {
        \$array = array();
        foreach (\$this as \$prop => \$val) {
            \$array[\$prop] = \$val;
        }
        return \$array;
    }
TOARRAYHELPER;

            $existCheck = 'if (!$this->offsetExists($prop)) {';
            if (self::$strict) {
                $existCheck .= 'throw new \InvalidArgumentException(\'Struct does not contain property `\' . $key . \'`\');';
            } else {
                $existCheck .= 'continue;';
            }
            $existCheck .= '}';
            $this->src .= <<<FROMARRAYHELPER

    public function fromArray(\$array) {
        foreach (\$array as \$prop => \$val) {
            {$existCheck}

            \$this->offsetSet(\$prop,\$val);
        }
    }
FROMARRAYHELPER;
        }

        protected function methods() {
            foreach ($this->methods as $name => $fn) {

                $ref = new \ReflectionFunction($fn);
                $filename = $ref->getFileName();
                $start_line = $ref->getStartLine();
                $end_line = $ref->getEndLine()-1;
                $length = $end_line - $start_line;
                $source = file($filename);
                $body = implode("", array_slice($source, $start_line, $length));

                $this->src .= <<<METHOD

    public function {$name}() {
{$body}
    }
METHOD;
            }
        }

        protected function properties() {
            $propArray = array();
            foreach ($this->properties as $property => $type) {
                if (!in_array($type, array('int','string','float','bool')) && !class_exists($type)) {
                    throw new \InvalidArgumentException('Unknown property type for ' . $property . ': ' . $type);
                }
                if (!preg_match('/^[A-Za-z]\w+/', $property)) {
                    throw new \InvalidArgumentException('Invalid property name: ' . $property);
                }
                $propArray[] = "'{$property}'";

                $this->property($property, $type);
            }
            $this->src .= PHP_EOL . '    private $properties = array(' . implode(',', $propArray) . ');';
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
            if (self::$strict === true) {
                $prepend = 'declare(strict_types=1);' . PHP_EOL . PHP_EOL;
            }

            $this->src .= sprintf('
%sclass %s implements \ArrayAccess, \Iterator {', $prepend, $this->name) . PHP_EOL . PHP_EOL;
            $this->src .= <<<BOILERPLATE
    private \$idx;

    public function __construct() {
        \$this->idx = 0;
    }

    public function current() {
        \$getter = 'get_' . \$this->properties[\$this->idx];
        return \$this->{\$getter}();
    }

    public function key() {
        return \$this->properties[\$this->idx];
    }
    public function next() {
        ++\$this->idx;
    }
    public function rewind() {
        \$this->idx = 0;
    }
    public function valid() {
        return isset(\$this->properties[\$this->idx]);
    }

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

        return \$this->{\$getter}();
    }
BOILERPLATE;

        }
    }
}

