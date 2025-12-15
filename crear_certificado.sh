#!/bin/bash
# Script para crear certificado de prueba
cd /c/xampp/htdocs/programas/certificados-sistema
php -d error_reporting=E_ALL -d display_errors=1 database/crear_cert_prueba.php
