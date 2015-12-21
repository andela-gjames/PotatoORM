<?php

namespace BB8\Potatoes\ORM\System;

use BB8\Potatoes\ORM\System\Interfaces\IPDO;
use PDO;

class PDOConnection implements IPDO
{
    private $handler;
    private $config;
    private static $instance;

    /**
     * Singleton Class:
     * Constructor reads database config file and  creates connection.
     */
    private function __construct()
    {
        //Read config file
        $this->config = parse_ini_file('config.ini');
        $this->setUp();
    }

    /**
     * Creates instance of current class.
     *
     * @return PDO instace of current class
     */
    public static function getInstance()
    {
        //Check if object has been created
        if (self::$instance == null) {
            //Create new object of current class if no object exist
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns pdo connection handler.
     *
     * @return PDO handler for connecting to database
     */
    public function connect()
    {
        return $this->handler;
    }

    private function setUp()
    {
        try {
            $dbType = $this->config['dbtype'];
            switch ($dbType) {
                case 'sqlite':
                    $dsn = $this->config['dbtype'].'::memory:';
                    $this->handler = new \PDO($dsn);
                    break;
                case 'mysql':
                    $dsn = $this->config['dbtype'].':host=localhost;dbname='.$this->config['dbname'];
                    $this->handler = new \PDO($dsn, 'root', 'root');
                    break;
            }

            if ($this->config['environment'] == 'development') {
                $this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (\PDOException $pdo) {
            //            echo ($pdo->getMessage());
            echo '<pre>';
            echo $pdo->xdebug_message;
            echo '</pre>';
            die();
        }
    }

    /**
     * closes connection to the database.
     */
    public function close()
    {
        $this->$handler = null;
    }
}
