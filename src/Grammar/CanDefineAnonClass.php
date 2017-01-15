<?php

namespace Bagf\Dynamic\Grammar;

interface CanDefineAnonClass extends CanDefineClass
{
    public function setAnonymous();
    public function setDefined($name);
}
