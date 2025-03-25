<?php
date_default_timezone_set('America/Sao_Paulo');

// Configurações do banco de dados
$host = "localhost";
$username = "admEvoGraph";        // Usuário atual, pode ser alterado
$password = "evoGraph123";     // Senha atual, pode ser alterada
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