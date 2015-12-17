<?php

namespace BB8\Potatoes\ORM\System;
use BB8\Potatoes\ORM\System\PDO;
use BB8\Potatoes\ORM\Exceptions\InvalidTableNameException;
class StaticSQLQuery
{
    protected static $tableName;
    protected static $dbHandler;
    protected static $className;

    /**
     * Intializes static properties required for perforing query to db
     * @param string $tableName name of the database table to connect to
     * @param string $className name of calling class
     */
    public static function init($tableName, $className)
    {
        self::$tableName = $tableName;
        self::$dbHandler = PDO::getInstance()->connect();
        self::$className = $className;
    }

    /**
     * Queries and gets data from the database
     * @param  array [array $fields = ["*"]] databse columns to return
     * @return array of data from the query result
     */
    public static function select(array $fields = ["*"])
    {
        //Convert array to string, seperated by comma
        $fields = implode(",", $fields);

        //Query Database
        $query  = self::$dbHandler->query("select $fields from ".self::$tableName);
        if ($query  === false) {
            throw new InvalidTableNameException("There is no class with the name ".self::$tableName);
        }

        //Set the return type to the type of the calling class
        $query->setFetchMode(\PDO::FETCH_CLASS, self::$className);

        //Return all data if default value is *
        if ($fields == "*") {
            return $row = $query->fetchAll();
        }

        $result = array();

        while ($row = $query->fetch()) {
            foreach ($fields as $field) {
                $result[$field] = $row[$field];
            }
        }
        return $result;
    }
}
