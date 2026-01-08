# Guía: Sistema de Filtro por Año de Trabajo

## ¿Qué se implementó?

Se agregó un sistema que permite a los usuarios seleccionar un **año de trabajo** al iniciar sesión. Todos los datos, certificados y presupuestos quedarán filtrados **solo al año seleccionado**.

---

## Cómo Usar el Año en Código

### 1. **Obtener el año actual de la sesión**

```php
// En cualquier controlador o vista
$año = $_SESSION['año_trabajo'] ?? date('Y');

// O mejor aún, usar el método del AuthController
$año = AuthController::obtenerAñoTrabajo();
```

### 2. **Ejemplo: Filtrar certificados por año**

En `CertificateController.php`, método `listAction()`:

```php
public function listAction() {
    $año_trabajo = AuthController::obtenerAñoTrabajo();
    
    if (PermisosHelper::esAdmin()) {
        // Admin ve todos, pero solo del año seleccionado
        $certificates = $this->certificateModel->getAllByYear($año_trabajo);
    } else {
        // Operador solo ve sus certificados del año seleccionado
        $usuario_id = PermisosHelper::getUsuarioIdActual();
        $certificates = $this->certificateModel->getByUsuarioAndYear($usuario_id, $año_trabajo);
    }
    
    require_once __DIR__ . '/../views/certificate/list.php';
}
```

---

## Modelos a Actualizar

### Tabla: `certificados`
Agregar columna de año si no existe:

```sql
ALTER TABLE certificados ADD COLUMN año INT DEFAULT 2024;
```

### Ejemplo de Métodos en Certificate.php

```php
public function getAllByYear($año) {
    $sql = "SELECT * FROM certificados WHERE año = :año ORDER BY fecha_creacion DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':año' => $año]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getByUsuarioAndYear($usuario_id, $año) {
    $sql = "SELECT * FROM certificados 
            WHERE usuario_creacion = :usuario_id 
            AND año = :año 
            ORDER BY fecha_creacion DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id, ':año' => $año]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

---

## Cambiar el Año sin Cerrar Sesión

El selector de año está en la **navbar**, en la parte superior izquierda. 

Al cambiar el año, se redirige automáticamente a la misma página manteniendo la sesión activa.

```php
// La URL que se llama es:
?action=auth&method=cambiarAño

// El AuthController ya tiene el método implementado:
public function cambiarAño() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ?action=dashboard');
        exit;
    }
    
    $año_trabajo = $_POST['año_trabajo'] ?? '';
    
    if (!empty($año_trabajo) && preg_match('/^\d{4}$/', $año_trabajo)) {
        $_SESSION['año_trabajo'] = $año_trabajo;
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '?action=dashboard';
    header('Location: ' . $referer);
    exit;
}
```

---

## Archivos Modificados

✅ **app/views/auth/login.php**
- Agregado select de año en el formulario de login

✅ **app/controllers/AuthController.php**
- Agregado validación de año en `procesarLogin()`
- Guardado de año en `$_SESSION['año_trabajo']`
- Método `obtenerAñoTrabajo()` para acceso rápido
- Método `cambiarAño()` para cambiar el año sin cerrar sesión

✅ **app/views/layout/header.php**
- Agregado selector de año en la navbar
- Permite cambio rápido del año de trabajo

---

## Próximas Tareas (Recomendadas)

1. **Agregar columna `año` en tabla `certificados`**
   ```sql
   ALTER TABLE certificados ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
   ```

2. **Actualizar modelos para filtrar por año:**
   - Certificate.php
   - Liquidacion.php
   - PresupuestoItem.php (si aplica)

3. **Actualizar controladores principales:**
   - CertificateController.php
   - LiquidacionController.php
   - PresupuestoController.php

4. **En vistas, mostrar el año actual:**
   ```php
   <p>Año: <strong><?php echo AuthController::obtenerAñoTrabajo(); ?></strong></p>
   ```

---

## Notas Importantes

- ⚠️ **El año NO se valida contra la BD**, simplemente filtra por el valor en sesión
- ⚠️ **Al crear nuevos certificados**, asegúrate de guardar el año actual:
  ```php
  $año = AuthController::obtenerAñoTrabajo();
  // Guardar en base de datos
  ```
- ⚠️ **Al cerrar sesión**, el año se limpia automáticamente
- ℹ️ El selector muestra años desde -5 años atrás hasta el año actual

---

## Verificación

Para verificar que está funcionando:

1. Inicia sesión
2. Selecciona un año en el selector de la navbar
3. Verifica que los datos se filten correctamente
4. Cambia de año y verifica el cambio

¡Listo! El sistema está implementado. Solo falta filtrar los modelos y datos.
