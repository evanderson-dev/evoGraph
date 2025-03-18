<?php
$senha = "123456";
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Hash gerado: " . $hash;
?>