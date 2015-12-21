<?php
namespace BB8\Tests\Mocks;

use BB8\Potatoes\ORM\System\BaseModel;

class BaseModelStub extends BaseModel
{
    protected function initialize()
    {
        static::$classNS        =   get_called_class();
//        $pdo = new \PDO("sqlite::memory:");
        $pdo = new \PDO("sqlite:".__DIR__."/Data/database.sqlite");
        $pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        static::$DBH    =   $pdo;
    }

    public static function setUpDB()
    {
        $DBH = new \PDO("sqlite:".__DIR__."/Data/database.sqlite");
        $DBH->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $DBH->exec("DROP TABLE users");
        $DBH->exec(
            'CREATE TABLE IF NOT EXISTS "users"  (
                "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
                "full_name" VARCHAR,
                "description" TEXT,
                "token" INTEGER,
                UNIQUE("id"));'
        );

        $DBH->exec('INSERT OR IGNORE INTO "users" VALUES(1,"Hedy Copeland","montes, nascetur ridiculus mus. Donec dignissim",3629)');
        $DBH->exec('INSERT OR IGNORE INTO "users" VALUES(2,"Oscar Medina","faucibus ut, nulla. Cras eu tellus eu augue",3246)');
        $DBH->exec('INSERT OR IGNORE INTO "users" VALUES(3,"George James Okpe","eu, placerat eget, venenatis a, magna. Lorem",1779)');
    }

}
