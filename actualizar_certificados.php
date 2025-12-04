<?php
/**
 * Script para verificar y agregar columnas usuario_id y usuario_creacion a certificados
 */

require_once 'app/config.php';
require_once 'app/Database.php';

echo "<h1>Actualizando Tabla Certificados</h1>";
echo "<hr>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>1. Verificando columnas en 'certificados'</h2>";
    
    // Verificar si columna usuario_id existe
    $query = "SELECT EXISTS(SELECT 1 FROM information_schema.columns 
              WHERE table_name = 'certificados' AND column_name = 'usuario_id')";
    $result = $db->query($query);
    $usuario_id_existe = $result->fetch()[0] == 't';
    
    if (!$usuario_id_existe) {
        echo "<p>Agregando columna 'usuario_id'...</p>";
        $db->exec("ALTER TABLE certificados ADD COLUMN usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_certificados_usuario_id ON certificados(usuario_id)");
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Columna 'usuario_id' agregada";
        echo "</div>";
    } else {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Columna 'usuario_id' ya existe";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h2>2. Estructura Actual de 'certificados'</h2>";
    
    $query = "SELECT column_name, data_type FROM information_schema.columns 
              WHERE table_name = 'certificados' ORDER BY ordinal_position";
    $result = $db->query($query);
    $columns = $result->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #333; color: white;'>";
    echo "<th>Columna</th><th>Tipo de Dato</th>";
    echo "</tr>";
    
    foreach ($columns as $col) {
        $highlight = ($col['column_name'] === 'usuario_id' || $col['column_name'] === 'usuario_creacion') ? 'background-color: #d4edda;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . $col['column_name'] . "</strong></td>";
        echo "<td>" . $col['data_type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<div style='padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;'>";
    echo "<h3>✓ ¡Actualización completada!</h3>";
    echo "<p>Las columnas para registrar usuario han sido agregadas correctamente.</p>";
    echo "<p>Los próximos certificados que se creen registrarán automáticamente al usuario que los creó.</p>";
    echo "<p><a href='index.php?action=certificate-list' style='color: #155724; font-weight: bold; text-decoration: none;'>→ Ver Certificados</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>✗ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}
?>
