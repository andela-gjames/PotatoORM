<?php

namespace BB8\Potatoes\ORM\System\Exceptions;

class PropertyDoesNotExistException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
