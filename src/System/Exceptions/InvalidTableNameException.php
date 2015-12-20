<?php

namespace BB8\Potatoes\ORM\System\Exceptions;

class InvalidTableNameException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
