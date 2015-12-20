<?php

namespace BB8\Potatoes\ORM\System;
use BB8\Potatoes\ORM\System\Interfaces\IPDO;
use BB8\Potatoes\ORM\Exceptions\InvalidTableNameException;

class StaticSQLQuery
{
    /**
     * Queries and gets data from the database
     * @param  array [array $fields = ["*"]] databse columns to return
     * @return array of data from the query result
     */
    public static function select(array $fields = ["*"], \PDO $connection = null)
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
    public static function insert($data = null, \PDO $connection = null)
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
     * Selects data from database table based on conditions
     * @param  array [array $data = []] condtions for selecting data
     * @return array result of query
     */
    public static function selectWhere(array $data = [], \PDO $connection = null)
    {
        //Throw Exception if $data is null
        if ( array_filter($data) == null ) {
            throw new \InvalidArgumentException("Provide field to select from table ".self::$tableName);
        }

        //Get columns to select and values to search for
        $columns    = array_keys($data);
        $values    = array_values($data);

        //Set query string and loop through the columns to generate query string
        $query = "";
        foreach ($columns as $column) {
            $query .= " WHERE ".$column." = ? AND" ;
        }

        //Remove Trailing AND from the end of query string
        $query      = trim($query, " AND");

        //Build querystring with SELECT statement
        $queryString    = "SELECT * FROM ".self::$tableName." $query ";

        //Prepare querystring for db access
        $STH = self::$dbHandler->prepare($queryString);

        //If execute pass, set fetch type to type of calling class
        if ($STH->execute($values)) {
            $STH->setFetchMode(\PDO::FETCH_CLASS, self::$className);
            $result = $STH->fetchAll();

            //Return result as class object if result return is one
            //Else return class objects in default array
            if (count($result) == 1) {
                return $result[0];
            }

            return $result;
        }
        return null;
    }

    /**
     * Updates set of field
     * @param  mixed   [$data = null] object of data to update
     * @param  int     $tblId database id of data to update
     * @return boolean if successful or not
     */
    public static function update($data = null, $tblId = null, \PDO $connection = null)
    {
        //Throw Exception if $data or $tblId is null
        if ($data === null || $tblId = null) {
            throw new \InvalidArgumentException("Cannot save null to the database table ".self::$tableName);
        }

        //Remove null and empty elements from array
        $data = array_filter(self::to_array($data));

        //Remove id from data since it does not need to update
        unset($data['id']);

        //Get fields to update and their values to replace with
        $fields     = array_keys($data);
        $values     = array_values($data);


        //Build query
        $query = "UPDATE ".self::$tableName." SET ";
        foreach ($fields as $key) {
            //Create placeholders for binding
            $query .= $key ." = ?, ";
        }
        //Remove trailing comma from query string and build
        $query = trim($query, ' ,')." WHERE id = $tblId";

        try {
            //Prepare query string
            $STH = static::$dbHandler->prepare($query);

            //Execute query string
            return $STH->execute($values);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return false;
    }

    /**
     * Deletes a record from the databse
     * @param  int     $tblId table id of element to delete
     * @return boolean if result is successful or not
     */
    public static function delete($tblId, \PDO $connection = null)
    {
        if ($tblId == null) {
            throw new \InvalidArgumentException("Provide id of row to delete".self::$tableName);
        }

        $query  =   "DELETE FROM ".static::$tableName." WHERE ".static::$tableName.".id= ?";

        try {
            $STH    =   self::$dbHandler->prepare($query);
            $STH->bindParam(1, $tblId);
            return $STH->execute();
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return false;
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
