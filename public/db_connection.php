<?php
// Configurações do banco de dados
$host = "localhost";
$username = "admEvoGraph";
$password = "evoGraph@123";
$database = "evograph_db";

// Criar a conexão
$conn = new mysqli($host, $username, $password, $database);

// Verificar a conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Configurar charset (opcional, para evitar problemas com acentos)
$conn->set_charset("utf8");

?>