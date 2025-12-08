# ✅ FLUJO DE SINCRONIZACIÓN - SIN TRIGGERS

## 🎯 Lógica en PHP (Controllers y Models)

**Fecha:** 7 de Diciembre 2025  
**Sistema:** Gestión de Certificados - UEB Finanzas  
**Enfoque:** Sincronización manual en PHP

---

## 📊 FLUJO COMPLETO

### 1️⃣ **CREAR CERTIFICADO CON ITEMS**

```
Usuario crea certificado con 3 items
      ↓
certificados: INSERT
  - numero_certificado = "CERT-123"
  - monto_total = SUM(montos items) = 2500
  - total_pendiente = monto_total = 2500
      ↓
For each item:
  detalle_certificados: INSERT
    - certificado_id = 1
    - monto = 1000 (Item 1)
    - cantidad_liquidacion = 0 (inicial)
    - cantidad_pendiente = monto - 0 = 1000
    - codigo_completo = "01 00 000 001 001 0200 510601"
      ↓
    presupuesto_items: UPDATE (por codigo_completo)
      - col4 = col4 + 1000
      - saldo_disponible = col3 - col4
      
    (Repetir para Item 2 y 3)
      ↓
✅ RESULTADO:
   - certificados: monto_total = 2500, total_pendiente = 2500
   - detalle_certificados: 3 items con monto y cantidad_pendiente
   - presupuesto_items: col4 += 2500 (suma de todos los montos)
```

### 2️⃣ **LIQUIDAR UN ITEM (Pago Parcial)**

```
Usuario liquida Item 1: paga 500 de 1000
      ↓
detalle_certificados: UPDATE Item 1
  - cantidad_liquidacion = 500
  - cantidad_pendiente = monto - liquidacion = 1000 - 500 = 500
      ↓
✅ RESULTADO:
   - Item 1: cantidad_liquidacion = 500, cantidad_pendiente = 500
   - presupuesto_items: col4 = 2500 (SIN CAMBIOS, sigue siendo lo certificado)
   - Usuario ve: "Certificado $2500, Pagado $500, Pendiente $2000"
```

### 3️⃣ **MODIFICAR MONTO DE UN ITEM**

```
Usuario cambia Item 1 de $1000 a $1500
      ↓
detalle_certificados: UPDATE Item 1
  - monto = 1500 (anterior 1000)
  - cantidad_pendiente = 1500 - cantidad_liquidacion
  - cantidad_liquidacion = se mantiene igual (500)
  - cantidad_pendiente = 1500 - 500 = 1000
      ↓
presupuesto_items: UPDATE
  - col4 = col4 + diferencia = 2500 + (1500-1000) = 3000
  - saldo_disponible = col3 - 3000
      ↓
certificados: UPDATE
  - monto_total = SUM(items) = 3000
  - total_pendiente = monto_total = 3000 (se recalcula)
      ↓
✅ RESULTADO:
   - Item 1: monto = 1500, liquidado = 500, pendiente = 1000
   - presupuesto_items: col4 = 3000
   - certificado: monto_total = 3000, total_pendiente = 3000
```

### 4️⃣ **ELIMINAR UN ITEM**

```
Usuario elimina Item 1 ($1500)
      ↓
detalle_certificados: DELETE Item 1
      ↓
presupuesto_items: UPDATE
  - col4 = col4 - 1500 = 3000 - 1500 = 1500
  - saldo_disponible = col3 - 1500
      ↓
certificados: UPDATE
  - monto_total = SUM(items restantes) = 1500
  - total_pendiente = monto_total = 1500
      ↓
✅ RESULTADO:
   - certificados: monto_total = 1500, total_pendiente = 1500
   - presupuesto_items: col4 = 1500
   - Item 1 eliminado
```

---

## 🔧 IMPLEMENTACIÓN EN CÓDIGO

### **CertificateController.php - createAction()**

```php
// 1. Calcular monto total
$montoTotal = 0;
foreach ($items as $item) {
    $montoTotal += floatval($item['monto'] ?? 0);
}
$certificateData['monto_total'] = $montoTotal;

// 2. Crear certificado
$certificateId = $this->certificateModel->createCertificate($certificateData);

// 3. Crear cada item
foreach ($items as $item) {
    $detailData = [ ... ];
    $this->certificateModel->createDetail($detailData);  // Actualiza presupuesto_items
}

// 4. Actualizar total_pendiente = monto_total
$db = Database::getInstance()->getConnection();
$stmtUpdate = $db->prepare("
    UPDATE certificados 
    SET total_pendiente = monto_total 
    WHERE id = ?
");
$stmtUpdate->execute([$certificateId]);
```

### **Certificate.php - createDetail()**

```php
public function createDetail($data) {
    $monto = (float)($data['monto'] ?? 0);
    $codigoCompleto = (string)($data['codigo_completo'] ?? '');
    
    // 1. Insertar en detalle_certificados
    $stmt = $this->db->prepare("
        INSERT INTO detalle_certificados (
            certificado_id, ..., monto, codigo_completo,
            cantidad_pendiente, cantidad_liquidacion, ...
        ) VALUES (?, ?, ?, ?, ..., ?, 0, ...)
    ");
    $stmt->execute([ ..., $monto, $codigoCompleto, $monto, ... ]);
    
    // 2. Actualizar presupuesto_items
    $stmtPresupuesto = $this->db->prepare("
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) + ?,
            saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + ?),
            fecha_actualizacion = NOW()
        WHERE codigo_completo = ?
    ");
    $stmtPresupuesto->execute([$monto, $monto, $codigoCompleto]);
    
    return $this->db->lastInsertId();
}
```

### **Certificate.php - updateDetailLiquidacion()**

```php
public function updateDetailLiquidacion($id, $cantidadLiquidacion) {
    // 1. Obtener el monto del item
    $stmtGet = $this->db->prepare("SELECT monto FROM detalle_certificados WHERE id = ?");
    $stmtGet->execute([$id]);
    $item = $stmtGet->fetch();
    
    // 2. Calcular cantidad_pendiente
    $cantidadPendiente = $item['monto'] - $cantidadLiquidacion;
    
    // 3. Actualizar item
    $stmt = $this->db->prepare("
        UPDATE detalle_certificados 
        SET 
            cantidad_liquidacion = ?,
            cantidad_pendiente = ?,
            fecha_actualizacion = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$cantidadLiquidacion, $cantidadPendiente, $id]);
}
```

---

## 📋 CAMPOS EN CADA TABLA

### **certificados**
```sql
- id                  (PK)
- numero_certificado  
- monto_total         ← SUMA de todos los montos en detalle_certificados
- total_pendiente     ← = monto_total (sin liquidar nada)
- total_liquidado     ← SUMA de cantidad_liquidacion
- usuario_creacion
- ... (otros campos)
```

### **detalle_certificados**
```sql
- id                        (PK)
- certificado_id            (FK)
- codigo_completo           ← Llave para sincronizar con presupuesto
- monto                     ← El monto certificado del item
- cantidad_liquidacion      ← Lo que se ha pagado (0 inicial)
- cantidad_pendiente        ← = monto - cantidad_liquidacion
- ... (otros campos: programa, subprograma, etc.)
```

### **presupuesto_items**
```sql
- id                  (PK)
- codigo_completo     ← Llave para vincular con detalle_certificados
- col1                ← Codificado (del presupuesto)
- col3                ← Disponible Inicial
- col4                ← Total Certificado (SUMA de montos)
- col8                ← Disponible después de certificar = col1 - col4
- saldo_disponible    ← = col3 - col4
```

---

## ✅ GARANTÍAS

✅ **Consistencia:** Los montos se actualizan en tiempo real  
✅ **Integridad:** Cada operación actualiza todas las tablas involucradas  
✅ **Auditoría:** Todos los campos tienen fecha_actualizacion  
✅ **Control:** Lógica visible en PHP, fácil de debuggear  

---

## 🧪 EJEMPLO PASO A PASO

```
1. Crear Certificado CERT-001
   certificados: INSERT monto_total=2500, total_pendiente=2500
   
2. Item 1: $1000 código "01 00 000 001 001 0200 510601"
   detalle_certificados: INSERT monto=1000, cantidad_pendiente=1000
   presupuesto_items: col4 += 1000 (ahora 1000)
   
3. Item 2: $800 código "01 00 000 001 002 0200 510601"
   detalle_certificados: INSERT monto=800, cantidad_pendiente=800
   presupuesto_items: col4 += 800 (ahora 1800)
   
4. Item 3: $700 código "01 00 000 001 003 0200 510601"
   detalle_certificados: INSERT monto=700, cantidad_pendiente=700
   presupuesto_items: col4 += 700 (ahora 2500)
   
5. Liquidar Item 1: $500
   detalle_certificados: UPDATE cantidad_liquidacion=500, cantidad_pendiente=500
   (presupuesto_items NO cambia, col4 sigue siendo 2500)
   
6. Modificar Item 1: $1500 (aumentar de 1000)
   detalle_certificados: UPDATE monto=1500, cantidad_pendiente=1000
   presupuesto_items: col4 = 2500 + (1500-1000) = 3000
   certificados: UPDATE monto_total=3000, total_pendiente=3000
   
7. Eliminar Item 3: $700
   detalle_certificados: DELETE
   presupuesto_items: col4 = 3000 - 700 = 2300
   certificados: UPDATE monto_total=2300, total_pendiente=2300
```

**Estado Final:**
- Certificado: monto_total=2300, total_pendiente=2300
- Item 1: monto=1500, liquidado=500, pendiente=1000
- Item 2: monto=800, liquidado=0, pendiente=800
- Presupuestos: col4=2300 total

---

**Creado por:** Sistema Automático  
**Última actualización:** 7 de Diciembre 2025
