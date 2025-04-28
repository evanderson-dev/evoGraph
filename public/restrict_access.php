<?php
session_start();

function restrict_access($allowed_cargos, $redirect = 'index.php') {
    global $cargo, $funcionario_id;

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["cargo"]) || !in_array($_SESSION["cargo"], $allowed_cargos)) {
        header("Location: $redirect");
        exit;
    }

    $cargo = $_SESSION["cargo"];
    $funcionario_id = $_SESSION["funcionario_id"];
}
?>