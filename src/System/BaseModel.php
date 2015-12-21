<?php

namespace BB8\Potatoes\ORM\System;

class BaseModel
{
    protected static $tableName;
    protected static $classNS;
    protected static $DBH;

    protected static function initialize()
    {
        static::$classNS = get_called_class();
        static::$tableName = static::getTableName(static::$classNS);

        $pdo = PDOConnection::getInstance();
        static::$DBH = $pdo->connect();
    }

    public static function getAll()
    {
        static::initialize();
        $result = SQLQuery::select(static::$tableName, static::$classNS, static::$DBH);

        return $result;
    }

    public static function find($tableID)
    {
        static::initialize();
        $result = SQLQuery::select(
            static::$tableName,
            static::$classNS,
            static::$DBH,
            ['*'],
            ['id' => $tableID]
        );

        return array_shift($result);
    }

    public static function selectWhere($where, $fields = ['*'])
    {
        static::initialize();
        $result = SQLQuery::select(static::$tableName, static::$classNS, static::$DBH, $fields, $where);

        return $result;
    }

    public static function destroy($tableID)
    {
        static::initialize();
        $result = SQLQuery::delete(static::$tableName, static::$DBH, ['id' => $tableID]);

        return $result;
    }

    public function save()
    {
        static::initialize();

        if (isset($this->id)) {
            $result = SQLQuery::update(static::$tableName, static::$DBH, $this);
        } else {
            $result = SQLQuery::insert(static::$tableName, static::$DBH, $this);
        }

        return $result;
    }

    final private static function getTableName($namespace)
    {
        $classVariables = get_class_vars($namespace)['tableName'];
        $name = $classVariables ? strtolower($classVariables) : strtolower($classVariables);
        $tableName = $name ? strtolower($name) : strtolower($name);

        return $tableName;
    }
}
