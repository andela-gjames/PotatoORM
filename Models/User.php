<?php
namespace BB8\Potatoes\ORM\Models;

use BB8\Potatoes\ORM\System\BaseModel;

class User extends BaseModel
{
    protected static $tableName = "Users";

    public function getName()
    {
        return "This is the name function for this";
    }

}
