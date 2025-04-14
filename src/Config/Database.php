<?php
namespace EvoGraph\Config;

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            $servername = "localhost";
            $username = "admEvoGraph";
            $password = "evoGraph123";
            $dbname = "evograph_db";

            self::$conn = new mysqli($servername, $username, $password, $dbname);

            if (self::$conn->connect_error) {
                die("Conexão falhou: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
}