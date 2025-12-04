# 🐛 FIX: Duplicación de Total Certificado en Dashboard

## Problema Identificado

En el dashboard, el "Total Certificado" mostraba **el doble** del valor correcto:

```
Total Certificado: $10,040
```

Cuando el valor correcto debería ser:

```
Total Certificado: $5,020
```

### Causa Raíz

El método `getTotalsGlobal()` en `Certificate.php` usaba un `LEFT JOIN` entre las tablas `certificados` y `detalle_certificados`:

```php
// ❌ INCORRECTO
$stmt = $this->db->prepare("
    SELECT 
        COALESCE(SUM(dc.cantidad_liquidacion), 0) as total_liquidado,
        COALESCE(SUM(c.monto_total), 0) as total_monto
    FROM certificados c
    LEFT JOIN detalle_certificados dc ON c.id = dc.certificado_id
");
```

**Problema:** Si un certificado tiene 2 ítems en `detalle_certificados`, el `SUM(c.monto_total)` se suma **2 veces** en lugar de una.

### Ejemplo

- Certificado CERT-001 con `monto_total = $5,020`
- Tiene 2 items en `detalle_certificados`
- El LEFT JOIN produce 2 filas
- SUM(c.monto_total) = $5,020 + $5,020 = **$10,040** ❌

---

## Solución Implementada

Separar las queries para evitar el JOIN que causa duplicación:

```php
// ✅ CORRECTO
public function getTotalsGlobal() {
    // Obtener monto_total de certificados (sin duplicar por items)
    $stmt = $this->db->prepare("
        SELECT COALESCE(SUM(monto_total), 0) as total_monto
        FROM certificados
    ");
    $stmt->execute();
    $row = $stmt->fetch();
    $total_monto = $row['total_monto'] ?? 0;
    
    // Obtener total liquidado de detalles
    $stmt = $this->db->prepare("
        SELECT COALESCE(SUM(cantidad_liquidacion), 0) as total_liquidado
        FROM detalle_certificados
    ");
    $stmt->execute();
    $row = $stmt->fetch();
    $total_liquidado = $row['total_liquidado'] ?? 0;
    
    return [
        'total_monto' => $total_monto,
        'total_liquidado' => $total_liquidado
    ];
}
```

### Cambios Realizados

1. **`getTotalsGlobal()`** - Query 1: Suma directa de `monto_total` desde `certificados`
2. **`getTotalsGlobal()`** - Query 2: Suma directa de `cantidad_liquidacion` desde `detalle_certificados`
3. **`getTotalsByOperador()`** - Aplicó la misma corrección para totales por operador

---

## Resultado

✅ **Dashboard ahora muestra los totales correctos:**

| Métrica | Antes (❌) | Después (✅) |
|---------|-----------|-----------|
| Total Certificado | $10,040 | $5,020 |
| CERT-001 Monto | $5,020 | $5,020 |
| Items de CERT-001 | 2 | 2 |

---

## Archivos Modificados

- `app/models/Certificate.php`
  - Método `getTotalsGlobal()` - Líneas ~319-340
  - Método `getTotalsByOperador()` - Líneas ~342-365

## Commit

```
Commit: dc08330
Message: fix: Corregir duplicación de Total Certificado en Dashboard
```

---

## Verificación

Para verificar que funciona correctamente, el método ahora retorna:

```php
getTotalsGlobal() => [
    'total_monto' => 5020.00,      // ✅ Correcto
    'total_liquidado' => 0.00       // ✅ Correcto
]
```

En lugar de:

```php
getTotalsGlobal() => [
    'total_monto' => 10040.00,     // ❌ Duplicado
    'total_liquidado' => 0.00
]
```

---

## Notas Importantes

- Este bug también afectaba a `getTotalsByOperador()` que mostraba el doble de totales para operadores
- El cambio no afecta a ninguna otra funcionalidad
- Las queries ahora son más eficientes al no usar JOIN innecesarios
- El problema solo existía en el modelo `Certificate.php`, no en `PresupuestoItem.php`

