<?php
require_once 'app/Database.php';
$db = Database::getInstance()->getConnection();

echo "VERIFICANDO RELACIONES EN LA BD\n";
echo "================================\n\n";

// 1. Actividades con sus Fuentes
echo "[1] ACTIVIDADES → FUENTES\n";
echo "========================\n";
$stmt = $db->query('
    SELECT a.id, a.codigo, a.descripcion, COUNT(DISTINCT f.id) as num_fuentes
    FROM actividades a
    LEFT JOIN fuentes_financiamiento f ON a.id = f.actividad_id
    GROUP BY a.id, a.codigo, a.descripcion
    HAVING COUNT(DISTINCT f.id) > 0
    ORDER BY a.id
');
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($actividades as $act) {
    echo "\nActividad {$act['codigo']}: {$act['descripcion']}\n";
    echo "  Fuentes: {$act['num_fuentes']}\n";
    
    // Mostrar fuentes de esta actividad
    $stmt2 = $db->prepare('
        SELECT id, codigo, descripcion FROM fuentes_financiamiento 
        WHERE actividad_id = ? ORDER BY codigo
    ');
    $stmt2->execute([$act['id']]);
    $fuentes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fuentes as $fte) {
        echo "    → Fuente: {$fte['codigo']} - {$fte['descripcion']}\n";
        
        // Contar ubicaciones de esta fuente
        $stmt3 = $db->prepare('SELECT COUNT(*) as count FROM ubicaciones WHERE fuente_id = ?');
        $stmt3->execute([$fte['id']]);
        $ubgCount = $stmt3->fetch(PDO::FETCH_ASSOC)['count'];
        echo "       Ubicaciones: $ubgCount\n";
        
        // Mostrar ubicaciones
        $stmt3 = $db->prepare('SELECT id, codigo FROM ubicaciones WHERE fuente_id = ?');
        $stmt3->execute([$fte['id']]);
        $ubicaciones = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ubicaciones as $ubg) {
            // Contar items de esta ubicación
            $stmt4 = $db->prepare('SELECT COUNT(*) as count FROM items WHERE ubicacion_id = ?');
            $stmt4->execute([$ubg['id']]);
            $itemCount = $stmt4->fetch(PDO::FETCH_ASSOC)['count'];
            echo "         └─ Ubicación: {$ubg['codigo']} → Items: $itemCount\n";
        }
    }
}

if (empty($actividades)) {
    echo "No hay actividades con fuentes\n";
}

// 2. Resumen de estructura
echo "\n\n[2] RESUMEN DE ESTRUCTURA\n";
echo "=========================\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM actividades WHERE id IN (SELECT DISTINCT actividad_id FROM fuentes_financiamiento)');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Actividades con Fuentes: {$result['count']}\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM fuentes_financiamiento');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total Fuentes: {$result['count']}\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM ubicaciones');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total Ubicaciones: {$result['count']}\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM items');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total Items: {$result['count']}\n";

// Advertencia explícita de jerarquía
if ($totalFuentes < $actividadesConFuente) {
    echo "\nADVERTENCIA: Hay menos fuentes ($totalFuentes) que actividades con fuente ($actividadesConFuente). Esto puede romper la jerarquía.\n";
}
if ($totalUbicaciones < $actividadesConFuente) {
    echo "ADVERTENCIA: Hay menos ubicaciones ($totalUbicaciones) que actividades con fuente ($actividadesConFuente). Esto puede romper la jerarquía.\n";
}

// 3. Verificar si hay Ubicaciones sin Fuente
echo "\n\n[3] VALIDACIÓN DE INTEGRIDAD\n";
echo "============================\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM ubicaciones WHERE fuente_id IS NULL OR fuente_id = 0');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$orphanUbg = $result['count'];
echo "Ubicaciones sin Fuente: $orphanUbg\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM items WHERE ubicacion_id IS NULL OR ubicacion_id = 0');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$orphanItems = $result['count'];
echo "Items sin Ubicación: $orphanItems\n";

$stmt = $db->query('SELECT COUNT(*) as count FROM fuentes_financiamiento WHERE actividad_id IS NULL OR actividad_id = 0');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$orphanFte = $result['count'];
echo "Fuentes sin Actividad: $orphanFte\n";

if ($orphanUbg == 0 && $orphanItems == 0 && $orphanFte == 0) {
    echo "\n✓ Integridad de relaciones: OK\n";
} else {
    echo "\n✗ Hay datos huérfanos sin relación\n";
}
?>
