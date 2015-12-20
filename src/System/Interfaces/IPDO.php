<?php

namespace BB8\Potatoes\ORM\System\Interfaces;

interface IPDO
{
    public static function getInstance();
    public function connect();
    public function close();
}
