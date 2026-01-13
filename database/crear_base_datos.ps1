#!/usr/bin/env powershell
# =====================================================
# SCRIPT DE CREACIÓN DE BASE DE DATOS
# Sistema de Gestión de Certificados y Liquidaciones
# Fecha: 2026-01-13
# Versión: v1.3
# =====================================================

Write-Host "========================================================" -ForegroundColor Cyan       
Write-Host "   CREADOR DE BASE DE DATOS - CERTIFICADOS Y LIQUIDACIONES  " -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan       
Write-Host ""

# Variables de configuración
$dbName = "certificados_sistema"
$dbUser = "postgres"
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$sqlFile = Join-Path $scriptPath "schema_produccion.sql"

Write-Host "Información de la Base de Datos:" -ForegroundColor Yellow
Write-Host "   - Nombre: $dbName"
Write-Host "   - Usuario: $dbUser"
Write-Host "   - Ruta del Script: $sqlFile"
Write-Host ""

# Verificar que el archivo SQL existe
if (-not (Test-Path $sqlFile)) {
    Write-Host "ERROR: No se encontró el archivo '$sqlFile'" -ForegroundColor Red
    Write-Host "   Asegúrate de que schema_produccion.sql existe en la carpeta 'database'" -ForegroundColor Red  
    exit 1
}

Write-Host "✓ Archivo SQL encontrado" -ForegroundColor Green
Write-Host ""

# Solicitar contraseña de postgres
Write-Host "Ingresa la contraseña de PostgreSQL (usuario 'postgres'):" -ForegroundColor Yellow
$password = Read-Host -AsSecureString

# Convertir a texto plano para psql
$secureString = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($password))

# Establecer variable de entorno para psql
$env:PGPASSWORD = $secureString

Write-Host ""
Write-Host "Creando base de datos '$dbName'..." -ForegroundColor Yellow

# Crear la base de datos
try {
    psql -U $dbUser -h localhost -c "DROP DATABASE IF EXISTS $dbName;" 2>$null | Out-Null
    psql -U $dbUser -h localhost -c "CREATE DATABASE $dbName;" 2>$null | Out-Null
    
    Write-Host "✓ Base de datos creada" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ejecutando script SQL..." -ForegroundColor Yellow
    
    # Ejecutar el script SQL
    psql -U $dbUser -h localhost -d $dbName -f $sqlFile
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ Base de datos creada exitosamente" -ForegroundColor Green
        Write-Host "========================================================" -ForegroundColor Cyan
        Write-Host "La base de datos '$dbName' está lista para usar" -ForegroundColor Green
        Write-Host "========================================================" -ForegroundColor Cyan
    } else {
        Write-Host "ERROR: Ocurrió un error al ejecutar el script SQL" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: $_" -ForegroundColor Red
    exit 1
} finally {
    # Limpiar la variable de entorno
    Remove-Item env:PGPASSWORD -ErrorAction SilentlyContinue
}
