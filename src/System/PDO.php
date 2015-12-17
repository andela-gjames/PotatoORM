<?php

namespace BB8\Potatoes\ORM\System;

class PDO
{
    private $handler;
    private $config;
    private static $instance;

    /**
     * Singleton Class:
     * Constructor reads database config file and  creates connection
     *
     */
    private function __construct()
    {
        //Read config file
        $this->config = parse_ini_file("config.ini");

        //Create connection string
        $dsn = $this->config['dbtype'].":host=localhost;dbname=".$this->config['dbname'];

        try {
            //Estable connection
            $this->handler = new \PDO($dsn, "root", "root");
        } catch (\PDOException $pdo) {
            echo ($pdo->getMessage());
        }
    }

    /**
     * Creates instance of current class
     * @return PDO instace of current class
     */
    public static function getInstance()
    {
        //Check if object has been created
        if (self::$instance == null) {
            //Create new object of current class if no object exist
            self::$instance =  new self();
        }
        return self::$instance;
    }

    /**
     * Returns pdo connection handler
     * @return PDO handler for connecting to database
     */
    public function connect()
    {
        return $this->handler;
    }

    /**
     * closes connection to the database
     */
    public function close()
    {
        $this->$handler = null;
    }
}
