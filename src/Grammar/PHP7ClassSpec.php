<?php

namespace Bagf\Dynamic\Grammar;

use ReflectionType;

class PHP7ClassSpec implements CanDefineAnonClass
{
    protected $methods = [];
    protected $constants = [];
    protected $properties = [];
    protected $name = null;
    protected $extend = null;
    protected $interfaces = [];

    public function extend($class)
    {
        $this->extend = $class;
    }

    public function giveName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function implement($interface)
    {
        if (!in_array($interface, $this->interfaces)) {
            $this->interfaces[] = $interface;
        }
    }

    public function privateConstant($name, $value)
    {
        $this->constant('private', $name, $value);
    }

    public function privateMethod($name, $function, $isStatic = false, $return = null)
    {
        $this->method('private', $name, $function, $isStatic, $return);
    }

    public function privateProperty($name, $isStatic = false)
    {
        $this->property('private', $name, $isStatic);
    }

    public function protectedConstant($name, $value)
    {
        $this->constant('protected', $name, $value);
    }

    public function protectedMethod($name, $function, $isStatic = false, $return = null)
    {
        $this->method('protected', $name, $function, $isStatic, $return);
    }

    public function protectedProperty($name, $isStatic = false)
    {
        $this->property('protected', $name, $isStatic);
    }

    public function publicConstant($name, $value)
    {
        $this->constant('public', $name, $value);   
    }

    public function publicMethod($name, $function, $isStatic = false, $return = null)
    {
        $this->method('public', $name, $function, $isStatic, $return);
    }

    public function publicProperty($name, $isStatic = false)
    {
        $this->property('public', $name, $isStatic);
    }

    public function setAnonymous()
    {
        $this->giveName(null);
    }

    public function setDefined($name)
    {
        $this->giveName($name);
    }

    protected function method($access, $name, $function, $isStatic, $return)
    {
        if ($return instanceof ReflectionType) {
            if ($return->allowsNull()) {
                $return = ':? '.$return->__toString();
            } else {
                $return = ': '.$return->__toString();
            }
        }
        $this->methods[$name]['method'] = "{$access}".($isStatic?' static':'')." {$name}";
        $this->methods[$name]['return'] = "{$return}";
        $this->methods[$name]['func'] = "\n{$function}";
    }
    
    protected function constant($access, $name, $value)
    {
        $this->constants[$name] = "{$access} const {$name} = {$value};";
    }
    
    protected function property($access, $name, $isStatic)
    {
        $this->properties[$name] = "{$access}".($isStatic?' static':'')." \${$name}";
    }

    public function methodParameter($method, $name, $type, $default = '#nodefault#')
    {
        $params = '';
        if (!empty("{$type}")) {
            $params .= "{$type} ";
        }
        $params .= "\${$name}";
        if ($default != '#nodefault#') {
            if (is_null($default) || $default == "'null'") {
                $default = 'null';
            }
            $params .= " = {$default}";
        }
        
        if (isset($this->methods[$method])) {
            if (!isset($this->methods[$method]['params'])) {
                $this->methods[$method]['params'] = [];
            }
            $this->methods[$method]['params'][] = $params;
        }
    }

    public function defineClass()
    {
        
    }
}
