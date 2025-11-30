# Sistema de Gestión de Certificados Presupuestarios

Sistema web completo para la gestión, certificación y liquidación de presupuestos institucionales.

## 📋 Características

- ✅ **Gestión de Certificados** - Crear, editar, ver y eliminar certificaciones presupuestarias
- ✅ **Liquidación de Certificados** - Registrar liquidaciones por item presupuestario
- ✅ **Importación de Presupuestos** - Importar datos presupuestarios desde archivos CSV
- ✅ **Reportes PDF** - Generar reportes profesionales optimizados para A4
- ✅ **Filtrado Avanzado** - Filtrar presupuestos por programa, actividad y fuente
- ✅ **Dashboard** - Visualizar estadísticas generales del sistema
- ✅ **Interfaz Responsive** - Diseño adaptable a diferentes dispositivos

## 🛠️ Tecnología

- **Backend:** PHP 7+
- **Base de Datos:** PostgreSQL/MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 5
- **JavaScript:** Vanilla JS + Fetch API
- **Librerías:** Font Awesome (iconos)

## 📁 Estructura del Proyecto

```
certificados-sistema/
├── app/
│   ├── config.php                    # Configuración de la aplicación
│   ├── Database.php                  # Clase de conexión a BD
│   ├── controllers/                  # Controladores
│   │   ├── APICertificateController.php
│   │   ├── CertificateController.php
│   │   ├── DashboardController.php
│   │   ├── ImportController.php
│   │   ├── ParameterController.php
│   │   └── PresupuestoController.php
│   ├── models/                       # Modelos de datos
│   │   ├── Certificate.php
│   │   ├── CertificateItem.php
│   │   ├── Parameter.php
│   │   └── PresupuestoItem.php
│   ├── helpers/                      # Funciones auxiliares
│   │   └── MontoHelper.php
│   └── views/                        # Vistas (plantillas)
│       ├── certificate/
│       ├── dashboard.php
│       ├── import/
│       ├── parameters/
│       └── presupuesto/
├── database/                         # Scripts y migrations SQL
│   ├── schema_postgresql.sql
│   ├── estructura_presupuestaria.sql
│   └── (archivos de importación y migración)
├── public/                           # Archivos públicos
│   ├── css/                          # Estilos
│   └── js/                           # JavaScript
├── index.php                         # Punto de entrada
├── bootstrap.php                     # Inicialización
└── README.md                         # Este archivo
```

## 🚀 Instalación

### Requisitos Previos

- PHP 7.4 o superior
- PostgreSQL 12+ o MySQL 5.7+
- Composer (opcional)
- Git

### Pasos de Instalación

1. **Clonar el repositorio:**
```bash
git clone https://github.com/TU_USUARIO/certificados-sistema.git
cd certificados-sistema
```

2. **Configurar la conexión a base de datos:**
   - Editar `app/config.php` con tus credenciales de BD

3. **Crear la base de datos:**
```bash
# Usar el script SQL apropiado según tu BD
mysql < database/schema_postgresql.sql
```

4. **Asignar permisos (si es necesario):**
```bash
chmod -R 755 app/
chmod -R 755 public/
```

5. **Acceder a la aplicación:**
```
http://localhost/programas/certificados-sistema/
```

## 📖 Uso

### Dashboard
Accede al dashboard para ver un resumen de:
- Total de certificados
- Presupuesto certificado vs disponible
- Últimas transacciones

### Crear un Certificado
1. Navega a **Certificados**
2. Haz clic en **Crear Certificado**
3. Completa los campos requeridos
4. Haz clic en **Guardar**

### Importar Presupuestos
1. Navega a **Presupuesto**
2. Haz clic en **Importar CSV**
3. Carga tu archivo CSV con la estructura requerida
4. El sistema procesará e importará los datos

### Liquidar un Certificado
1. En la lista de certificados, haz clic en el botón **Liquidación**
2. Ingresa las cantidades liquidadas por item
3. Haz clic en **Guardar**

### Generar Reporte
1. Abre un certificado
2. Haz clic en **Imprimir** para generar PDF
3. El reporte se optimiza automáticamente para A4

## 🗄️ Base de Datos

### Tablas Principales

- **certificados** - Registro de certificaciones
- **certificados_detalles** - Items de cada certificación
- **presupuesto_items** - Presupuestos importados
- **parametros** - Configuración del sistema

### Migrations

Todos los scripts SQL de creación de tablas están en `database/`

## 🔧 Configuración

Editar `app/config.php`:

```php
// Conexión a BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'certificados_sistema');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_TYPE', 'mysql'); // o 'pgsql'
```

## 📝 API Endpoints

### Certificados
- `GET /index.php?action=api-certificate&action-api=get-liquidacion&certificate_id=X`
- `POST /index.php?action=api-certificate&action-api=update-liquidacion`

### Presupuestos
- `GET /index.php?action=presupuesto-list`
- `POST /index.php?action=presupuesto-upload` (importar CSV)

## 🐛 Solución de Problemas

### Error de conexión a BD
- Verificar credenciales en `app/config.php`
- Asegurar que el servicio de BD está ejecutándose
- Verificar permisos de usuario de BD

### Errores de importación CSV
- Verificar estructura del CSV (columnas esperadas)
- Asegurar que el archivo está en UTF-8
- Revisar los logs en `view_logs.php`

## 📄 Licencia

Este proyecto está bajo licencia [MIT](LICENSE)

## 👥 Contribuciones

Las contribuciones son bienvenidas. Para cambios importantes:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para reportar problemas o sugerencias, abre un issue en el repositorio.

## 🎯 Roadmap

- [ ] Autenticación de usuarios mejorada
- [ ] Auditoría de cambios
- [ ] Exportación a Excel
- [ ] Reportes avanzados
- [ ] API REST completa
- [ ] Integración con sistemas de nómina

---

**Versión:** 1.0  
**Última actualización:** 2025-01-29
