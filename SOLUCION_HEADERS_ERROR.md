# Solución: Error "Cannot modify header information"

## 🐛 Problema
Al guardar un certificado, salía el error:
```
Warning: Cannot modify header information - headers already sent by 
(output started at C:\xampp\htdocs\...\app\views\layout\header.php:92)
```

## ✅ Causa
El archivo `header.php` se estaba incluyendo (generando HTML) antes de que se ejecutase `header()` en el controlador para redirigir.

## 🔧 Solución Implementada

### 1. Output Buffering en index.php
- Se detectan acciones que pueden hacer POST (certificate-create, usuario, perfil)
- Cuando hay POST, se activa `ob_start()` ANTES de incluir header.php
- Esto previene que el HTML se envíe inmediatamente

### 2. Limpiezas de Buffer en Controladores
Se agregó limpieza del buffer antes de cada `header()`:
```php
if (ob_get_level() > 0) {
    ob_end_clean();
}
header('Location: ...');
```

### 3. Controladores Actualizados
- `CertificateController.php` - Al guardar certificado
- `UsuarioController.php` - Al crear/editar/eliminar usuario
- `PerfilController.php` - Al cambiar contraseña

## 📝 Cambios en index.php

### Antes:
```php
// No cargar layout para peticiones API
if ($action !== 'api-certificate' && !$is_public) {
    require_once __DIR__ . '/app/views/layout/header.php';
}
```

### Ahora:
```php
// Rutas que pueden hacer redirecciones (POST processing)
$redirect_actions = ['certificate-create', 'usuario', 'perfil'];
$may_redirect = in_array($action, $redirect_actions) && $_SERVER['REQUEST_METHOD'] === 'POST';

// Iniciar output buffering si puede haber redirecciones
if ($may_redirect) {
    ob_start();
}

// No cargar layout si puede redirigir
if ($action !== 'api-certificate' && !$is_public && !$may_redirect) {
    require_once __DIR__ . '/app/views/layout/header.php';
}
```

## ✨ Cómo Funciona

### Flujo normal (GET):
1. index.php inicia sesión
2. Carga header.php (genera navbar)
3. Carga controlador (muestra formulario)
4. Carga footer.php
5. Envía HTML al navegador

### Flujo POST (con redirección):
1. index.php inicia sesión
2. **Activa output buffering**
3. NO carga header.php (se evita generar HTML)
4. Carga controlador (procesa POST)
5. Controlador limpia buffer y hace `header()`
6. Navegador recibe redirección (sin error)

## ✅ Resultado
- ✓ Guardar certificados funciona correctamente
- ✓ Crear/editar usuarios funciona
- ✓ Cambiar contraseña funciona
- ✓ NO hay errores de "headers already sent"
- ✓ Todas las redirecciones funcionan correctamente

## 🧪 Para Probar
1. Inicia sesión
2. Crea un nuevo certificado
3. Rellena los datos
4. Haz clic en "Guardar"
5. ✓ Deberías ser redirigido a la lista sin errores

¡Listo! 🎉
