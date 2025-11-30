# 👨‍💻 Guía de Desarrollo

Información para desarrolladores que quieran contribuir o trabajar con este proyecto.

## 🔧 Configuración del Entorno

### Requisitos Mínimos
- PHP 7.4+ (recomendado 8.0+)
- PostgreSQL 12+ o MySQL 5.7+
- Apache/Nginx con mod_rewrite
- Git
- Composer (opcional)

### Instalación Local

1. Clonar el proyecto
```bash
git clone https://github.com/TU_USUARIO/certificados-sistema.git
cd certificados-sistema
```

2. Configurar credenciales de BD en `app/config.php`

3. Crear base de datos
```bash
# PostgreSQL
psql -U postgres -c "CREATE DATABASE certificados_sistema;"
psql -U postgres -d certificados_sistema < database/schema_postgresql.sql

# MySQL
mysql -u root < database/schema_postgresql.sql
# (O usar estructura_presupuestaria.sql)
```

4. Configurar permiso de directorios (Linux/Mac)
```bash
chmod -R 755 app/views
chmod -R 755 public
```

## 📁 Estructura MVC

```
app/
├── controllers/          # Lógica de negocio
├── models/              # Acceso a datos
├── views/               # Interfaz
└── helpers/             # Funciones utilitarias
```

## 🔄 Flujo de Trabajo

### 1. Crear una rama para tu feature
```bash
git checkout -b feature/mi-feature
```

### 2. Hacer cambios y commits
```bash
git add .
git commit -m "type: Descripción clara del cambio"
```

### Tipos de commits recomendados:
- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `style:` Cambios de formato (sin cambiar lógica)
- `refactor:` Refactorización de código
- `test:` Agregar tests
- `chore:` Cambios en build/config

### 3. Hacer push a GitHub
```bash
git push origin feature/mi-feature
```

### 4. Crear Pull Request
- Ve a GitHub
- GitHub te sugerirá crear un PR
- Describe los cambios realizados
- Espera revisión

## 📝 Convenciones de Código

### PHP
```php
// Usar PSR-12
namespace App\Controllers;

class MiControlador {
    public function miMetodo() {
        // Código aquí
    }
}
```

### JavaScript
```javascript
// Usar funciones flecha modernas
const miFunction = (param) => {
    console.log(param);
};

// Evitar vars, usar const/let
const miVariable = "valor";
```

### CSS
```css
/* BEM naming convention */
.componente {
    color: #333;
}

.componente__elemento {
    color: #666;
}

.componente--modificador {
    color: #999;
}
```

## 🧪 Pruebas

### Archivos de prueba incluidos
```
test_certificate.php      # Prueba de certificados
test_create.php           # Prueba de creación
test_simple.php           # Prueba simple
```

### Ejecutar pruebas
```bash
php test_certificate.php
php test_create.php
```

## 📊 Debugging

### Habilitar modo debug
Editar `app/config.php`:
```php
define('DEBUG_MODE', true);
```

### Ver logs
```
http://localhost/programas/certificados-sistema/view_logs.php
```

### Inspeccionar BD
```bash
php inspect_database.php
php check_schema.php
```

## 🔐 Seguridad

### Prácticas recomendadas
- Siempre validar entrada del usuario
- Usar `htmlspecialchars()` para salida
- Usar prepared statements para SQL
- No guardar credenciales en Git (usar .env)
- Revisar OWASP Top 10

### Checklist de seguridad
- [ ] Sin credenciales en código
- [ ] SQL Injection protegido
- [ ] XSS protegido
- [ ] CSRF tokens implementados
- [ ] Validación servidor-side

## 🚀 Deployment

### Pasos para producción
1. Crear rama `release-X.X.X`
2. Actualizar versión en documentación
3. Crear tag: `git tag -a vX.X.X -m "Version X.X.X"`
4. Hacer push: `git push origin --tags`

### Configuración de producción
```php
// app/config.php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('DB_HOST', 'db-produccion.example.com');
```

## 📚 Recursos Útiles

- [PSR-12: PHP Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.0/)
- [Git Documentation](https://git-scm.com/docs)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

## 🤝 Contribuir

1. Fork el proyecto
2. Crea tu rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

- Issues: [GitHub Issues](https://github.com/TU_USUARIO/certificados-sistema/issues)
- Discussions: [GitHub Discussions](https://github.com/TU_USUARIO/certificados-sistema/discussions)

---

¡Gracias por contribuir! 🎉
