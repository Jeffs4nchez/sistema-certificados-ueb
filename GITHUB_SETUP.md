# 📤 Guía: Subir a GitHub

Tu repositorio Git local está **listo**. Aquí están los pasos finales para subirlo a GitHub.

## 📋 Pasos

### 1. Crear un repositorio en GitHub

1. Inicia sesión en [GitHub](https://github.com)
2. Haz clic en el **+** en la esquina superior derecha
3. Selecciona **New repository**
4. **Nombre del repositorio:** `certificados-sistema` (o el que prefieras)
5. **Descripción:** `Sistema web para gestión de certificados presupuestarios`
6. Selecciona **Public** (si quieres que sea público) o **Private** (si es privado)
7. **NO** inicialices con README (ya lo tenemos)
8. Haz clic en **Create repository**

### 2. Agregar el remote (origen remoto)

Después de crear el repositorio, GitHub te mostrará comandos. Ejecuta esto en tu terminal:

```bash
cd "c:\xampp\htdocs\programas\certificados-sistema"
git remote add origin https://github.com/TU_USUARIO/certificados-sistema.git
```

**Nota:** Reemplaza `TU_USUARIO` con tu nombre de usuario de GitHub.

### 3. Renombrar la rama (si es necesario)

Si tu rama principal se llama `master`, cambia a `main`:

```bash
git branch -M main
```

### 4. Subir el código a GitHub

```bash
git push -u origin main
```

O si tu rama es `master`:

```bash
git push -u origin master
```

### 5. Verificar

Abre tu repositorio en GitHub y verifica que todo esté ahí:
```
https://github.com/TU_USUARIO/certificados-sistema
```

---

## 🔑 Autenticación con GitHub

### Si usas HTTPS:
- GitHub te pedirá tus credenciales
- Si tienes 2FA activado, debes usar un **Personal Access Token** en lugar de contraseña
- [Crear Personal Access Token](https://github.com/settings/tokens)

### Si usas SSH (recomendado):
```bash
# Generar clave SSH
ssh-keygen -t ed25519 -C "tu-email@gmail.com"

# En Windows, puedes usar:
# ssh-keygen -t rsa -b 4096 -C "tu-email@gmail.com"

# Agregar tu clave pública a GitHub
# GitHub Settings > SSH and GPG keys > New SSH key
```

---

## 📊 Estado Actual

✅ Repositorio Git inicializado  
✅ `.gitignore` configurado  
✅ `README.md` completado  
✅ `LICENSE` agregado  
✅ Primer commit realizado  

**Commit:** `b2abde3` - Initial commit: Sistema de Gestión de Certificados Presupuestarios v1.0  
**Archivos:** 86 archivos agregados  
**Líneas de código:** 11,286 líneas

---

## 🎯 Próximos Pasos (Opcional)

- Crear ramas por feature: `git checkout -b feature/nombre-feature`
- Agregar colaboradores en GitHub Settings
- Configurar GitHub Pages para documentación
- Activar GitHub Actions para CI/CD

---

¿Necesitas ayuda con algo? 🚀
