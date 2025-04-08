<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once 'db_connection.php';

header('Content-Type: application/json');

$sql = "SELECT MAX(matricula) as max_matricula FROM alunos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$max_matricula = $row['max_matricula'];
if ($max_matricula === null) {
    $base_number = 1; // Primeiro número se o banco estiver vazio
} else {
    // Extrair o número base removendo os últimos 6 dígitos (data DDMMAA)
    $base_number = (int)substr($max_matricula, 0, -6);
    $base_number++; // Incrementar o número base
}

// Gerar a data atual no formato DDMMAA
$date = new DateTime();
$day = $date->format('d'); // Dia sem zero à esquerda
$month = $date->format('m'); // Mês sem zero à esquerda
$year = substr($date->format('Y'), -2); // Últimos 2 dígitos do ano
$date_suffix = $day . $month . $year; // Ex.: "80425" para 08/04/2025

// Concatenar número base com data
$next_matricula = $base_number . $date_suffix;

echo json_encode(['success' => true, 'matricula' => $next_matricula]);

$stmt->close();
$conn->close();
?>