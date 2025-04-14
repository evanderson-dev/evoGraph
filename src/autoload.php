<?php
spl_autoload_register(function ($class) {
    $prefix = 'EvoGraph\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        error_log("Carregando classe: $file");
        require $file;
    } else {
        error_log("Classe não encontrada: $file");
    }
});