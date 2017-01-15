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
    protected $traits = [];

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
            foreach ($this->interfaces as $i => $iface) {
                if (is_subclass_of($iface, $interface)) {
                    return ;
                }
            }
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
        } else {
            $return = '';
        }
        $this->methods[$name]['params'] = [];
        $this->methods[$name]['method'] = "{$access}".($isStatic?' static':'')." function {$name}";
        $this->methods[$name]['return'] = "{$return}";
        $this->methods[$name]['func'] = "\n{$function}";
    }
    
    protected function constant($access, $name, $value)
    {
        $this->constants[$name] = "{$access} const {$name} = {$value};";
    }
    
    protected function property($access, $name, $isStatic)
    {
        $this->properties[$name] = "{$access}".($isStatic?' static':'')." \${$name};";
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
            $this->methods[$method]['params'][] = $params;
        }
    }
    
    public function shareTrait($trait)
    {
        $this->traits[$trait] = "use {$trait};";
    }
    
    protected function defineAnonClass()
    {
        // Clear construct as it cannot be eval'ed at this point
        if (isset($this->methods['__construct'])) {
            unset($this->methods['__construct']);
        }
        
        return "\$newClass = new class()";
    }

    public function defineClass()
    {
        if (is_null($this->name)) {
            $class = $this->defineAnonClass();
        } else {
            $class = "class {$this->name}";
        }
        
        if (!is_null($this->extend)) {
            $class .= " extends {$this->extend}";
        }
        
        if (count($this->interfaces) > 0) {
            $class .= " implements ".implode(', ', $this->interfaces);
        }
        
        $code = "{$class}\n{\n";
        
        $code .= implode("\n", $this->constants);
        $code .= "\n";
        
        $code .= implode("\n", $this->properties);
        $code .= "\n";
        
        $code .= implode("\n", $this->traits);
        $code .= "\n";
        
        foreach ($this->methods as $m) {
            $code .= "{$m['method']}(".implode(", ", $m['params'])."){$m['return']}";
            $code .= $m['func'];
            $code .= "\n";
        }
        
        if (is_null($this->name)) {
            return "{$code}\n};\n";
        }
        
        return "{$code}\n}\n";
    }
}
