<?php

namespace BB8\Potatoes\ORM\System;

use PDO;
use BB8\Potatoes\ORM\System\Exceptions\InvalidTableNameException;

class SQLQuery
{
    /**
     * Selects data from database table based on conditions
     * @param  string $tableName             name of database table
     * @param  string $className             namespace of class to associate result with
     * @param  PDO    $dbHandler             connection to database
     * @param  array  [$fields               = array("*")] fields to select from database
     * @param  array  $where                 = null          array of conditional where clause
     * @return array  of class objects from database
     */
    public static function select($tableName, $className, $dbHandler, $fields = array("*"), $where = null)
    {
        $query  =   static::selectFields($tableName, $fields, $where);
        $STH    =   $dbHandler->prepare($query);
        $STH->setFetchMode(\PDO::FETCH_CLASS, $className);

        $result = $where == null ? static::fetch($STH) : static::fetch($STH, array_values($where));

        return $result;
    }

    public static function insert($tableName = null, $dbHandler = null, $data = null)
    {
        $data   =   static::toArray($data);
        $query  =   static::setInsertFields($tableName, $data);
        $STH    =   $dbHandler->prepare($query);

        return !!$STH->execute(array_values($data));
    }

    public static function delete( $tableName = null, $dbHandler = null, $where)
    {
        $query  =   static::addWhereClause("DELETE FROM $tableName ", $where);
        $STH    =   $dbHandler->prepare($query);
        $result =   static::fetch($STH, array_values($where));

        return !!$result;
    }

    public static function update($tableName, $dbHandler, $data)
    {
        $data       =   static::toArray($data);
        $tableID    =   $data["id"];
        unset($data['id']);

        $query = static::setUpdateFields($tableName, $data, $tableID);

        $STH    =   $dbHandler->prepare($query);
        return !!$STH->execute(array_values($data));

    }

    private static function setUpdateFields($tableName, $data, $tableID)
    {
        $query = "UPDATE $tableName SET ";
        $fields = array_keys($data);
        foreach ($fields as $key) {
            //Create placeholders for binding
            $query .= $key ." = ?, ";
        }

        $query = trim($query, ' ,')." WHERE id = ".$tableID;
        return $query;
    }

    private static function setInsertFields($tableName, $data)
    {
        $fields = implode(", ", array_keys($data));
        //Create placeholders based on the number of data to insert
        $bindable   = implode(", ", array_values(array_fill(0, count($data), '?')));

        $query = "INSERT INTO $tableName ($fields) VALUES ($bindable) ";
        $query = trim($query, ' ,');

        return $query;
    }

    private static function selectFields($tableName = null, $fields = array("*"), $where = null)
    {
        if ($tableName == null ) {
            throw new InvalidTableNameException();
        }

        $fields   =   implode(",", $fields);
        $fieldsString =   $fields == "*" ? "*": $fields;

        $query = "SELECT $fieldsString FROM $tableName ";
        $sql = $where == null ? $query : static::addWhereClause($query, $where);
        return $sql;
    }

    private static function addWhereClause($sqlFragment, $where, $condition = 'AND')
    {
        //Get columns to select and values to search for
        $columns    =   array_keys($where);
//        $values     =   array_values($where);

        //Set query string and loop through the columns to generate query string
        $query = "";
        foreach ($columns as $column) {
            $query .= " WHERE ".$column." = ? $condition" ;
        }

        //Remove Trailing condition(AND | OR) from the end of query string
        $query      = trim($query, " $condition");

        return $sqlFragment." ".$query;
    }

    private static function fetch($STH, $bindParams = null)
    {
        $bindParams == null ? $STH->execute() : $STH->execute($bindParams);

        $result = array();
        while ($row = $STH->fetch()) {
            $result[] = $row;
        }

//        if (count($result) == 1) {
//            return $result[0];
//        }

        return $result;
    }

    private static function toArray($object)
    {
        return (array)$object;
    }
}
