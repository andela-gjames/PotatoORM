<?php

namespace BB8\Potatoes\ORM\System;

class BaseModel
{
    protected static $tableName;
    protected static $classNS;
    protected static $DBH;

    /**
     * @codeCoverageIgnore
     */
    protected static function initialize()
    {
        static::$classNS = get_called_class();
        static::$tableName = static::getTableName(static::$classNS);

        $pdo = PDOConnection::getInstance();
        static::$DBH = $pdo->connect();
    }

    /**
     * Returns all record of model in the database.
     *
     * @return array of model objects
     */
    public static function getAll()
    {
        //Initialize Parameters
        static::initialize();

        //Run database query to select
        $result = SQLQuery::select(static::$tableName, static::$classNS, static::$DBH);

        //Return result
        return $result;
    }

    /**
     * Finds a particular record from database.
     *
     * @param int $tableID Table ID of row to select
     *
     * @return object type of calling class
     */
    public static function find($tableID)
    {
        //Initialize Parameters
        static::initialize();

        //Run databse select query
        $result = SQLQuery::select(
            static::$tableName,
            static::$classNS,
            static::$DBH,
            array('*'),
            array('id' => $tableID)
        );

        //Remove first result from array and return object
        return array_shift($result);
    }

    /**
     * Selects record based on WHERE Clause.
     *
     * @param array $where Parameters to use as WHERE clause condition
     * @param  array [$fields               = array('*')] fields to select from the query
     *
     * @return array of calling class data type
     */
    public static function selectWhere($where, $fields = array('*'))
    {
        //Initialize parameters
        static::initialize();

        //Run query
        $result = SQLQuery::select(static::$tableName, static::$classNS, static::$DBH, $fields, $where);

        //Return result
        return $result;
    }

    /**
     * Deletes a particular database record.
     *
     * @param int $tableID Row ID of record to delete
     *
     * @return bool if transaction completes returns true, otherwise false
     */
    public static function destroy($tableID)
    {
        //Initialize Parameters
        static::initialize();

        //Run SQL query
        $result = SQLQuery::delete(static::$tableName, static::$DBH, array('id' => $tableID));

        //Return result
        return $result;
    }

    /**
     * Saves a new record or Updates an already created one.
     *
     * @return [[Type]] [[Description]]
     */
    public function save()
    {
        //Initialize parameters
        static::initialize();

        //Check if Update or Insert is needeed and run required query
        if (isset($this->id)) {
            $result = SQLQuery::update(static::$tableName, static::$DBH, $this);
        } else {
            $result = SQLQuery::insert(static::$tableName, static::$DBH, $this);
        }

        //Return result
        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    final private static function getTableName($namespace)
    {
        $classVariables = get_class_vars($namespace)['tableName'];
        $name = $classVariables ? strtolower($classVariables) : strtolower($classVariables);
        $tableName = $name ? strtolower($name) : strtolower($name);

        return $tableName;
    }
}
