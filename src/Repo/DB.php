<?php
declare(strict_types=1);

namespace SomeSample\Repo;

use PDO;

class DB
{
    protected static PDO|null $pdo = null;

    public function __construct(PDO|null $pdo = null)
    {
        if (!is_null($pdo)) {
            static::$pdo = $pdo;
        }
    }

    public function getPdo(): PDO
    {
        static::$pdo ??= static::getNewPdo();
        return static::$pdo;
    }

    public static function getNewPdo(): PDO
    {
        // ugly short hands.
        $dsn = getenv("DB_DSN");
        $db_user_name = getenv("DB_USER_NAME");
        $db_user_pass = getenv("DB_USER_PASS");

        // このサンプルは強くMysqlとペッグしている実装なので注意
        return new PDO($dsn, $db_user_name, $db_user_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => true
        ]);
    }
}
