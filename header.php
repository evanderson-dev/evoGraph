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
$default_photo = './img/employee_photos/default_photo.jpg';
$photo_path = $user['foto'] ? $user['foto'] : $default_photo;
$user['foto'] = file_exists($photo_path) ? $photo_path : $default_photo;
$ext = pathinfo($user['foto'], PATHINFO_EXTENSION);
$square_photo_path = str_replace(".$ext", "_square.$ext", $user['foto']);
$header_photo = file_exists($square_photo_path) ? $square_photo_path : $default_photo;
?>
<header>
    <div class="info-header">
        <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
        <div class="logo">
            <h3>evoGraph</h3>
        </div>
    </div>
    <div class="info-header">
        <i class="fa-solid fa-envelope"></i>
        <i class="fa-solid fa-bell"></i>
        <img src="<?php echo $header_photo; ?>" alt="User" class="user-icon" id="header-photo">
    </div>
</header>