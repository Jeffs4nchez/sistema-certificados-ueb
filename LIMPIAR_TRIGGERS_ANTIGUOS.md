# Limpieza de Triggers Antiguos - Instrucciones

## Status Actual ✅
Los 3 triggers nuevos **ya están instalados** en tu base de datos:
- ✅ trigger_certificados_actualiza_col4
- ✅ trigger_liquidaciones_actualiza_col4  
- ✅ trigger_col4_recalcula_saldo

## Problema
Hay triggers antiguos que impiden la limpieza completa:
- ❌ trg_sync_col4_on_insert (y otros)

## Solución

### Opción 1: Ejecutar en phpMyAdmin (Recomendado)

1. Abre phpMyAdmin
2. Selecciona la base de datos `certificados_ueb`
3. Ve a la pestaña "SQL"
4. Copia y pega el siguiente código:

```sql
-- Eliminar triggers antiguos
DROP TRIGGER IF EXISTS trg_sync_col4_on_insert;
DROP TRIGGER IF EXISTS trg_sync_col4_on_update;
DROP TRIGGER IF EXISTS trg_sync_col4_on_delete;
DROP TRIGGER IF EXISTS trigger_insert_detalle_certificados;
DROP TRIGGER IF EXISTS trigger_update_liquidacion;
DROP TRIGGER IF EXISTS trigger_recalculate_saldo_disponible;
DROP TRIGGER IF EXISTS trigger_delete_detalle_certificados;

-- Eliminar funciones antiguas
DROP FUNCTION IF EXISTS trg_sync_col4_on_insert;
DROP FUNCTION IF EXISTS trg_sync_col4_on_update;
DROP FUNCTION IF EXISTS trg_sync_col4_on_delete;
DROP FUNCTION IF EXISTS trigger_insert_detalle_certificados;
DROP FUNCTION IF EXISTS trigger_update_liquidacion;
DROP FUNCTION IF EXISTS trigger_recalculate_saldo_disponible;
DROP FUNCTION IF EXISTS trigger_delete_detalle_certificados;
```

5. Haz clic en "Ejecutar"

### Opción 2: Ejecutar con MySQL en terminal

```bash
mysql -u root -p certificados_ueb < database/create_triggers.sql
```

## Verificación

Después de ejecutar, ve a phpMyAdmin y ejecuta:

```sql
SELECT trigger_name, event_object_table 
FROM information_schema.triggers 
WHERE trigger_schema = 'certificados_ueb' 
ORDER BY event_object_table;
```

Deberías ver solo estos 3:
- trigger_certificados_actualiza_col4 (certificados)
- trigger_liquidaciones_actualiza_col4 (detalle_certificados)
- trigger_col4_recalcula_saldo (presupuesto_items)

✅ Listo!
