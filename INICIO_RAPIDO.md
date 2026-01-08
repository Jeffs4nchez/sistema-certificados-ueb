# 🎯 GUÍA RÁPIDA: Activar Filtro de Año (5 Pasos)

## Situación Actual

✅ **Ya implementado:**
- Selector de año en login
- Cambio de año en navbar
- Modelos listos para filtrar
- Controladores listos para usar el año

❌ **Falta un paso:**
- Ejecutar SQL para agregar columna `año` en BD

---

## 📋 5 Pasos para Activar

### PASO 1: Abre el archivo SQL
```
Ruta: c:\xampp\htdocs\programas\certificados-sistema\database\add_year_column.sql
```

Contiene:
```sql
ALTER TABLE certificados ADD COLUMN año INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);
CREATE INDEX idx_certificados_año ON certificados(año);
-- ... más líneas
```

---

### PASO 2: Copia TODO el contenido del archivo

Selecciona todo (Ctrl+A) y copia (Ctrl+C)

---

### PASO 3: Abre tu gestor de BD

**Si usas MySQL/MariaDB:**
- Abre: `http://localhost/phpmyadmin`
- Selecciona tu BD

**Si usas PostgreSQL:**
- Abre: `http://localhost/pgadmin`
- Selecciona tu BD

---

### PASO 4: Pega el SQL en la consola

1. Haz clic en la pestaña **"SQL"** o **"Consulta"**
2. Pega el contenido (Ctrl+V)
3. Haz clic en **"Ejecutar"** (o Ctrl+Enter)

**Resultado esperado:**
```
✓ Query OK
✓ Columna creada
✓ Índice creado
```

---

### PASO 5: Reinicia la sesión

1. **Logout** (Cerrar sesión)
2. **Login** de nuevo
3. Intenta el flujo de año

---

## ✅ Verificar que funcionó

### En la BD:

Ejecuta esta query:
```sql
SELECT * FROM certificados LIMIT 1;
```

Deberías ver una columna nueva: `año`

```
id | numero_certificado | ... | año
---+--------------------+-----+-----
 1 | CERT-001          |     |2026
```

### En el Sistema:

1. **Login** con año 2026
2. **Crea un certificado** (cualquiera)
3. **Cambia a año 2025** en la navbar
4. **Abre lista de certificados**
   - El certificado que creaste NO debe aparecer

5. **Vuelve a 2026**
   - Ahora SÍ aparece

**Si esto funciona:** ✅ **¡TODO ESTÁ LISTO!**

---

## 🚨 Si hay error

### Error 1: "Columna ya existe"
```
ERROR: column "año" of relation "certificados" already exists
```

**Solución:** Es normal, significa que ya está. Continúa normalmente.

### Error 2: "Síntaxis incorrecta"
```
ERROR: syntax error at "ALTER"
```

**Solución:**
- Verifica que hayas copiado todo el SQL correctamente
- Busca líneas comentadas (#, --)
- Intenta ejecutar línea por línea

### Error 3: "No se filtra por año"
```
Cambio de año pero veo los mismos datos
```

**Solución:**
1. Verifica que la columna `año` exista en la BD
2. Reinicia la sesión (logout/login)
3. Limpia caché del navegador (Ctrl+F5)
4. Verifica en el código:
   - Certificate.php tiene `getAllByYear()`?
   - CertificateController usa `getAllByYear()`?

---

## 🔄 El Flujo (Para entender)

```
1. Usuario hace Login
   ↓ Selecciona AÑO 2026
   ↓ Se guarda en $_SESSION['año_trabajo'] = 2026

2. Usuario crea Certificado
   ↓ Certificate::createCertificate() obtiene año de sesión
   ↓ INSERT INTO certificados VALUES (..., año=2026)
   ↓ Se guarda en BD CON el año

3. Usuario ve lista de Certificados
   ↓ CertificateController::listAction() obtiene año = 2026
   ↓ Llama a Certificate::getAllByYear(2026)
   ↓ SELECT * FROM certificados WHERE año = 2026
   ↓ Solo ve datos de 2026

4. Usuario cambia AÑO en navbar a 2025
   ↓ $_SESSION['año_trabajo'] = 2025
   ↓ Página se recarga

5. Usuario ve lista de Certificados
   ↓ Ahora obtiene año = 2025
   ↓ SELECT * FROM certificados WHERE año = 2025
   ↓ Solo ve datos de 2025
```

---

## 📊 Antes vs Después

### ❌ ANTES (Sin filtro)
```
Login año 2026
    ↓
Ver certificados
    ↓
Muestra:
- Cert de 2026 ✓
- Cert de 2025 ✗ (No debería)
- Cert de 2024 ✗ (No debería)
```

### ✅ DESPUÉS (Con filtro)
```
Login año 2026
    ↓
Ver certificados
    ↓
Muestra:
- Cert de 2026 ✓
- Cert de 2025 ✗
- Cert de 2024 ✗

Cambiar a año 2025
    ↓
Ver certificados
    ↓
Muestra:
- Cert de 2026 ✗
- Cert de 2025 ✓
- Cert de 2024 ✗
```

---

## 📱 Comandos Alternativos (Si no tienes phpmyadmin)

### Vía Terminal (MySQL):
```bash
mysql -u root -p tu_bd < database/add_year_column.sql
```

### Vía Terminal (PostgreSQL):
```bash
psql -U usuario -d tu_bd -f database/add_year_column.sql
```

### Vía SQLite:
Si usas SQLite, copia las líneas relevantes y ejecuta en tu cliente SQLite.

---

## ✨ Resumen

| Paso | Acción | Resultado |
|------|--------|-----------|
| 1 | Abre archivo SQL | Ves el contenido del archivo |
| 2 | Copia el SQL | Tienes el SQL en portapapeles |
| 3 | Abre gestor BD | Ves phpmyadmin/pgadmin |
| 4 | Ejecuta SQL | Se agregan columnas de año |
| 5 | Reinicia sesión | El filtro funciona |

**Total de tiempo:** 5 minutos ⏱️

---

## 🎉 ¡Listo!

Una vez ejecutado el SQL:
- ✅ Los certificados se guardan con año
- ✅ Al cambiar año, ves datos diferentes
- ✅ El problema se resuelve

**¿Necesitas más ayuda?**

Ver:
- `FILTRO_COMPLETO_LISTO.md` - Explicación completa
- `EJECUTAR_SQL_PRIMERO.md` - Instrucciones detalladas
- `REFERENCIA_RAPIDA.md` - Referencia de código

---

**¡A ejecutar el SQL!**
