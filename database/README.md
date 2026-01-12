# Base de Datos - Sistema de Gestión de Certificados y Liquidaciones

## 📋 Descripción General

Base de datos PostgreSQL para el sistema de gestión de certificados y liquidaciones. Gestiona usuarios, presupuestos, certificados, detalles de certificados, liquidaciones y auditoria completa.

**Fecha de Creación:** 2026-01-12  
**Versión:** 1.0  
**Engine:** PostgreSQL 12+  

---

## 📊 Estructura de Tablas

### 1. **usuarios**
Almacena información de usuarios del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `nombre` | VARCHAR(100) | Nombre del usuario |
| `apellidos` | VARCHAR(100) | Apellidos del usuario |
| `correo_institucional` | VARCHAR(255) UNIQUE | Email institucional (único) |
| `cargo` | VARCHAR(100) | Puesto o cargo |
| `tipo_usuario` | VARCHAR(50) | Tipo: admin, operador, etc. |
| `contraseña` | VARCHAR(255) | Hash de contraseña |
| `estado` | VARCHAR(20) | Estado: activo, inactivo |
| `fecha_creacion` | TIMESTAMP | Fecha de creación |
| `fecha_actualizacion` | TIMESTAMP | Fecha última actualización |
| `es_root` | INTEGER | Flag de usuario root (0/1) |

**Índices:**
- `idx_usuarios_correo` en `correo_institucional`

---

### 2. **presupuesto_items**
Almacena artículos presupuestarios con detalles de ingresos y gastos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `descripciong1-g5` | VARCHAR | Descripciones por nivel presupuestario |
| `codigog1-g5` | VARCHAR(20) | Códigos presupuestarios |
| `col1-col10` | NUMERIC(14,2) | Columnas de montos diversos |
| `col20` | NUMERIC(7,2) | Columna adicional |
| `saldo_disponible` | NUMERIC(14,2) | Saldo disponible |
| `codigo_completo` | VARCHAR(255) | Código presupuestario completo |
| `year` | INTEGER | Año fiscal |
| `fecha_creacion` | TIMESTAMP | Fecha de creación |
| `fecha_actualizacion` | TIMESTAMP | Fecha última actualización |

**Índices:**
- `idx_presupuesto_codigog3` en `codigog3`
- `idx_presupuesto_items_year` en `year`

---

### 3. **estructura_presupuestaria**
Describe la estructura jerárquica del presupuesto (programas, subprogramas, proyectos, actividades).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `cod_programa-cod_nprest` | VARCHAR | Códigos de diferentes niveles |
| `desc_programa-desc_nprest` | VARCHAR | Descripciones de cada nivel |
| `codigo_completo` | VARCHAR(255) | Código jerárquico completo |
| `year` | INTEGER | Año fiscal |

**Niveles Presupuestarios:**
- Programa
- Subprograma
- Proyecto
- Actividad
- Fuente
- Ubicación
- Item
- Organismo
- Naturaleza de Prestación (NPREST)

**Índices:**
- `idx_estructura_year` en `year`

---

### 4. **certificados**
Registra certificados de asignación de fondos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `numero_certificado` | VARCHAR(50) | Número único del certificado (CERT-XXX) |
| `institucion` | VARCHAR(255) | Institución beneficiaria |
| `seccion_memorando` | VARCHAR(255) | Sección del memorando |
| `descripcion` | TEXT | Descripción detallada |
| `fecha_elaboracion` | DATE | Fecha de creación |
| `monto_total` | NUMERIC(15,2) | Monto total del certificado |
| `estado` | VARCHAR(20) | Estado: PENDIENTE, APROBADO, etc. |
| `usuario_creacion` | VARCHAR(255) | Usuario que creó |
| `usuario_id` | INTEGER FK | Referencia a usuarios |
| `unid_ejecutora` | VARCHAR(50) | Unidad ejecutora |
| `unid_desc` | VARCHAR(50) | Descripción unidad ejecutora |
| `clase_registro` | VARCHAR(50) | Clase de registro |
| `clase_gasto` | VARCHAR(50) | Clase de gasto |
| `tipo_doc_respaldo` | VARCHAR(50) | Tipo de documento respaldo |
| `clase_doc_respaldo` | VARCHAR(50) | Clase de documento respaldo |
| `total_liquidado` | NUMERIC | Total liquidado |
| `total_pendiente` | NUMERIC | Total pendiente |
| `year` | INTEGER | Año fiscal |
| `fecha_creacion` | TIMESTAMP | Fecha de creación |
| `fecha_actualizacion` | TIMESTAMP | Fecha última actualización |

**Restricciones:**
- **UNIQUE:** `(numero_certificado, year)` - Permite CERT-001 en múltiples años
- **FOREIGN KEY:** `usuario_id` → `usuarios(id)` ON DELETE SET NULL

**Índices:**
- `idx_certificados_numero` en `numero_certificado`
- `idx_certificados_year` en `year`

---

### 5. **detalle_certificados**
Registra los ítems individuales dentro de cada certificado.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `certificado_id` | INTEGER FK | Referencia al certificado |
| `programa_codigo-naturaleza_codigo` | VARCHAR(50) | Códigos presupuestarios |
| `descripcion_item` | TEXT | Descripción del ítem |
| `monto` | NUMERIC(15,2) | Monto del ítem |
| `codigo_completo` | VARCHAR(30) | Código presupuestario completo |
| `cantidad_liquidacion` | NUMERIC(15,2) | Cantidad liquidada |
| `cantidad_pendiente` | NUMERIC(15,2) | Cantidad pendiente de liquidar |
| `year` | INTEGER | Año fiscal |
| `fecha_creacion` | TIMESTAMP | Fecha de creación |
| `fecha_actualizacion` | TIMESTAMP | Fecha última actualización |

**Restricciones:**
- **FOREIGN KEY:** `certificado_id` → `certificados(id)` ON DELETE CASCADE

**Índices:**
- `idx_detalle_certificado_id` en `certificado_id`
- `idx_detalle_certificados_year` en `year`

---

### 6. **liquidaciones**
Registra las liquidaciones (pagos) realizados contra los certificados.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `detalle_certificado_id` | INTEGER FK | Referencia al detalle |
| `cantidad_liquidacion` | NUMERIC(15,2) | Cantidad liquidada |
| `fecha_liquidacion` | DATE | Fecha de la liquidación |
| `memorando` | TEXT | Memorando asociado |
| `usuario_creacion` | VARCHAR(255) | Usuario que creó |
| `fecha_creacion` | TIMESTAMP | Fecha de creación |
| `fecha_actualizacion` | TIMESTAMP | Fecha última actualización |

**Restricciones:**
- **FOREIGN KEY:** `detalle_certificado_id` → `detalle_certificados(id)` ON DELETE CASCADE

**Índices:**
- `idx_liquidaciones_detalle_id` en `detalle_certificado_id`

---

### 7. **auditoria_liquidaciones**
Registro de auditoría para todos los cambios en liquidaciones.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `liquidacion_id` | INTEGER | ID de liquidación |
| `detalle_certificado_id` | INTEGER | ID del detalle certificado |
| `accion` | VARCHAR(50) | Acción: INSERT, UPDATE, DELETE |
| `cantidad_anterior` | NUMERIC(15,2) | Valor anterior |
| `cantidad_nueva` | NUMERIC(15,2) | Valor nuevo |
| `usuario` | VARCHAR(255) | Usuario que realizó cambio |
| `fecha_cambio` | TIMESTAMP | Fecha del cambio |

**Índices:**
- `idx_auditoria_liquidacion_id` en `liquidacion_id`

---

### 8. **delete_tracking**
Registro de ítems eliminados para auditoría.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `codigo_completo` | VARCHAR(100) | Código del ítem eliminado |
| `created_at` | TIMESTAMP | Fecha de eliminación |

---

### 9. **trigger_log**
Registro de eventos generados por triggers (auditoría).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `trigger_name` | VARCHAR(100) | Nombre del trigger |
| `operacion` | VARCHAR(50) | Tipo de operación |
| `codigo_completo` | VARCHAR(100) | Código del ítem |
| `cantidad_pendiente` | NUMERIC | Cantidad pendiente |
| `resultado` | VARCHAR(500) | Resultado de la operación |
| `fecha_evento` | TIMESTAMP | Fecha del evento |

---

### 10. **trigger_logs**
Registro adicional de eventos de triggers para auditoría detallada.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL PRIMARY KEY | Identificador único |
| `trigger_name` | VARCHAR(100) | Nombre del trigger |
| `action` | VARCHAR(50) | Acción realizada |
| `codigo_completo` | VARCHAR(100) | Código del ítem |
| `monto_amount` | NUMERIC(14,2) | Monto afectado |
| `col4_before` | NUMERIC(14,2) | Valor antes |
| `col4_after` | NUMERIC(14,2) | Valor después |
| `created_at` | TIMESTAMP | Fecha del evento |

---

## 🔄 Relaciones Entre Tablas

```
usuarios
    ↓
    └─→ certificados (usuario_id FK)
            ↓
            └─→ detalle_certificados (certificado_id FK)
                    ↓
                    ├─→ liquidaciones (detalle_certificado_id FK)
                    │       ↓
                    │       └─→ auditoria_liquidaciones
                    │
                    └─→ estructura_presupuestaria (referencia conceptual)

presupuesto_items
    ↓
    └─→ estructura_presupuestaria (relación conceptual por código)
```

---

## 🔐 Funciones de Auditoría

### 1. **tr_liquidaciones_insert()**
Se ejecuta AFTER INSERT en liquidaciones. Registra el nuevo registro en auditoria_liquidaciones.

### 2. **tr_liquidaciones_update()**
Se ejecuta AFTER UPDATE en liquidaciones. Registra cambios en cantidad_liquidacion en auditoria_liquidaciones.

### 3. **tr_liquidaciones_delete()**
Se ejecuta BEFORE DELETE en liquidaciones. Registra el borrado en auditoria_liquidaciones.

---

## 🔔 Triggers Activos

| Nombre | Tabla | Evento | Tipo | Función |
|--------|-------|--------|------|---------|
| `trigger_liquidaciones_insert` | liquidaciones | AFTER INSERT | FOR EACH ROW | `tr_liquidaciones_insert()` |
| `trigger_liquidaciones_update` | liquidaciones | AFTER UPDATE | FOR EACH ROW | `tr_liquidaciones_update()` |
| `trigger_liquidaciones_delete` | liquidaciones | BEFORE DELETE | FOR EACH ROW | `tr_liquidaciones_delete()` |

---

## 📈 Vistas SQL

### **detalle_liquidaciones**
Vista que consolida información de liquidaciones para reportes.

```sql
SELECT 
    dc.id AS detalle_id,
    dc.certificado_id,
    dc.monto AS monto_original,
    COALESCE(SUM(l.cantidad_liquidacion), 0) AS total_liquidado,
    (dc.monto - COALESCE(SUM(l.cantidad_liquidacion), 0)) AS cantidad_pendiente,
    COUNT(l.id) AS num_liquidaciones,
    MAX(l.fecha_liquidacion) AS fecha_ultima_liquidacion
```

**Columnas:**
- `detalle_id`: ID del detalle certificado
- `certificado_id`: ID del certificado
- `monto_original`: Monto original asignado
- `total_liquidado`: Total pagado hasta ahora
- `cantidad_pendiente`: Monto aún pendiente
- `num_liquidaciones`: Cantidad de pagos registrados
- `fecha_ultima_liquidacion`: Fecha del último pago

---

## 🗂️ Archivos SQL

| Archivo | Descripción |
|---------|-------------|
| `schema_postgresql.sql` | Schema original (referencia) |
| `crear_tabla_usuarios.sql` | Creación de tabla usuarios |
| `add_year_column.sql` | Agregación de columna year |

---

## 🚀 Instalación en Producción (Paso a Paso)

### Requisitos Previos
- **PostgreSQL** 12 o superior instalado
- **Usuario:** postgres (con contraseña configurada)
- **Acceso a:** Línea de comandos (Command Prompt o PowerShell en Windows)

---

## 🤖 OPCIÓN 0: Script Automatizado PowerShell (LA MÁS FÁCIL)

Si tienes **PowerShell** en Windows, puedes ejecutar un script que lo hace TODO automáticamente:

### Paso 1: Abre PowerShell como Administrador
- Presiona **Windows + X**
- Selecciona **Windows PowerShell (Admin)** o **Terminal**

### Paso 2: Ejecuta el script
```powershell
C:\xampp\htdocs\programas\certificados-sistema\database\crear_base_datos.ps1
```

Si te pide permiso, escribe:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Luego presiona **Y** para confirmar y ejecuta el script de nuevo.

### Paso 3: Ingresa la contraseña
El script pedirá la contraseña de `postgres`. Ingrésala.

### Paso 4: Listo ✅
El script:
- Crea la base de datos
- Ejecuta TODO el SQL automáticamente
- Verifica que las tablas se crearon
- Muestra un resumen final

---

## 📝 OPCIÓN 1: Copiar y Ejecutar en pgAdmin (MÁS FÁCIL)

Si tienes **pgAdmin** instalado (interfaz gráfica de PostgreSQL):

1. Abre **pgAdmin**
2. Conecta con tu servidor PostgreSQL
3. Haz clic derecho en **"Databases"** → **"Create"** → **"Database"**
4. Nombre: `certificados_sistema`
5. Haz clic en **Create**
6. Abre la pestaña **"Query Tool"** (ícono SQL)
7. **Copia TODO el script SQL** desde arriba
8. **Pégalo en el Query Tool**
9. Haz clic en el botón **▶️ Execute** (o presiona F5)
10. ¡Listo! Las tablas, funciones y triggers se crearán automáticamente

---

## 💻 OPCIÓN 2: Línea de Comandos en Windows (Más Rápido)

### Paso 1: Abre PowerShell como Administrador
- Presiona **Windows + X**
- Selecciona **Windows PowerShell (Admin)** o **Terminal**

### Paso 2: Navega a la carpeta del script
```powershell
cd "C:\xampp\htdocs\programas\certificados-sistema\database"
```

### Paso 3: Crea la base de datos
```powershell
psql -U postgres -c "CREATE DATABASE certificados_sistema ENCODING 'UTF8';"
```
> Te pedirá la contraseña de `postgres`. Ingrésala.

### Paso 4: Guarda el script SQL en un archivo

**Opción A:** Si ya tienes el archivo `schema_produccion.sql`
```powershell
psql -U postgres -d certificados_sistema -f schema_produccion.sql
```

**Opción B:** Si NO tienes el archivo, créalo:
```powershell
# Abre Notepad
notepad "C:\xampp\htdocs\programas\certificados-sistema\database\schema_produccion.sql"
```
- Copia TODO el script SQL desde arriba
- Pégalo en Notepad
- Guarda el archivo (Ctrl + S)
- Regresa a PowerShell y ejecuta:
```powershell
psql -U postgres -d certificados_sistema -f schema_produccion.sql
```

### Paso 5: Verifica que se creó correctamente
```powershell
psql -U postgres -d certificados_sistema -c "\dt public.*"
```

Deberías ver una lista con estas 10 tablas:
- usuarios
- presupuesto_items
- estructura_presupuestaria
- certificados
- detalle_certificados
- liquidaciones
- auditoria_liquidaciones
- delete_tracking
- trigger_log
- trigger_logs

---

## 📱 OPCIÓN 3: Método Completo (Paso a Paso Detallado)

### Si NO tienes PostgreSQL o está en otra ruta:

1. **Abre cmd.exe** (no PowerShell)
2. Busca dónde está instalado PostgreSQL:
```cmd
where psql
```
3. Debería mostrar algo como: `C:\Program Files\PostgreSQL\15\bin\psql.exe`

4. Navega a esa carpeta:
```cmd
cd "C:\Program Files\PostgreSQL\15\bin"
```

5. Luego ejecuta los comandos anteriores:
```cmd
psql -U postgres -c "CREATE DATABASE certificados_sistema ENCODING 'UTF8';"
psql -U postgres -d certificados_sistema -f "C:\xampp\htdocs\programas\certificados-sistema\database\schema_produccion.sql"
```

---

## ✅ Verificación Final

Después de ejecutar, verifica que TODO funcione:

```powershell
# Ver todas las tablas
psql -U postgres -d certificados_sistema -c "\dt public.*"

# Ver funciones
psql -U postgres -d certificados_sistema -c "\df public.*"

# Ver triggers
psql -U postgres -d certificados_sistema -c "\dy"

# Ver vistas
psql -U postgres -d certificados_sistema -c "\dv public.*"
```

Si ves todas las tablas listadas → **¡Está listo para usar!** ✅

---

## 🔧 Operaciones Comunes

### Insertar Usuario Administrador
```sql
INSERT INTO usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, es_root)
VALUES ('Admin', 'Sistema', 'admin@institucion.gov', 'Administrador', 'admin', 'hash_contraseña', 1);
```

### Ver Certificados por Año
```sql
SELECT * FROM certificados WHERE year = 2026 ORDER BY fecha_creacion DESC;
```

### Ver Liquidaciones de un Certificado
```sql
SELECT 
    dc.codigo_completo,
    dc.monto AS monto_original,
    SUM(l.cantidad_liquidacion) AS liquidado,
    (dc.monto - SUM(l.cantidad_liquidacion)) AS pendiente
FROM detalle_certificados dc
LEFT JOIN liquidaciones l ON l.detalle_certificado_id = dc.id
WHERE dc.certificado_id = 1
GROUP BY dc.id, dc.codigo_completo, dc.monto;
```

### Ver Historial de Cambios
```sql
SELECT * FROM auditoria_liquidaciones ORDER BY fecha_cambio DESC LIMIT 20;
```

### Contar Certificados por Usuario
```sql
SELECT 
    u.nombre,
    u.apellidos,
    COUNT(c.id) AS total_certificados
FROM usuarios u
LEFT JOIN certificados c ON c.usuario_id = u.id
GROUP BY u.id, u.nombre, u.apellidos;
```

---

## 📊 Estadísticas Útiles

### Total de Certificados por Año
```sql
SELECT year, COUNT(*) AS total FROM certificados GROUP BY year ORDER BY year DESC;
```

### Monto Total Liquidado vs Pendiente
```sql
SELECT 
    SUM(total_liquidado) AS total_pagado,
    SUM(total_pendiente) AS total_pendiente,
    SUM(monto_total) AS presupuesto_total
FROM certificados
WHERE year = 2026;
```

### Usuarios Más Activos
```sql
SELECT 
    usuario_creacion,
    COUNT(*) AS total_certificados,
    SUM(monto_total) AS monto_total
FROM certificados
GROUP BY usuario_creacion
ORDER BY total_certificados DESC;
```

---

## ⚠️ Consideraciones de Seguridad

1. **Credenciales:** Cambiar contraseña de usuario `postgres` en producción
2. **Backups:** Realizar backups regulares de la base de datos
3. **Acceso:** Limitar acceso a usuario específico (no usar `postgres`)
4. **Auditoría:** Revisar regularmente `auditoria_liquidaciones`
5. **Encriptación:** Considerar encriptación de campos sensibles

---

## 🔄 Backup y Restauración

### Crear Backup
```bash
pg_dump -U postgres certificados_sistema > backup_$(date +%Y%m%d).sql
```

### Restaurar desde Backup
```bash
psql -U postgres -d certificados_sistema -f backup_20260112.sql
```

---

## 📝 Notas de Versión

**v1.0 (2026-01-12)**
- Creación inicial de schema
- Implementación de auditoria de liquidaciones
- Soporte multi-año con year como columna
- Unique constraint en (numero_certificado, year) para numbering yearly

---

## 📞 Soporte

Para problemas con la base de datos:
1. Verificar logs de PostgreSQL: `var/log/postgresql/`
2. Revisar triggers activos: `\dy` en psql
3. Consultar tabla de auditoría: `SELECT * FROM auditoria_liquidaciones`

---

**Última Actualización:** 2026-01-12  
**Mantenedor:** Sistema de Gestión de Certificados  
**Estado:** ✅ Producción
