# Módulo de Presupuesto - Importar CSV

## Descripción General

El módulo de presupuesto permite importar datos de presupuesto desde archivos CSV a la base de datos del sistema. Ofrece funcionalidades de visualización, búsqueda, cálculo de saldos y eliminación de registros.

## Características

### 1. **Importación de CSV**
- Soporta archivos CSV con cualquier codificación: UTF-8, CP1252 (Windows), ISO-8859-1
- Detección automática de encoding
- Validación de formato y tamaño máximo (10 MB)
- Parseo inteligente respetando comillas en campos CSV
- Mapeo automático de columnas por nombre

### 2. **Gestión de Datos**
- **Visualización**: Lista de presupuestos con búsqueda en tiempo real
- **Detalles**: Vista completa de cada ítem con información financiera
- **Eliminación**: Borrado individual o masivo
- **Cálculo automático**: Saldos disponibles basados en codificado - comprometido

### 3. **Análisis Financiero**
- Resumen con estadísticas totales:
  - Total asignado
  - Total comprometido
  - Saldo disponible
  - Porcentaje de ejecución promedio

## Estructura de Carpetas

```
php-certificates/
├── app/
│   ├── controllers/
│   │   └── PresupuestoController.php    (Controlador de presupuesto)
│   ├── models/
│   │   └── PresupuestoItem.php          (Modelo de presupuesto)
│   ├── views/
│   │   └── presupuesto/
│   │       ├── list.php                 (Listar presupuestos)
│   │       ├── upload.php               (Subir CSV)
│   │       └── view.php                 (Ver detalles)
│   └── Database.php                     (Conexión a BD)
├── database/
│   ├── install.php                      (Crea la tabla presupuesto_items)
│   └── csv/                             (Carpeta para CSVs subidos)
├── index.php                            (Enrutador principal)
└── public/
    ├── css/
    ├── js/
    └── ...
```

## Archivos Creados / Modificados

### Nuevos Archivos

1. **PresupuestoItem.php** (Modelo)
   - `getAll()` - Obtener todos los items
   - `getById($id)` - Obtener item por ID
   - `create($data)` - Crear nuevo item
   - `update($id, $data)` - Actualizar item
   - `delete($id)` - Eliminar item
   - `findByPrograma($codigog1)` - Buscar por código de programa
   - `findByFuente($codigog3)` - Buscar por código de fuente
   - `getResumen()` - Estadísticas totales
   - `calcularSaldo($id)` - Calcular saldo disponible

2. **PresupuestoController.php** (Controlador)
   - `listAction()` - Listar presupuestos
   - `uploadAction()` - Mostrar formulario y procesar subida
   - `viewAction($id)` - Ver detalles de un item
   - `deleteAction($id)` - Eliminar item
   - `handleCSVUpload($file)` - Procesamiento del CSV
   - `parseCSVLine($line)` - Parseo de línea CSV
   - `mapCSVRowToData($headers, $data)` - Mapeo de datos
   - `parseMoneda($value)` - Conversión de valores monetarios

3. **Vistas**
   - `app/views/presupuesto/list.php` - Lista con tabla resumen
   - `app/views/presupuesto/upload.php` - Formulario de carga
   - `app/views/presupuesto/view.php` - Detalles del item

### Archivos Modificados

1. **index.php** (Enrutador)
   - Agregadas 4 nuevas rutas para presupuesto
   - Integración del controlador de presupuesto

2. **app/views/layout/header.php** (Navegación)
   - Nuevo menú "Presupuesto" con 2 opciones:
     - Ver Presupuestos
     - Importar CSV

3. **database/install.php** (Base de datos)
   - Tabla presupuesto_items con 24 columnas
   - Índices en campos de código
   - UTF-8mb4 charset

## Uso del Sistema

### Flujo de Importación

1. **Ir a Presupuesto > Importar CSV**
2. **Seleccionar archivo CSV**
3. **El sistema automáticamente:**
   - Detecta la codificación
   - Lee los encabezados
   - Mapea las columnas por nombre
   - Parsea valores monetarios
   - Importa a la base de datos
4. **Ver resumen de importación** con cantidad de registros importados

### Visualización

1. **Ir a Presupuesto > Ver Presupuestos**
2. **Ver tabla con:**
   - Información resumida de cada item
   - Estadísticas totales en tarjetas
   - Buscador en tiempo real
   - Botones para ver detalles o eliminar

### Búsqueda

- Buscar por nombre de programa, fuente o código
- Búsqueda en tiempo real (sin necesidad de presionar botón)

## Estructura de Datos - CSV Esperado

### Encabezados Reconocidos

| Campo | Nombres Aceptados | Tipo | Ejemplo |
|-------|-------------------|------|---------|
| Programa | PROGRAMA, DESCRIPCIONG1 | VARCHAR(100) | "Educación" |
| Actividad | ACTIVIDAD, DESCRIPCIONG2 | VARCHAR(150) | "Capacitación" |
| Fuente | FUENTE, DESCRIPCIONG3 | VARCHAR(150) | "Presupuesto General" |
| Geográfico | GEOGRAFICO, DESCRIPCIONG4 | VARCHAR(100) | "Nacional" |
| Item | ITEM, DESCRIPCIONG5 | VARCHAR(200) | "Material Didáctico" |
| Montos | COL1-COL10, COL20 | DECIMAL(14,2) | 1000000.50 |
| Código Programa | CODIGOG1, CÓDIGO 1 | VARCHAR(20) | "01" |
| Código Actividad | CODIGOG2 | VARCHAR(20) | "01.01" |
| Código Fuente | CODIGOG3 | VARCHAR(20) | "10" |
| Código Geográfico | CODIGOG4 | VARCHAR(20) | "01" |
| Código Item | CODIGOG5 | VARCHAR(20) | "01" |

### Ejemplo de CSV

```csv
PROGRAMA,ACTIVIDAD,FUENTE,GEOGRAFICO,ITEM,COL1,COL2,COL3,COL4,COL5,COL6,COL7,COL8,COL9,COL10,COL20,CODIGOG1,CODIGOG2,CODIGOG3,CODIGOG4,CODIGOG5
"Educación","Capacitación","Presupuesto General","Nacional","Material Didáctico",1000000,800000,900000,700000,500000,300000,200000,100000,50000,25000,75,01,01.01,10,01,01
```

### Notas sobre Formato

- **Separador de campos**: coma (`,`)
- **Separador decimal**: punto (`.`)
- **Campos con comillas**: Los valores con comillas se parsean correctamente
- **Moneda**: Soporta valores con símbolo de moneda ($, €, etc.) que se eliminan automáticamente
- **Múltiples separadores**: Maneja automáticamente puntos y comas como separadores numéricos

## Tabla de Base de Datos

```sql
CREATE TABLE IF NOT EXISTS presupuesto_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    -- Descripciones
    descripciong1 VARCHAR(100),     -- PROGRAMA
    descripciong2 VARCHAR(150),     -- ACTIVIDAD
    descripciong3 VARCHAR(150),     -- FUENTE
    descripciong4 VARCHAR(100),     -- GEOGRAFICO
    descripciong5 VARCHAR(200),     -- ITEM
    -- Montos (DECIMAL 14,2 = 12 dígitos + 2 decimales)
    col1 DECIMAL(14,2),             -- Asignado
    col2 DECIMAL(14,2),
    col3 DECIMAL(14,2),             -- Codificado
    col4 DECIMAL(14,2),
    col5 DECIMAL(14,2),             -- Comprometido
    col6 DECIMAL(14,2),             -- Devengado
    col7 DECIMAL(14,2),             -- Pagado
    col8 DECIMAL(14,2),
    col9 DECIMAL(14,2),
    col10 DECIMAL(14,2),
    col20 DECIMAL(14,2),            -- % Ejecución
    -- Códigos
    codigog1 VARCHAR(20),           -- Código Programa
    codigog2 VARCHAR(20),           -- Código Actividad
    codigog3 VARCHAR(20),           -- Código Fuente
    codigog4 VARCHAR(20),           -- Código Geográfico
    codigog5 VARCHAR(20),           -- Código Item
    -- Metadatos
    saldo_disponible DECIMAL(14,2), -- Saldo = col3 - col5
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Índices para búsqueda rápida
    INDEX idx_codigog1 (codigog1),
    INDEX idx_codigog3 (codigog3),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Rutas / URLs del Sistema

| Acción | URL | Método |
|--------|-----|--------|
| Listar presupuestos | `?action=presupuesto-list` | GET |
| Subir CSV | `?action=presupuesto-upload` | GET/POST |
| Ver detalles | `?action=presupuesto-view&id=X` | GET |
| Eliminar | `?action=presupuesto-delete&id=X` | POST |

## Integración con Sistema Existente

El módulo se integra completamente con el sistema existente:

1. **Base de datos**: Usa la misma conexión singleton `Database.php`
2. **Layout**: Usa el mismo header/footer compartido
3. **Estilos**: Bootstrap 5 como todo el sistema
4. **Menú**: Integrado en la navegación principal
5. **Patrón MVC**: Sigue el mismo patrón que Certificates y Parameters
6. **Seguridad**: Usa prepared statements de mysqli

## Seguridad

- ✅ Prepared statements para evitar SQL injection
- ✅ Validación de tipo de archivo (solo CSV)
- ✅ Validación de tamaño máximo
- ✅ Escapado de valores de usuario
- ✅ Detección de encoding para evitar problemas UTF-8
- ✅ Manejo de excepciones

## Rendimiento

- **Índices**: Creados en campos codigog1, codigog3, fecha_creacion
- **Búsqueda**: En tiempo real con JavaScript (sin recargar página)
- **Importación**: Optimizada para procesar 1000+ registros
- **Cálculos**: Saldos calculados durante importación (no bajo demanda)

## Próximos Pasos (Opcionales)

1. Agregar filtros avanzados (por rango de fechas, montos, etc.)
2. Exportación de presupuestos a CSV/PDF
3. Gráficos de ejecución presupuestaria
4. Comparativa entre periodos
5. Alertas de saldos disponibles bajos
6. Auditoría de cambios
7. Importación recurrente/programada

## Verificación

Para verificar que el módulo está funcionando:

1. Acceder a `http://localhost/programas/php-certificates/`
2. Ir a "Presupuesto" > "Importar CSV"
3. Descargar plantilla CSV
4. Rellenarla con datos de prueba
5. Importarla
6. Verificar en "Ver Presupuestos"

## Soporte

- Todos los archivos tienen comentarios en código
- Validación completa de datos
- Mensajes de error descriptivos
- Log de importación con detalles de errores
