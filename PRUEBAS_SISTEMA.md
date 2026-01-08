# 🧪 PRUEBAS: Cómo Verificar que Todo Funciona

## ✅ Checklist de Pruebas

### Test 1: Página de Login
- [ ] Abre `index.php` (o la página de login)
- [ ] Verifica que el select de año esté visible
- [ ] Verifica que muestre años 2026, 2025, 2024, 2023, 2022, 2021
- [ ] El año 2026 está seleccionado por defecto

### Test 2: Validación - Campo obligatorio
- [ ] **Intenta enviar sin seleccionar año:**
  - Email: admin@institucion.com
  - Contraseña: admin123
  - Año: (sin seleccionar)
  - Click "Iniciar Sesión"
- [ ] **Resultado esperado:** Error "Debe seleccionar un año de trabajo"
- [ ] Permanece en la página de login

### Test 3: Validación - Formato incorrecto
- [ ] **Intenta enviar con año inválido (mediante Inspect):**
  - Modifica el valor del select a "abcd" o "20"
  - Envía el formulario
- [ ] **Resultado esperado:** Error "Año de trabajo inválido"

### Test 4: Login exitoso con año
- [ ] **Ingresa credenciales correctas:**
  - Email: admin@institucion.com
  - Contraseña: admin123
  - Año: 2026
- [ ] Click "Iniciar Sesión"
- [ ] **Resultado esperado:** Redirige al dashboard

### Test 5: Verificar selector en navbar
- [ ] En el dashboard (o cualquier página), mira la navbar superior
- [ ] Verifica que aparece: `📅 [2026 ▼] Año Actual`
- [ ] Al lado del logo, antes de los menús

### Test 6: Cambiar año desde navbar
- [ ] Haz clic en el dropdown del año
- [ ] Selecciona otro año (ejemplo: 2025)
- [ ] **Resultado esperado:** 
  - Página se recarga
  - El navbar muestra: `📅 [2025 ▼] Año Actual`
  - La URL permanece igual (solo cambia la sesión)

### Test 7: Cambiar año múltiples veces
- [ ] Cambia año: 2025 → 2026 → 2024 → 2023
- [ ] Verifica que cada cambio se refleje en el navbar
- [ ] Navega a diferentes secciones (Certificados, Presupuesto, etc.)
- [ ] El año persiste en cada sección

### Test 8: Cerrar y abrir sesión
- [ ] En sesión con año 2024
- [ ] Click en "Cerrar Sesión" (Logout)
- [ ] **Resultado esperado:** Se destruye la sesión
- [ ] Vuelve a la página de login
- [ ] Intenta hacer login nuevamente

### Test 9: Año persiste en sesión
- [ ] Login con año 2025
- [ ] Navega a: Dashboard → Certificados → Usuarios → Presupuesto
- [ ] En cada página, verifica que el navbar siga mostrando `2025`
- [ ] **Resultado esperado:** El año NO cambia automáticamente

### Test 10: Acceso directo a controlador
- [ ] Abre en la barra de dirección: `?action=auth&method=cambiarAño`
- [ ] **Resultado esperado:** Error o redirige (método solo acepta POST)

---

## 🔍 Verificaciones en el Código

### Test 11: Verificar que $_SESSION se guarda
```php
// En cualquier página autenticada, agrega esto temporalmente:
<?php
echo '<pre>';
var_dump($_SESSION);
echo '</pre>';
?>
```

**Resultado esperado:**
```
array (size=6)
  'usuario_id' => int 1
  'usuario_nombre' => string 'Admin Usuario'
  'usuario_correo' => string 'admin@institucion.com'
  'usuario_tipo' => string 'admin'
  'usuario_cargo' => string 'Administrador'
  'año_trabajo' => string '2026'    ← DEBE EXISTIR
```

### Test 12: Llamar a método AuthController::obtenerAñoTrabajo()
```php
<?php
// En cualquier controlador:
$año = AuthController::obtenerAñoTrabajo();
echo "Año de trabajo: " . $año;
?>
```

**Resultado esperado:**
```
Año de trabajo: 2026
```

---

## 🐛 Solución de Problemas

### ❌ Error: "No se ve el select de año en login"
**Solución:**
1. Verifica que la columna HTML esté en [app/views/auth/login.php](app/views/auth/login.php)
2. Busca por "Año de Trabajo"
3. Si no está, revisa que el archivo se haya actualizado correctamente
4. Limpia caché del navegador (Ctrl+F5)

### ❌ Error: "El año no se valida"
**Solución:**
1. Verifica que [app/controllers/AuthController.php](app/controllers/AuthController.php) tenga la validación
2. Busca por "preg_match('/^\d{4}$/', $año_trabajo)"
3. Verifica que esté en el método `procesarLogin()`

### ❌ Error: "El selector de año no aparece en navbar"
**Solución:**
1. Verifica que la sesión esté activa (`isset($_SESSION['usuario_id'])`)
2. Revisa [app/views/layout/header.php](app/views/layout/header.php)
3. Busca por "Selector de Año de Trabajo"
4. Verifica que esté DENTRO del `if (isset($_SESSION['usuario_id'])):`

### ❌ Error: "No puedo cambiar el año"
**Solución:**
1. Abre DevTools (F12) → Network
2. Haz clic en cambiar año
3. Verifica que se envíe POST a `?action=auth&method=cambiarAño`
4. El servidor debe responder 302 (redirect)

### ❌ Error: "El año no persiste en sesión"
**Solución:**
1. Verifica que `session_start()` esté al inicio del archivo
2. Revisa que `$_SESSION['año_trabajo']` se guarde en `procesarLogin()`
3. No cierres la sesión con `session_destroy()` sin querer

---

## 📊 Datos de Prueba

### Cuentas de prueba:
```
Admin:
  Email: admin@institucion.com
  Contraseña: admin123
  Tipo: admin

Operador:
  Email: encargado@institucion.com
  Contraseña: encargado123
  Tipo: operador
```

### Años de prueba:
- 2026 (actual)
- 2025
- 2024
- 2023
- 2022
- 2021

---

## 📸 Capturas de Pantalla Esperadas

### Pantalla 1: Login con select de año
```
┌─────────────────────────────┐
│ 🎓 Sistema de Gestión      │
│ Certificados y Presupuesto │
├─────────────────────────────┤
│ Email: [__________]         │
│ Contraseña: [__________]    │
│ Año: [2026 ▼]              │ ← Debe aparecer
│ [Recuérdame]                │
│ [INICIAR SESIÓN]            │
└─────────────────────────────┘
```

### Pantalla 2: Navbar con selector
```
┌─────────────────────────────────┐
│ 🎓 Sistema | 📅 2026 ▼ | Menú...│
└─────────────────────────────────┘
           ↑
      Debe estar aquí
```

### Pantalla 3: Dropdown abierto
```
📅 Año Actual [2026 ▼]
   ├─ 2026 ✓
   ├─ 2025
   ├─ 2024
   ├─ 2023
   ├─ 2022
   └─ 2021
```

---

## ✅ Confirmación Final

Si pasas todos estos tests, la implementación está **100% correcta**:

- ✅ Login funciona con año
- ✅ Año se valida
- ✅ Año se guarda en sesión
- ✅ Año se muestra en navbar
- ✅ Año se puede cambiar desde navbar
- ✅ Año persiste en la sesión
- ✅ Año se limpia al cerrar sesión

---

## 📝 Notas Importantes

⚠️ **El filtro real de datos por año aún no está implementado**

Esto significa:
- El año se selecciona y se guarda ✓
- Pero los datos no se filtran automáticamente ✗
- Necesitas actualizar los modelos (Certificate.php, etc.)
- Agregar columna `año` en tablas de BD
- Modificar los queries para filtrar por año

**Próximo paso:** [Ver GUIA_FILTRO_AÑO.md](GUIA_FILTRO_AÑO.md)

---

## 🚀 Resumen

```
✅ COMPLETADO:
   - Interfaz de login con select de año
   - Validación del año
   - Guardado en sesión
   - Selector en navbar
   - Método para obtener el año

⏳ POR HACER:
   - Filtrar datos en modelos
   - Agregar columna año en BD
   - Actualizar controladores para usar el año
```

---

**¡A probar el sistema!**

Recuerda: Las pantallas deben verse como en VISUAL_IMPLEMENTACION.md
