<?php
session_start();

// Destruir a sessão
session_unset();
session_destroy();

// Redirecionar ao login
header("Location: index.html");
exit;
?>