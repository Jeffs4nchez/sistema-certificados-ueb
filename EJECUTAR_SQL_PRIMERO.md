# ⚠️ IMPORTANTE: Ejecutar Script SQL para agregar columna de AÑO

## Problema que se soluciona

Los datos NO se filtraban por año porque:
- ❌ No había columna `año` en las tablas
- ❌ Los modelos no filtraban por año
- ❌ Los controladores no pasaban el año al modelo

## Solución implementada

✅ Ahora:
1. Se agregó columna `año` en tablas principales
2. Modelos filtran por año
3. Controladores pasan el año al modelo
4. Los datos se crean con el año actual de la sesión

---

## 📋 Qué ejecutar

### Opción 1: Ejecutar SQL en phpMyAdmin/pgAdmin

1. Abre: `http://localhost/phpmyadmin` (o pgAdmin si usas PostgreSQL)
2. Selecciona tu base de datos
3. Copia y pega el contenido de **database/add_year_column.sql**
4. Ejecuta (Ctrl+Enter o botón Execute)

### Opción 2: Ejecutar desde la terminal

```bash
# Para PostgreSQL
psql -U usuario -d nombre_bd < database/add_year_column.sql

# Para MySQL
mysql -u usuario -p nombre_bd < database/add_year_column.sql
```

---

## 📝 Lo que hace el script

```sql
-- 1. Agrega columna año a certificados
ALTER TABLE certificados ADD COLUMN año INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

-- 2. Crea índice para mejor performance
CREATE INDEX idx_certificados_año ON certificados(año);

-- 3. Agrega columna año a detalle_certificados
ALTER TABLE detalle_certificados ADD COLUMN año INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

-- ... y lo mismo para presupuesto_items
```

---

## ✅ Verificar que funcionó

1. Ejecuta esta query:
```sql
SELECT COUNT(*) as total, año FROM certificados GROUP BY año;
```

2. Deberías ver:
```
total | año
------+------
  10  | 2026
   5  | 2025
   8  | 2024
```

---

## 🔧 Cambios en el código

### Certificate.php (Modelo)
✅ Agregado `getAllByYear($año)` - Obtiene certificados por año
✅ Agregado `getByUsuarioAndYear($usuario_id, $año)` - Por usuario Y año
✅ Actualizado `createCertificate()` - Guarda el año automáticamente

### CertificateController.php (Controlador)
✅ Actualizado `listAction()` - Filtra por año actual de la sesión

---

## 🧪 Prueba que todo funciona

1. **Iniciar sesión** con año 2026
2. **Crear un certificado** (se guardará con año 2026)
3. **Cambiar a año 2025** en la navbar
4. **Ver lista de certificados**
   - ❌ NO debe aparecer el certificado que acaba de crear
   - ✅ Solo mostrará certificados de 2025
5. **Volver a cambiar a 2026**
   - ✅ Ahora SÍ aparece el certificado creado

---

## 🚨 Si hay error

Si obtienes error de que la columna `año` ya existe:
```
ERROR: column "año" of relation "certificados" already exists
```

Es normal, significa que ya está agregada. Puedes ignorar el error.

---

## ⏭️ Próximas tablas a actualizar (Opcional)

Si tienes otras tablas con datos filtrados por año:

1. **Presupuesto**: Igual proceso
2. **Liquidaciones**: Igual proceso
3. **Importaciones**: Igual proceso

---

## 📞 Soporte

Si algo no funciona después de ejecutar el SQL:

1. Verifica que la columna se agregó:
   ```sql
   DESCRIBE certificados;  -- o
   \d certificados;  -- en PostgreSQL
   ```

2. Verifica que los datos se actualizaron:
   ```sql
   SELECT * FROM certificados WHERE año IS NULL;  -- No debería retornar nada
   ```

3. Reinicia la sesión (logout y login) para que el cambio se note

---

**¡IMPORTANTE!** Ejecuta el SQL ANTES de usar el sistema, de lo contrario obtendrás error al filtrar por año.

Una vez ejecutado, todo debe funcionar correctamente.
