<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: index.php");
    exit;
}
require_once 'db_connection.php';
$funcionario_id = $_SESSION["funcionario_id"];
$cargo = $_SESSION["cargo"];
$sql = "SELECT nome, foto FROM funcionarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $funcionario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$default_photo = './assets/img/employee_photos/default_photo.jpg';
$photo_path = $user['foto'] ? $user['foto'] : $default_photo;
$user['foto'] = file_exists($photo_path) ? $photo_path : $default_photo;
$ext = pathinfo($user['foto'], PATHINFO_EXTENSION);
$square_photo_path = str_replace(".$ext", "_square.$ext", $user['foto']);
$header_photo = file_exists($square_photo_path) ? $square_photo_path : $default_photo;
?>