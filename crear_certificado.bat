@echo off
REM Script para crear el certificado de prueba directamente en PostgreSQL
REM Requiere que psql esté en el PATH o PostgreSQL esté instalado

echo Conectando a PostgreSQL...
echo.

REM Ejecutar el SQL directamente
set PGPASSWORD=jeffo2003

REM Intenta ejecutar psql (busca en rutas comunes de PostgreSQL)
for %%G in ("C:\Program Files\PostgreSQL\*" "C:\Program Files (x86)\PostgreSQL\*" "%PROGRAMFILES%\PostgreSQL\*") do (
    if exist "%%G\bin\psql.exe" (
        echo Encontrado PostgreSQL en: %%G
        "%%G\bin\psql.exe" -U postgres -d certificados_sistema -h localhost -f "%CD%\database\crear_certificados_prueba.sql"
        goto end
    )
)

echo.
echo ERROR: No se encontró PostgreSQL instalado
echo Por favor asegúrate de que PostgreSQL esté instalado y accesible

:end
pause
