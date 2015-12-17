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

    /**
     * inserts new data into database table
     * @param  mixed [$data         = null] object of class to insert
     * @return boolean  of result of query
     */
    public static function insert($data = null)
    {
        //Through Exception if $data is null
        if ($data === null) {
            throw new \InvalidArgumentException("Cannot save null to the database table ".self::$tableName);
        }

        //Use util method to convert data to array
        $data = self::to_array($data);

        //Get the columns/fields and convert them to string
        $fields     = implode(", ", array_keys($data));

        //Create placeholders based on the number of data to insert
        $bindable   = implode(", ", array_values(array_fill(0, count($data), '?')));

        //Get the array of data to insert into database
        $values     = array_values($data);

        //Build the query
        $query = "INSERT INTO ".self::$tableName." ($fields) VALUES ($bindable) ";

        try {
            //Prepare the query
            $statement = static::$dbHandler->prepare($query);
            //Excecute and return boolean
            return $statement->execute($values);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Converts object to array by casting
     * @param  mixed $object class object to be converted
     * @return array of converted object
     */
    private static function to_array($object)
    {
        return (array)$object;
    }

}
