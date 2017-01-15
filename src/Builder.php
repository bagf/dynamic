<?php

namespace Bagf\Dynamic;

use Bagf\Grammar\CanDefineClass;

class Builder
{
    /**
     * @param object $instance
     * @return Bagf\Dynamic\Builder
     */
    public static function fromInstance($instance)
    {
        // @TODO
    }
    
    /**
     * @param string $class
     * @return Bagf\Dynamic\Builder
     */
    public static function fromClass($class)
    {
        // @TODO
    }
    
    /**
     * @var \Bagf\Grammar\CanDefineClass 
     */
    protected $grammar;
    
    public function __construct(CanDefineClass $grammar)
    {
        $this->grammar = $grammar;
    }
    
    public function instance()
    {
        // @TODO
    }
}
