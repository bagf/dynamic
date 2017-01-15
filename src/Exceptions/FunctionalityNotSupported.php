<?php

namespace Bagf\Dynamic\Exceptions;

use Exception;

class FunctionalityNotSupported extends Exception
{
    public function __construct($function)
    {
        parent::__construct("Functionality for {$funciton} not supported");
    }
}
