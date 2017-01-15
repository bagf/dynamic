<?php

namespace Bagf\Dynamic;

use ReflectionObject;
use ReflectionClass;

class Builder
{
    /**
     * @param string $version
     * @return \Bagf\Dynamic\Grammar\PHP7ClassSpec
     */
    public static function resolveGrammar($version = PHP_VERSION)
    {
        if (version_compare($version, '7.0.0') >= 0) {
            return new Grammar\PHP7ClassSpec;
        }
        /**
         * @todo php 5 support
         */
    }
    
    /**
     * @param object $instance
     * @return \Bagf\Dynamic\Builder
     */
    public static function fromInstance($instance)
    {
        $grammar = static::resolveGrammar();
        $reflect = new ReflectionObject($instance);
        $restore = ['static' => [], 'property' => [],];
        
        if ($reflect->isInternal()) {
            throw new Exceptions\FunctionalityNotSupported('Internal Class');
        }
        
        if (!$reflect->isInstantiable()) {
            throw new Exceptions\FunctionalityNotSupported('Class not instantiable');
        }
        
        $grammar->giveName($reflect->getName());
        
        if ($reflect->isAnonymous()) {
            if (!$grammar instanceof Grammar\CanDefineAnonClass) {
                throw new Exceptions\FunctionalityNotSupported('CanDefineAnonClass');
            }
            
            $grammar->setAnonymous();
        }
        
        $parent = $reflect->getParentClass();
        if ($parent instanceof ReflectionClass) {
            $grammar->extend($parent->getName());
        }
        
        foreach ($reflect->getInterfaces() as $interface) {
            $grammar->implement($interface->getName());
        }
        
        foreach ($reflect->getTraits() as $trait) {
            $grammar->shareTrait($trait->getName());
        }
        
        foreach ($reflect->getConstants() as $constant => $value) {
            $grammar->publicConstant($constant, static::exportVarLine($value));
        }
        
        foreach ($reflect->getProperties() as $property) {
            $access = null;
            if ($property->isPublic()) {
                $grammar->publicProperty($property->getName(), $property->isStatic());
                $access = true;
            } else if ($property->isProtected()) {
                $grammar->protectedProperty($property->getName(), $property->isStatic());
                $access = false;
            } else if ($property->isPrivate()) {
                $grammar->privateProperty($property->getName(), $property->isStatic());
                $access = false;
            }
            if (!is_null($access)) {
                if (!$access) {
                    $property->setAccessible(true);
                    $value = $property->getValue($instance);
                    $property->setAccessible(false);
                } else {
                    $value = $property->getValue($instance);
                }
                $restore[(($property->isStatic())?'static':'property')][$property->getName()] = $value;
            }
        }
        
        $files = [];
        
        foreach ($reflect->getMethods() as $method) {
            if ($method->isAbstract()) {
                continue;
            }
            
            if (!isset($files[$method->getFileName()])) {
                $files[$method->getFileName()] = file($method->getFileName());
            }
            $code = static::extractCode($files[$method->getFileName()], $method->getStartLine(), $method->getEndLine());
            
            if ($method->isPublic()) {
                $grammar->publicMethod($method->getName(), $code, $method->isStatic(), $method->getReturnType());
            } else if ($method->isProtected()) {
                $grammar->protectedMethod($method->getName(), $code, $method->isStatic(), $method->getReturnType());
            } else if ($method->isPrivate()) {
                $grammar->privateMethod($method->getName(), $code, $method->isStatic(), $method->getReturnType());
            }
            
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->isDefaultValueAvailable()) {
                    $default = $parameter->getDefaultValueConstantName();
                    if (is_null($default)) {
                        $default = static::exportVarLine($parameter->getDefaultValue());
                    }
                } else {
                    $default = '#nodefault#';
                }
                $grammar->methodParameter($method->getName(), $parameter->getName(), $parameter->getType(), $default);
            }
        }
        
        return new static($grammar, $restore);
    }
    
    /**
     * @param string $class
     * @return \Bagf\Dynamic\Builder
     */
    public static function fromClass($class)
    {
        // @TODO
    }
    
    /**
     * @var \Bagf\Dynamic\Grammar\CanDefineClass 
     */
    protected $grammar;
    protected $restore;

    public function __construct(Grammar\CanDefineClass $grammar, $restore = [])
    {
        $this->grammar = $grammar;
        $this->restore = $restore;
    }
    
    public function instance()
    {
        $code = $this->grammar->defineClass();
        
        eval($code);
        
        if (is_null($this->grammar->getName()) && isset($newClass)) {
            $reflect = new ReflectionObject($newClass);
            foreach ($this->restore['property'] as $name => $value) {
                $p = $reflect->getProperty($name);
                if (!$p->isPublic()) {
                    $p->setAccessible(true);
                    $p->setValue($newClass, $value);
                    $p->setAccessible(false);
                } else {
                    $p->setValue($newClass, $value);
                }
            }
            
            foreach ($this->restore['static'] as $name => $value) {
                $p = $reflect->getProperty($name);
                if (!$p->isPublic()) {
                    $p->setAccessible(true);
                    $p->setValue(null, $value);
                    $p->setAccessible(false);
                } else {
                    $p->setValue(null, $value);
                }
            }
            
            return $newClass;
        }
    }
    
    protected static function extractCode($file, $start, $end)
    {
        if (is_string($file)) {
            $file = file($file);
        }
        return implode("\n", array_slice($file, $start, ($end - $start)));
    }
    
    public static function exportVarLine($var)
    {
        return str_replace("\n", '', var_export($var, true));
    }
}
