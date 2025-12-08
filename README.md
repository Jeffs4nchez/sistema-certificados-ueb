# Sistema de Certificados - UEB

## Descripción
Sistema de gestión de certificados y presupuestos para la Universidad Estatal de Bolívar (UEB). Permite administrar certificados, liquidaciones, presupuestos y generar reportes detallados.

## Características Principales

### 1. Gestión de Certificados
- Crear, editar y eliminar certificados
- Asociar múltiples ítems a certificados
- Tracking de estados (Pendiente, Liquidado, Cancelado)
- Búsqueda y filtrado avanzado

### 2. Liquidaciones
- Registrar liquidaciones de certificados
- Cálculo automático de `cantidad_pendiente = monto - cantidad_liquidacion`
- Actualización en tiempo real del `col4` (col4 -= cantidad_pendiente)
- Sincronización automática con tabla presupuesto_items
- Cálculo de saldo disponible: `saldo_disponible = col3 - col4`

### 3. Presupuestos
- Gestión de presupuestos por usuario/departamento
- Columnas: col1, col2, col3 (asignado), col4 (gastado)
- Saldo disponible calculado automáticamente

### 4. Reportes
- Reportes por usuario
- Reportes por certificado
- Exportación a Excel/PDF
- Estadísticas y análisis

## Estructura del Proyecto

```
app/
├── controllers/          # Controladores MVC
│   ├── CertificateController.php
│   ├── APICertificateController.php
│   └── ...
├── models/              # Modelos y lógica de negocio
│   ├── Certificate.php
│   ├── Presupuesto.php
│   └── ...
└── views/               # Vistas (HTML/Template)

database/
├── migrations/          # Cambios de estructura
└── seeds/               # Datos iniciales

public/
├── index.php            # Punto de entrada
└── assets/              # CSS, JS, imágenes

resources/
├── css/
├── js/
└── views/
```

## Requisitos Técnicos

- **PHP**: 8.0+
- **Base de Datos**: MySQL/PostgreSQL
- **Servidor Web**: Apache/Nginx
- **Dependencias**: Composer packages

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Jeffs4nchez/sistema-certificados-ueb.git
```

2. Instalar dependencias:
```bash
composer install
npm install
```

3. Configurar base de datos en `.env`

4. Ejecutar migraciones:
```bash
php artisan migrate
```

5. Iniciar servidor:
```bash
php artisan serve
```

## Configuración de la Base de Datos

Las tablas principales son:

- **certificados**: Información general de certificados
- **detalle_certificados**: Ítems asociados a certificados
- **presupuesto_items**: Presupuestos por partida
- **usuarios**: Usuarios del sistema

## API REST

El sistema incluye endpoints API para integración con aplicaciones externas:

- `GET /api/certificados` - Listar certificados
- `POST /api/certificados` - Crear certificado
- `GET /api/certificados/{id}` - Obtener detalles
- `PUT /api/certificados/{id}/liquidar` - Registrar liquidación

## Lógica de Liquidación

Cuando se registra una liquidación:

1. **Cálculo de pendiente**: `cantidad_pendiente = monto - cantidad_liquidacion`
2. **Actualización de detalle**: Se guarda en `detalle_certificados.cantidad_pendiente`
3. **Sincronización presupuesto**: Se resta de `presupuesto_items.col4`
   - Formula: `col4_nuevo = col4_anterior - cantidad_pendiente_nuevo`
4. **Cálculo de saldo**: `saldo_disponible = col3 - col4`
5. **Totales certificado**: Se recalculan totales en tabla `certificados`

## Operaciones Principales

### Registrar Liquidación
```php
$certificateModel->updateLiquidacion(
    $id_detalle,
    $cantidad_liquidacion,
    $memorando
);
```

Esta operación:
- Calcula cantidad_pendiente
- Actualiza detalle_certificados
- Suma totales de pendientes por codigo_completo
- Actualiza presupuesto_items con col4 -= cantidad_pendiente
- Calcula saldo_disponible
- Actualiza totales en certificados

### Consultar Estado
```php
$estado = $certificateModel->getStatusByDetailId($id_detalle);
```

## Logging y Debugging

El sistema incluye logs detallados con emojis para facilitar debugging:
- 📌 Información de entrada
- ✅ Operaciones exitosas
- ❌ Errores y excepciones
- 🔄 Procesos en progreso

Los logs se guardan en `storage/logs/`

## Desarrollo

### Git Workflow
```bash
git add .
git commit -m "Descripción del cambio"
git push origin main
```

### Estándares de Código
- Usar nombres descriptivos en español/inglés
- Documentar métodos complejos
- Incluir manejo de errores
- Agregar logs para facilitar debugging

## Solución de Problemas

### cantidad_pendiente no se guarda
- Verificar que se use `$certificateModel->updateLiquidacion()`
- Revisar logs en `storage/logs/`
- Confirmar conexión a base de datos

### col4 no se actualiza correctamente
- Verificar que la fórmula sea: `col4 -= cantidad_pendiente`
- No debe ser reemplazo: `col4 = cantidad_pendiente`
- Revisar transacciones en base de datos

## Contacto y Soporte

Para reportar problemas o sugerencias:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo

## Licencia

Proyecto privado - Derechos reservados UEB

---

**Última actualización**: Diciembre 2025
**Versión**: 1.0
