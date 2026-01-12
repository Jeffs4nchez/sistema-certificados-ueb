#!/usr/bin/env powershell
# =====================================================
# SCRIPT DE CREACIÓN DE BASE DE DATOS
# Sistema de Gestión de Certificados y Liquidaciones
# Fecha: 2026-01-12
# =====================================================

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   CREADOR DE BASE DE DATOS - CERTIFICADOS Y LIQUIDACIONES  ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Variables de configuración
$dbName = "certificados_sistema"
$dbUser = "postgres"
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$sqlFile = Join-Path $scriptPath "schema_produccion.sql"

Write-Host "📋 Información de la Base de Datos:" -ForegroundColor Yellow
Write-Host "   - Nombre: $dbName"
Write-Host "   - Usuario: $dbUser"
Write-Host "   - Ruta del Script: $sqlFile"
Write-Host ""

# Verificar que el archivo SQL existe
if (-not (Test-Path $sqlFile)) {
    Write-Host "❌ ERROR: No se encontró el archivo '$sqlFile'" -ForegroundColor Red
    Write-Host "   Asegúrate de que schema_produccion.sql existe en la carpeta 'database'" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Archivo SQL encontrado" -ForegroundColor Green
Write-Host ""

# Solicitar contraseña de postgres
Write-Host "🔐 Ingresa la contraseña de PostgreSQL (usuario 'postgres'):" -ForegroundColor Yellow
$password = Read-Host -AsSecureString

# Convertir a texto plano para psql
$secureString = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($password))

# Establecer variable de entorno para psql
$env:PGPASSWORD = $secureString

Write-Host ""
Write-Host "⏳ Creando base de datos '$dbName'..." -ForegroundColor Yellow

# Crear la base de datos
try {
    psql -U $dbUser -h localhost -c "DROP DATABASE IF EXISTS $dbName;" 2>$null | Out-Null
    $createOutput = psql -U $dbUser -h localhost -c "CREATE DATABASE $dbName ENCODING 'UTF8';" 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "⚠️  Aviso: $createOutput" -ForegroundColor Yellow
    } else {
        Write-Host "✅ Base de datos creada exitosamente" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ ERROR al crear la base de datos: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "⏳ Ejecutando script SQL (esto puede tomar un momento)..." -ForegroundColor Yellow

# Ejecutar el script SQL
try {
    $sqlOutput = psql -U $dbUser -h localhost -d $dbName -f $sqlFile 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ ERROR al ejecutar el script SQL:" -ForegroundColor Red
        Write-Host $sqlOutput -ForegroundColor Red
        exit 1
    } else {
        Write-Host "✅ Script SQL ejecutado exitosamente" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ ERROR: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "⏳ Verificando tablas creadas..." -ForegroundColor Yellow

# Verificar tablas
try {
    $tables = psql -U $dbUser -h localhost -d $dbName -c "SELECT tablename FROM pg_tables WHERE schemaname = 'public';" 2>&1
    Write-Host "✅ Tablas creadas:" -ForegroundColor Green
    $tables | Select-String "^\s*\w+" | ForEach-Object {
        $tableName = $_.Line.Trim()
        if ($tableName -and $tableName -notmatch "^(tablename|-|$)") {
            Write-Host "   • $tableName" -ForegroundColor Green
        }
    }
} catch {
    Write-Host "⚠️  No se pudo verificar tablas" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "⏳ Verificando funciones y triggers..." -ForegroundColor Yellow

# Verificar funciones
try {
    $functions = psql -U $dbUser -h localhost -d $dbName -c "SELECT proname FROM pg_proc WHERE pronamespace = 2200;" 2>&1
    Write-Host "✅ Funciones creadas:" -ForegroundColor Green
    $functions | Select-String "tr_liquidaciones" | ForEach-Object {
        Write-Host "   • $($_.Line.Trim())" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  No se pudo verificar funciones" -ForegroundColor Yellow
}

# Limpiar variable de entorno
Remove-Item env:PGPASSWORD -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║              ✅ BASE DE DATOS CREADA EXITOSAMENTE          ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host ""
Write-Host "📊 Información de Conexión:" -ForegroundColor Cyan
Write-Host "   Host: localhost"
Write-Host "   Puerto: 5432"
Write-Host "   Base de Datos: $dbName"
Write-Host "   Usuario: $dbUser"
Write-Host ""

Write-Host "🔧 Próximos pasos:" -ForegroundColor Yellow
Write-Host "   1. Actualiza config.php con los datos de conexión"
Write-Host "   2. Ingresa usuarios administradores a través de la interfaz"
Write-Host "   3. Importa presupuesto mediante el formulario de carga"
Write-Host ""

Write-Host "Presiona cualquier tecla para cerrar..." -ForegroundColor Gray
[Console]::ReadKey() | Out-Null
