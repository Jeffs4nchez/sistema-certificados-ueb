# ✅ Validación de Monto Codificado - Sistema de Certificados

## Resumen de Cambios

Se ha implementado un sistema de validación que **previene crear certificados con montos que excedan el monto codificado** en el presupuesto.

---

## 🔧 Cambios Realizados

### 1️⃣ **Modelo: CertificateItem.php**
**Archivo:** `app/models/CertificateItem.php`

Nuevo método agregado:
```php
getMontoCoificado($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion, $cod_item)
```

**Función:** 
- Obtiene el `col3` (monto codificado) de la tabla `presupuesto_items`
- Busca basándose en los códigos del item
- Retorna el monto codificado o 0 si no encuentra

---

### 2️⃣ **Controlador: CertificateController.php**
**Archivo:** `app/controllers/CertificateController.php`

Validación agregada en `createAction()`:
- **ANTES** de crear el certificado, valida cada item
- Compara el monto ingresado vs monto codificado
- Si monto ingresado **>** monto codificado → Lanza excepción
- Si monto ingresado **≤** monto codificado → Permite continuar

**Mensaje de error:** 
```
❌ No se puede crear el certificado:
Item #1: Monto ingresado ($5,000.00) excede el monto codificado ($4,000.00)
```

---

### 3️⃣ **Controlador API: APICertificateController.php**
**Archivo:** `app/controllers/APICertificateController.php`

Nuevo endpoint AJAX:
```
GET ?action=api-certificate&action-api=get-monto-codicado
    &cod_programa=01
    &cod_subprograma=01
    &cod_proyecto=01
    &cod_actividad=01
    &cod_fuente=10
    &cod_ubicacion=01
    &cod_item=01
```

**Respuesta JSON:**
```json
{
  "success": true,
  "data": {
    "monto_codificado": 10000.00,
    "formateado": "10,000.00"
  }
}
```

---

### 4️⃣ **Vista: certificate/form.php**
**Archivo:** `app/views/certificate/form.php`

Validación frontend en evento `addItemBtn`:
- Llama al API para obtener monto codificado
- Valida ANTES de agregar el item a la tabla
- Muestra alerta si el monto excede:

```
❌ ERROR: El monto ingresado ($5,000.00) EXCEDE el monto codificado ($4,000.00)

No se puede agregar este item.
```

- Si es igual o menor, permite agregar el item
- Si hay error en AJAX, continúa (degradación elegante)

---

## 📋 Flujo de Validación

```
1. Usuario ingresa monto en formulario
   ↓
2. Usuario hace clic en "Agregar"
   ↓
3. Frontend valida:
   ├─ ¿Monto ingresado > Monto codificado?
   │  └─ SÍ → Muestra alerta ❌ (No agrega)
   │  └─ NO → Continúa
   ↓
4. Item se agrega a tabla (frontend)
   ↓
5. Usuario hace clic en "Guardar Certificado"
   ↓
6. Backend valida:
   ├─ ¿Monto ingresado > Monto codificado?
   │  └─ SÍ → Error 500 + mensaje ❌
   │  └─ NO → Crea certificado ✅
   ↓
7. Certificado creado exitosamente
```

---

## 🎯 Comportamiento

| Situación | Frontend | Backend | Resultado |
|-----------|----------|---------|-----------|
| Monto < Codificado | ✅ Permite agregar | ✅ Permite guardar | ✅ Certificado creado |
| Monto = Codificado | ✅ Permite agregar | ✅ Permite guardar | ✅ Certificado creado |
| Monto > Codificado | ❌ Alerta + No agrega | ❌ Error + No guarda | ❌ Certificado bloqueado |

---

## 🧪 Ejemplos de Prueba

### Caso 1: Monto válido (igual al codificado)
```
Codificado: $10,000.00
Ingresado: $10,000.00
Resultado: ✅ Certificado creado
```

### Caso 2: Monto válido (menor al codificado)
```
Codificado: $10,000.00
Ingresado: $8,500.00
Resultado: ✅ Certificado creado
```

### Caso 3: Monto inválido (mayor al codificado)
```
Codificado: $10,000.00
Ingresado: $12,000.00
Resultado: ❌ Alert en frontend, no agrega el item
```

---

## 📝 Notas Técnicas

- **Tabla de presupuesto:** `presupuesto_items`
- **Columna de monto codificado:** `col3`
- **Búsqueda basada en:** Códigos de programa, subprograma, proyecto, actividad, fuente, ubicación, item
- **Validación en dos capas:** Frontend (UX) + Backend (Seguridad)
- **Manejo de errores:** Mensajes claros y específicos

---

## ✨ Beneficios

✅ Previene crear certificados con montos excedidos  
✅ Validación en tiempo real (frontend)  
✅ Validación de seguridad (backend)  
✅ Mensajes claros al usuario  
✅ No bloquea si el API falla (degradación)  
✅ Mejora la integridad de datos  

---

## 📞 Soporte

Si hay problemas con la validación, revisar:
1. Logs en: `error_log()` del servidor
2. Console del navegador (F12 → Console)
3. Que `presupuesto_items` esté poblada con datos
4. Que los códigos sean correctos

