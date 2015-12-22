<?php

namespace BB8\Potatoes\ORM\System;

use BB8\Potatoes\ORM\System\Exceptions\InvalidTableNameException;
use PDO;

class SQLQuery
{
    /**
     * Selects data from database table based on conditions.
     *
     * @param string $tableName name of database table
     * @param string $className namespace of class to associate result with
     * @param PDO    $dbHandler connection to database
     * @param  array  [$fields               = array("*")] fields to select from database
     * @param array $where = null          array of conditional where clause
     *
     * @return array of class objects from database
     */
    public static function select($tableName, $className, $dbHandler, $fields = array('*'), $where = null)
    {
        //Build select Query
        $query = static::selectFields($tableName, $fields, $where);

        //Begin Transaction
        $dbHandler->beginTransaction();

        if ($STH = $dbHandler->prepare($query)) {
            $STH->setFetchMode(\PDO::FETCH_CLASS, $className);

            //detect if WHERE clause is needed and run query
            $result = $where === null ? static::fetch($STH) : static::fetch($STH, array_values($where));

            //Commit Transaction
            $dbHandler->commit();
            return $result;
        }
        //Rollback to initial state
        $dbHandler->rollBack();


        // @codeCoverageIgnoreStart
        throw new InvalidTableNameException("no table with the name $tableName in the database");
        // @codeCoverageIgnoreEnd
    }

    /**
     * Inserts a new data into the database
     * @param  string  [$tableName         = null] name of database table
     * @param  PDO     [$dbHandler         = null] database handler
     * @param  Object  [$data              = null] instantiated object to insert into database
     * @return boolean true if transaction completes and false if not
     */
    public static function insert($tableName = null, $dbHandler = null, $data = null)
    {
        //Begin Transaction
        $dbHandler->beginTransaction();

        $data = static::toArray($data);
        $query = static::setInsertFields($tableName, $data);
        $STH = $dbHandler->prepare($query);

        if ($STH->execute(array_values($data))) {
             //Commit Transaction
            $dbHandler->commit();
             return true;
        }

        //Rollback to initial state
        $dbHandler->rollBack();
        return false;
    }

    /**
     * Deletes a record from the database
     * @param  string  [$tableName         = null] name of the database table to delete from
     * @param  PDO     [$dbHandler         = null] database handler
     * @param  array   [$where             = null]     Condition to delete with
     * @return boolean true if transaction completes and false otherwise
     */
    public static function delete($tableName = null, $dbHandler = null, $where = null)
    {
        //Begin Transaction
        $dbHandler->beginTransaction();

        //Build query
        $query = static::addWhereClause("DELETE FROM $tableName ", $where);
        //Prepare query
        $STH = $dbHandler->prepare($query);

        //Execute and check if completes
        if ($STH->execute(array_values($where))) {
            //Commit Transaction
            $dbHandler->commit();
            return true;
        }
        //Rollback to initial state
        $dbHandler->rollBack();
        return false;
    }

    public static function update($tableName, $dbHandler, $data)
    {
         //Begin Transaction
        $dbHandler->beginTransaction();

        //Convert object to array
        $data = static::toArray($data);

        //Extract id
        $tableID = $data['id'];
        unset($data['id']);

        //Build query
        $query = static::setUpdateFields($tableName, $data, $tableID);

        //Prepare Query
        $STH = $dbHandler->prepare($query);

        //Execute and check if transaction completes
        if ($STH->execute(array_values($data))) {
            //Execution passed: Commit Transaction
            $dbHandler->commit();
            return true;
        }

        //Execution failed: Rollback to initial state
        $dbHandler->rollBack();
        return false;

    }

    /**
     * Builds UPDATE Query
     * @param  string  $tableName name of database table to build query for
     * @param  array   $data      array of properties to update in Database
     * @param  integer $tableID   Table ID of field to update
     * @return string  of built query
     */
    private static function setUpdateFields($tableName, $data, $tableID)
    {
        //Set initial query fragment
        $query = "UPDATE $tableName SET ";

        //Extract Column names
        $fields = array_keys($data);

        //Iterate through column names
        foreach ($fields as $key) {
            //Create placeholders for binding
            $query .= $key.' = ?, ';
        }

        //Remove trailing space and comma, build by adding WHERE clause
        $query = trim($query, ' ,').' WHERE id = '.$tableID;

        //Return result
        return $query;
    }

    /**
     * Builds Insert Query
     * @param  string $tableName name of table to make insert to
     * @param  array  $data      data to insert to database
     * @return string of built query
     */
    private static function setInsertFields($tableName, $data)
    {
        //Extract array keys and convert to string with comma as delimiter
        $fields = implode(', ', array_keys($data));

        //Create placeholders based on the number of data to insert
        $bindable = implode(', ', array_values(array_fill(0, count($data), '?')));

        //Develop query fragment
        $query = "INSERT INTO $tableName ($fields) VALUES ($bindable) ";

        //Remove trailing space and comma
        $query = trim($query, ' ,');

        //Return build query string
        return $query;
    }

    /**
     * Builds SELECT Query
     * @param  string [$tableName            = null]    database table name to make insert to
     * @param  array  [$fields               = array('*')] fields to select from database table row
     * @param  array  $where                 = null          WHERE conditions clause
     * @return string of built SQL SELECT query
     */
    private static function selectFields($tableName = null, $fields = array('*'), $where = null)
    {
        // @codeCoverageIgnoreStart
        if ($tableName === null) {
            throw new InvalidTableNameException();
        }
        // @codeCoverageIgnoreEnd

        //Convert fields to string
        $fields = implode(',', $fields);

        //Test if to select all fields or to select specific fields
        $fieldsString = $fields === '*' ? '*' : $fields;

        //Build query fragment
        $query = "SELECT $fieldsString FROM $tableName ";


        //Check if EHERE clasuse needed
        $sql = $where === null ? $query : static::addWhereClause($query, $where);

        //Return built SQL string
        return $sql;
    }

    /**
     * Adds WHERE clause to code fragment
     * @param  string $sqlFragment         Partially build SQL statement
     * @param  array  $where               WHERE clause to append
     * @param  string [$condition          = 'AND'] Binding conditions to use AND or OR
     * @return string of built SQL statement
     */
    private static function addWhereClause($sqlFragment, $where, $condition = 'AND')
    {
        //Get columns to select and values to search for
        $columns = array_keys($where);
//        $values     =   array_values($where);

        //Set query string and loop through the columns to generate query string
        $query = '';
        foreach ($columns as $column) {
            $query .= ' WHERE '.$column." = ? $condition";
        }

        //Remove Trailing condition(AND | OR) from the end of query string
        $query = trim($query, " $condition");

        return $sqlFragment.' '.$query;
    }

    /**
     * Runs an execute and fetch query to the database
     * @param  PDOStatment $STH                 prepared PDO statement
     * @param  array       [$bindParams         = null] parameters to bind to the exeucute statement
     * @return array       of fetched data
     */
    private static function fetch($STH, $bindParams = null)
    {
        //Check to see ff binding parameters needed, add if needed
        $bindParams === null ? $STH->execute() : $STH->execute($bindParams);

        //Create empty array to place result
        $result = array();

        //Loop through and place each row into result array
        while ($row = $STH->fetch()) {
            $result[] = $row;
        }

        //Return result
        return $result;
    }

    /**
     * Converts an object to array
     * @param  mixed $object object to convert to array
     * @return array of converted object
     */
    private static function toArray($object)
    {
        return (array) $object;
    }
}
