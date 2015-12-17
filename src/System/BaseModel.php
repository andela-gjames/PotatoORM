<?php

namespace BB8\Potatoes\ORM\System;
use BB8\Potatoes\ORM\System\StaticSQLQuery;

class BaseModel
{
    protected static $tableName;
    protected static $subClassName;
    protected static $modelProperties = array();

    /**
     * Call static property in class to initialize variables
     */
    public function __construct()
    {
        static::initializeQuery();
    }

    /**
     * Magic method used to set names dynamically
     * @param string $propName  name of the property to create
     * @param string $propValue value of the property to be created
     */
    public function __set($propName, $propValue)
    {
        $this->{$propName} = $propValue;
    }

    /**
     * Gets all data relating to calling class from database
     * @return array of result from databse
     */
    public static function getAll()
    {
        static::initializeQuery();
        return StaticSQLQuery::select();
    }



    /**
     * Initializes all static query and also
     * Initializes the StaticSQLQuery class
     */
    public static function initializeQuery()
    {
        if (static::$tableName == null) {
            static::$tableName =  end(explode("\\", get_called_class()));
        }
        static::$subClassName =  get_called_class();
        static::$tableName =  strtolower(static::$tableName);
        StaticSQLQuery::init(static::$tableName, static::$subClassName);
    }


}
