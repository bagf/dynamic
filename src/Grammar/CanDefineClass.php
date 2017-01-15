<?php

namespace Bagf\Dynamic\Grammar;

interface CanDefineClass
{
    public function giveName($name);
    public function publicMethod($name, $function, $isStatic = false, $return = null);
    public function protectedMethod($name, $function, $isStatic = false, $return = null);
    public function privateMethod($name, $function, $isStatic = false, $return = null);
    public function publicConstant($name, $value);
    public function protectedConstant($name, $value);
    public function privateConstant($name, $value);
    public function publicProperty($name, $isStatic = false);
    public function protectedProperty($name, $isStatic = false);
    public function privateProperty($name, $isStatic = false);
    public function methodParameter($method, $name, $type, $default = '#nodefault#');
    public function implement($interface);
    public function shareTrait($trait);
    public function extend($class);
    public function defineClass();
    public function getName();
}
