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

            error_log("Tentando conectar ao banco: $servername, $dbname");
            self::$conn = new \mysqli($servername, $username, $password, $dbname);

            if (self::$conn->connect_error) {
                error_log("Conexão falhou: " . self::$conn->connect_error);
                http_response_code(500);
                die("Erro interno no servidor. Tente novamente mais tarde.");
            }
            error_log("Conexão com banco bem-sucedida");
        }
        return self::$conn;
    }
}