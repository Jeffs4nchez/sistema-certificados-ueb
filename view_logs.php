<?php
// Ver logs de PHP

$logFile = 'C:\\xampp\\php\\logs\\php_error.log';

if (!file_exists($logFile)) {
    // Intentar con la ruta alternativa
    $logFile = 'C:\\xampp\\apache\\logs\\error.log';
}

if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100); // Últimas 100 líneas
    
    echo "<pre style='background: #222; color: #0f0; padding: 20px; font-family: monospace; overflow-x: auto;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "Archivo de log no encontrado: $logFile<br>";
    echo "Rutas probadas:<br>";
    echo "1. C:\\xampp\\php\\logs\\php_error.log<br>";
    echo "2. C:\\xampp\\apache\\logs\\error.log<br>";
}
?>
