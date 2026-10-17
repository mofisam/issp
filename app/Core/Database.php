<?php
declare(strict_types=1);

namespace App\Core;

use mysqli;

/**
 * Thin mysqli singleton. Every Model goes through here so there is exactly
 * one connection per request, opened lazily on first query.
 */
class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection === null) {
            $config = require BASE_PATH . '/app/Config/database.php';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            self::$connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            self::$connection->set_charset($config['charset']);
        }

        return self::$connection;
    }
}
