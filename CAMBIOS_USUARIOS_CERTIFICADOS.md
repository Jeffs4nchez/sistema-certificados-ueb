# Actualización: Registro de Usuario en Certificados

## ✅ Cambios Realizados

### 1. Base de Datos
- ✓ Columna `usuario_id` agregada a tabla `certificados` (referencia a tabla usuarios)
- ✓ Columna `usuario_creacion` agregada a tabla `certificados` (almacena nombres y apellidos)

### 2. Modelo Certificate.php
- ✓ Método `createCertificate()` actualizado para guardar:
  - `usuario_id`: ID del usuario autenticado
  - `usuario_creacion`: Nombre y apellidos del usuario

### 3. Controlador CertificateController.php
- ✓ Al crear un certificado, ahora se pasan automáticamente:
  - `usuario_id` desde `$_SESSION['usuario_id']`
  - `usuario_creacion` desde `$_SESSION['usuario_nombre']`

### 4. Vista list.php
- ✓ Columna "Usuario" muestra `usuario_creacion` en lugar de "Sistema"

---

## 🎯 Cómo Funciona

### Cuando un usuario autenticado crea un certificado:

1. El usuario inicia sesión y obtiene una sesión con sus datos
2. Al crear un certificado, automáticamente se registra:
   - Su ID en la BD
   - Sus nombres y apellidos en la vista

### Ejemplo de sesión:
```php
$_SESSION['usuario_id'] = 1
$_SESSION['usuario_nombre'] = "Juan Pérez Admin"
$_SESSION['usuario_correo'] = "admin@institucion.com"
$_SESSION['usuario_tipo'] = "admin"
```

### Cuando se crea un certificado:
```php
certificados.usuario_id = 1
certificados.usuario_creacion = "Juan Pérez Admin"
```

### En la lista de certificados aparecerá:
```
CERT-001 | Universidad XYZ | Juan Pérez Admin | 29/11/2025 | $10,000
```

---

## ✨ Beneficios

- ✅ Auditoría completa de quién creó cada certificado
- ✅ Trazabilidad de cambios en el sistema
- ✅ Reportes por usuario
- ✅ Control de permisos mejorado
- ✅ Registro histórico de operaciones

---

## 📊 Consultas Útiles

### Ver certificados creados por un usuario:
```sql
SELECT * FROM certificados WHERE usuario_id = 1;
```

### Contar certificados por usuario:
```sql
SELECT u.nombre, u.apellidos, COUNT(c.id) as total_certificados
FROM usuarios u
LEFT JOIN certificados c ON u.id = c.usuario_id
GROUP BY u.id, u.nombre, u.apellidos
ORDER BY total_certificados DESC;
```

### Ver historial completo:
```sql
SELECT 
    c.id,
    c.numero_certificado,
    c.institucion,
    c.usuario_creacion,
    c.fecha_creacion,
    c.monto_total
FROM certificados c
ORDER BY c.fecha_creacion DESC;
```

---

## ✅ Listo para Usar

El sistema está completamente configurado. Ahora:

1. Inicia sesión con cualquier usuario
2. Crea un nuevo certificado
3. Verifica en la lista que aparezca tu nombre en la columna "Usuario"

¡Todo funciona automáticamente! 🚀
