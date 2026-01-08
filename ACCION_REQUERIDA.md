# 🎬 ACCIÓN REQUERIDA: Ejecutar SQL en 3 pasos

## ⚠️ ¡IMPORTANTE!

El sistema está **100% implementado** pero necesita un paso manual:

### Sin este paso: ❌ No funciona el filtro de año
### Con este paso: ✅ Todo funciona perfectamente

---

## 🚀 3 PASOS SIMPLES

### PASO 1️⃣: Abre el archivo SQL

**Ruta:** 
```
c:\xampp\htdocs\programas\certificados-sistema\database\add_year_column.sql
```

**Qué hacer:**
1. Usa cualquier editor (Notepad, VS Code, etc.)
2. Abre el archivo
3. Selecciona TODO (Ctrl+A)
4. Copia (Ctrl+C)

---

### PASO 2️⃣: Abre tu BD (phpmyadmin o similar)

**En navegador:**
```
http://localhost/phpmyadmin
```

**En phpmyadmin:**
1. Selecciona tu base de datos
2. Haz clic en pestaña **"SQL"**
3. Pega el código (Ctrl+V)

---

### PASO 3️⃣: Ejecuta el SQL

1. Haz clic en **"Ejecutar"** (o presiona Ctrl+Enter)
2. Espera a que termine
3. Deberías ver: ✅ "Query OK" o "Success"

---

## ✅ Verificar que funcionó

### En la BD, ejecuta esta query:
```sql
DESC certificados;
```

### Deberías ver una nueva columna: `año`
```
Field | Type    | Null | Default
------|---------|------|-------
id    | int     | NO   |
...   |         |      |
año   | int     | YES  | NULL  ← ESTA DEBE APARECER
```

---

## 🎯 Prueba del Sistema

### Test 1: Login
```
1. Abre: http://localhost/index.php
2. Email: admin@institucion.com
3. Contraseña: admin123
4. Año: 2026
5. Click "Iniciar Sesión"
```

### Test 2: Crear certificado
```
1. Menú: Certificados → Crear
2. Rellena los datos (cualquier dato)
3. Click "Guardar"
4. Deberías ver: "Certificado creado exitosamente"
```

### Test 3: Verificar que aparece
```
1. Menú: Certificados → Ver
2. Deberías ver el certificado que creaste
```

### Test 4: Cambiar año
```
1. En la navbar superior, ve el selector: 📅 [2026▼]
2. Haz clic y selecciona 2025
```

### Test 5: Verificar el filtro
```
1. Menú: Certificados → Ver
2. ❌ El certificado NO debe aparecer (porque es de 2026)
3. Esto significa: ✅ El filtro funciona correctamente
```

### Test 6: Volver a 2026
```
1. Selector: 📅 [2025▼] → Cambia a 2026
2. Menú: Certificados → Ver
3. ✅ El certificado aparece de nuevo
```

---

## 📊 Resultado Esperado

### ✅ SI FUNCIONA:
```
Año 2026:
- Ver certificado: SÍ ✓
- Crear certificado: SÍ ✓
- Editar certificado: SÍ ✓

Año 2025:
- Ver certificado: NO ✗ (correcto, es de 2026)
- Crear certificado: SÍ ✓
- Editar certificado: SÍ ✓
```

### ❌ SI NO FUNCIONA (Posibles causas):
```
1. No ejecutaste el SQL
   → Solución: Ejecuta PASO 2️⃣

2. El SQL falló
   → Solución: Verifica si la columna existe
   → Query: DESC certificados;
   
3. Viste datos del año anterior
   → Solución: Limpia caché (Ctrl+Shift+Del)
   → O cierra el navegador y abre de nuevo
   
4. El selector de año no aparece
   → Solución: Recarga la página (Ctrl+F5)
```

---

## 📞 Soporte Rápido

**P: ¿Qué es el SQL?**
R: Un script que agrega columnas a tu base de datos.

**P: ¿Puedo borrarlo después?**
R: No, debes mantenerlo. Sin él no funciona el filtro.

**P: ¿Cuánto tarda?**
R: Menos de 1 segundo.

**P: ¿Es seguro?**
R: Completamente seguro. Solo agrega datos, no borra nada.

**P: ¿Qué pasa con los datos existentes?**
R: Se actualizan con el año actual. No se pierde nada.

---

## 🎯 Línea de Tiempo

```
⏱️ 1 minuto   → Abrir archivo SQL
⏱️ 2 minutos  → Ir a phpmyadmin
⏱️ 3 minutos  → Ejecutar SQL
⏱️ 4 minutos  → Reiniciar navegador
⏱️ 5 minutos  → ✅ ¡LISTO! El filtro funciona
```

---

## 🚨 ATENCIÓN

Si NO ejecutas el SQL:
- ❌ Error: "Unknown column 'año' in where clause"
- ❌ El filtro de año no funciona
- ❌ Los datos no se aislán por año

Una vez ejecutes el SQL:
- ✅ El error desaparece
- ✅ El filtro funciona perfectamente
- ✅ Cada usuario ve solo sus datos del año

---

## 📍 Ubicación de Archivos

```
Sistema Principal:
c:\xampp\htdocs\programas\certificados-sistema\

Archivo SQL (NECESARIO):
c:\xampp\htdocs\programas\certificados-sistema\database\add_year_column.sql

Documentación:
c:\xampp\htdocs\programas\certificados-sistema\INICIO_RAPIDO.md
c:\xampp\htdocs\programas\certificados-sistema\EJECUTAR_SQL_PRIMERO.md
c:\xampp\htdocs\programas\certificados-sistema\FILTRO_COMPLETO_LISTO.md
```

---

## ✅ RESUMEN

| Item | Estado | Acción |
|------|--------|--------|
| Selector de año | ✅ Listo | Usa normalmente |
| Cambio de año | ✅ Listo | Usa normalmente |
| Modelos filtran | ✅ Listo | Usa normalmente |
| SQL para columnas | ⏳ PENDIENTE | **Ejecuta ahora** |
| Filtro funcionando | ❌ Espera SQL | Funciona después |

---

## 🎬 ACCIÓN INMEDIATA

**TÚ DEBES HACER:**
1. Abre `database\add_year_column.sql`
2. Copia TODO (Ctrl+A, Ctrl+C)
3. Ve a http://localhost/phpmyadmin
4. Pestaña SQL → Pega (Ctrl+V)
5. Click Ejecutar
6. ✅ ¡LISTO!

**TIEMPO:** 3 minutos ⏰

---

**¿Lista? ¡Ejecuta el SQL y disfruta del filtro de año!**
