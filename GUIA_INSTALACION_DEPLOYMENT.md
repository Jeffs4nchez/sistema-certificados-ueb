# 🚀 Guía de Instalación y Deployment - Edición de Montos

## ✅ Requisitos Previos

- ✅ PHP 8.0 o superior
- ✅ MySQL 5.7+ o PostgreSQL 12+
- ✅ Bootstrap 5 (ya en proyecto)
- ✅ Tablas de base de datos existentes:
  - `certificados`
  - `detalle_certificados`
  - `presupuesto_items`
  - `liquidaciones`

---

## 📋 Cambios Incluidos

### Archivos Modificados (No Borrados)
```
✅ app/views/certificate/list.php
   ├─ loadEditModalItems()
   ├─ updateEditTotal()
   ├─ saveEditCertificate()
   └─ loadEditModalItems() - nueva versión

✅ app/models/Certificate.php
   └─ updateItemMonto() - NUEVO MÉTODO

✅ app/controllers/CertificateController.php
   └─ updateAction() - MEJORADO
```

### Archivos Nuevos de Documentación
```
📄 EDICION_MONTOS_CERTIFICADOS.md
📄 GUIA_EDICION_MONTOS.md
📄 DOCUMENTACION_TECNICA_EDICION_MONTOS.md
📄 PLAN_PRUEBAS_EDICION_MONTOS.md
📄 RESUMEN_VISUAL_EDICION_MONTOS.md
📄 GUIA_INSTALACION_DEPLOYMENT.md (este archivo)
```

---

## 🔧 Instalación

### Opción 1: Reemplazar Archivos (Recomendado)

**Paso 1**: Hacer backup de archivos actuales
```bash
# En tu servidor
cd /xampp/htdocs/programas/certificados-sistema

# Crear backup
cp -r app/views/certificate/list.php app/views/certificate/list.php.backup
cp -r app/models/Certificate.php app/models/Certificate.php.backup
cp -r app/controllers/CertificateController.php app/controllers/CertificateController.php.backup
```

**Paso 2**: Reemplazar archivos
```bash
# Copiar archivos modificados del repositorio
# (Asumiendo que los tengas en una carpeta local)

cp ./updates/list.php ./app/views/certificate/
cp ./updates/Certificate.php ./app/models/
cp ./updates/CertificateController.php ./app/controllers/
```

**Paso 3**: Verificar cambios
```bash
# Verificar que no haya errores de sintaxis
php -l app/views/certificate/list.php
php -l app/models/Certificate.php
php -l app/controllers/CertificateController.php
```

**Paso 4**: Limpiar caché (si existe)
```bash
# Si hay carpeta de cache
rm -rf app/cache/*
rm -rf tmp/*
```

---

## 🗄️ Base de Datos

### ❌ NO se necesita cambios en la BD

Los cambios son **SOLO en lógica de aplicación**, **NO en estructura**.

Las tablas ya tienen todas las columnas necesarias:
- `detalle_certificados.monto` ✅
- `detalle_certificados.cantidad_liquidacion` ✅
- `detalle_certificados.cantidad_pendiente` ✅
- `detalle_certificados.codigo_completo` ✅
- `presupuesto_items.col4` ✅
- `presupuesto_items.saldo_disponible` ✅
- `certificados.monto_total` ✅
- `certificados.total_pendiente` ✅

---

## 🧪 Verificación Post-Instalación

### Test 1: Verificar Sintaxis PHP
```bash
php -l app/views/certificate/list.php
# Resultado esperado: No syntax errors detected
```

### Test 2: Verificar en Navegador
1. Ir a `http://localhost/programas/certificados-sistema`
2. Loguear como admin
3. Ir a Certificados
4. Hacer clic en botón [✏️] Editar

**Resultado esperado**:
- ✅ Modal se abre sin errores JavaScript
- ✅ Tabla de items tiene columna "Monto (Editable)"
- ✅ Inputs de monto son editables

### Test 3: Verificar Funcionalidad
1. Cambiar un monto en el modal
2. Verificar que total se recalcula en tiempo real
3. Hacer clic en "Guardar Cambios"
4. Verificar que BD se actualiza

```sql
-- Verificar en BD después de cambios
SELECT id, monto, cantidad_pendiente FROM detalle_certificados WHERE id = [item_id];
```

### Test 4: Revisar Logs
```bash
# En Windows (XAMPP)
tail -n 50 xampp/apache/logs/error.log

# O en Linux
tail -f /var/log/apache2/error.log

# Buscar líneas con "UPDATE ITEM MONTO"
grep "UPDATE ITEM MONTO" error.log
```

---

## 📊 Monitoreo Post-Instalación

### Los primeros días

Mantén un ojo en:
1. **Error Log**: ¿Hay excepciones?
2. **Performance**: ¿Tiempo de respuesta normal?
3. **Datos**: ¿Se guardan correctamente?

### Comandos de monitoreo

```bash
# Ver últimos errores
tail -n 100 xampp/apache/logs/error.log | grep -i "error\|exception"

# Ver logs de PHP
tail -f xampp/apache/logs/access.log

# Verificar permisos de archivos
ls -la app/views/certificate/list.php
ls -la app/models/Certificate.php
ls -la app/controllers/CertificateController.php
```

---

## 🆘 Solución de Problemas

### Problema 1: "Parse error" en list.php
**Síntomas**: Página blanca, error en consola del servidor

**Soluciones**:
```bash
# 1. Verificar sintaxis
php -l app/views/certificate/list.php

# 2. Si hay error, revertir backup
cp app/views/certificate/list.php.backup app/views/certificate/list.php

# 3. Descargar archivos nuevamente y verificar encoding
file app/views/certificate/list.php  # Debe ser UTF-8
```

### Problema 2: Modal no abre
**Síntomas**: Clic en [✏️] no hace nada

**Soluciones**:
```bash
# 1. Revisar consola JavaScript (F12)
# 2. Ver Network tab - ¿API devuelve error?
# 3. Revisar que sea admin loguead
# 4. Limpiar caché del navegador (Ctrl+Shift+Del)
```

### Problema 3: "CORS error"
**Síntomas**: Error en consola sobre CORS

**Soluciones**:
```php
// En app/controllers/CertificateController.php
header('Access-Control-Allow-Origin: *');  // Solo dev
// En producción, especificar dominio
header('Access-Control-Allow-Origin: https://tudominio.com');
```

### Problema 4: Montos no se guardan
**Síntomas**: Modal cierra pero BD no cambia

**Soluciones**:
```bash
# 1. Verificar permisos de BD
# 2. Revisar logs: grep "UPDATE ITEM MONTO" error.log
# 3. Verificar conexión a BD
# 4. Revisar que el usuario sea admin
```

---

## 🔄 Rollback (Revertir Cambios)

Si necesitas volver a la versión anterior:

```bash
# Restaurar desde backup
cp app/views/certificate/list.php.backup app/views/certificate/list.php
cp app/models/Certificate.php.backup app/models/Certificate.php
cp app/controllers/CertificateController.php.backup app/controllers/CertificateController.php

# Limpiar caché
rm -rf app/cache/*
```

**Nota**: Los datos en BD NO se verán afectados. Los cambios solo son en el código.

---

## 📱 Deployment en Producción

### Pre-deployment Checklist

- [ ] Hacer backup completo de BD
- [ ] Hacer backup de archivos PHP
- [ ] Ejecutar pruebas en ambiente de staging
- [ ] Revisar que no haya referencias a ambiente local
- [ ] Verificar permisos de archivos
- [ ] Revisar logs para warnings

### Pasos de Deployment

1. **Activar modo mantenimiento** (si es posible)
   ```bash
   touch app/maintenance.php
   ```

2. **Hacer backup**
   ```bash
   # Base de datos
   mysqldump -u root -p certificados > backup_2025_01_10.sql
   
   # Archivos
   tar -czf certificados-sistema-backup.tar.gz app/
   ```

3. **Copiar archivos nuevos**
   ```bash
   # Vía SCP
   scp -r app/views/certificate/list.php usuario@servidor:/var/www/html/app/views/certificate/
   scp -r app/models/Certificate.php usuario@servidor:/var/www/html/app/models/
   scp -r app/controllers/CertificateController.php usuario@servidor:/var/www/html/app/controllers/
   ```

4. **Verificar instalación**
   ```bash
   # En servidor remoto
   ssh usuario@servidor
   cd /var/www/html
   php -l app/views/certificate/list.php
   ```

5. **Testear en producción**
   - Loguear como admin
   - Editar un certificado
   - Cambiar montos
   - Guardar

6. **Desactivar modo mantenimiento**
   ```bash
   rm app/maintenance.php
   ```

---

## 📈 Performance y Escalabilidad

### Optimizaciones Aplicadas
- ✅ Cálculos eficientes
- ✅ Índices en BD (si existen)
- ✅ JSON ligero en comunicación

### Si tienes 100+ certificados
```sql
-- Crear índices para mejorar velocidad
CREATE INDEX idx_detalle_cert_id ON detalle_certificados(certificado_id);
CREATE INDEX idx_presupuesto_codigo_year ON presupuesto_items(codigo_completo, year);

-- Verificar índices
SHOW INDEX FROM detalle_certificados;
SHOW INDEX FROM presupuesto_items;
```

---

## 📚 Documentación Adicional

Dentro del proyecto tienes:
- 📄 `EDICION_MONTOS_CERTIFICADOS.md` - Implementación técnica
- 📄 `GUIA_EDICION_MONTOS.md` - Guía de usuario
- 📄 `DOCUMENTACION_TECNICA_EDICION_MONTOS.md` - API y métodos
- 📄 `PLAN_PRUEBAS_EDICION_MONTOS.md` - Tests y casos
- 📄 `RESUMEN_VISUAL_EDICION_MONTOS.md` - Interfaz visual

---

## 🤝 Soporte y Actualizaciones

### Reportar Problemas
Si encuentras un error:
1. Revisa el error log
2. Abre issue con:
   - PHP version
   - Navegador
   - Pasos para reproducir
   - Error exacto
   - Logs relevantes

### Actualizar en el Futuro
```bash
# Para actualizar a una nueva versión
git pull origin main
php -l app/views/certificate/list.php
php -l app/models/Certificate.php
# Testear en navegador
```

---

## ✅ Checklist Final

Antes de considerar completada la instalación:

- [ ] Archivos copiados sin errores
- [ ] PHP syntax válida (php -l)
- [ ] Modal se abre correctamente
- [ ] Montos son editables
- [ ] Total se recalcula en tiempo real
- [ ] Cambios se guardan en BD
- [ ] Presupuesto se actualiza
- [ ] Liquidaciones se mantienen
- [ ] Logs sin errores
- [ ] Performance aceptable
- [ ] Usuarios pueden editar
- [ ] Admin solo puede editar

---

## 🎉 ¡Listo!

La instalación está completa. Los usuarios ahora pueden editar montos directamente desde el modal de certificados.

**Tiempo de instalación**: ~5 minutos (sin incluir testing)

**Impacto en usuarios**: Edición de montos más rápida y eficiente ✅

