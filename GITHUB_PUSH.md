# 🚀 Subir a GitHub - Repositorio sistema-certificados-ueb

Tu repositorio local está completamente listo. Aquí están los pasos finales y definitivos.

## 📋 PASO 1: Crear Repositorio en GitHub

### En tu navegador:
1. Accede a: https://github.com/new
2. **Repository name:** `sistema-certificados-ueb`
3. **Description:** `Sistema web para gestión de certificados presupuestarios - UEB`
4. Selecciona:
   - ✅ Public (si quieres que sea público)
   - ⭕ Private (si es privado)
5. **IMPORTANTE:** NO marques "Initialize this repository with:"
6. Haz clic en **"Create repository"**

---

## 📋 PASO 2: Configurar Git Localmente

Después de crear el repositorio en GitHub, ejecuta estos comandos EN TU TERMINAL:

```bash
cd "c:\xampp\htdocs\programas\certificados-sistema"

# Agregar el repositorio remoto (reemplaza TU_USUARIO con tu nombre de usuario GitHub)
git remote add origin https://github.com/TU_USUARIO/sistema-certificados-ueb.git

# Cambiar de rama master a main (recomendado)
git branch -M main

# Subir todo el código a GitHub
git push -u origin main
```

---

## 🔐 SI TIENES 2FA ACTIVADO EN GITHUB

Si GitHub te pide autenticación y tienes 2FA:

1. En lugar de tu contraseña, usa un **Personal Access Token**
2. Crea uno aquí: https://github.com/settings/tokens
3. Selecciona permisos: `repo` (acceso completo a repos)
4. Copia el token generado
5. Pégalo cuando GitHub te pida la contraseña

---

## 🔑 ALTERNATIVA: Usar SSH (Más Seguro)

Si prefieres SSH en lugar de HTTPS:

```bash
# Generar clave SSH (si no la tienes)
ssh-keygen -t ed25519 -C "tu-email@gmail.com"
# (Presiona Enter para todas las preguntas)

# Agregar la clave SSH a GitHub
# GitHub Settings > SSH and GPG keys > New SSH key
# Copia el contenido de: C:\Users\TU_USUARIO\.ssh\id_ed25519.pub

# Luego usa este comando:
git remote add origin git@github.com:TU_USUARIO/sistema-certificados-ueb.git
git branch -M main
git push -u origin main
```

---

## ✅ VERIFICAR QUE TODO ESTÉ CORRECTO

Después de hacer push, verifica:

1. Abre https://github.com/TU_USUARIO/sistema-certificados-ueb
2. Deberías ver:
   - ✅ 89 archivos
   - ✅ 3 commits
   - ✅ README.md mostrado
   - ✅ Descripción del proyecto

---

## 📊 ESTADO ACTUAL DEL REPOSITORIO LOCAL

```
Rama: master (será renombrada a main al hacer push)
Commits: 3
Archivos: 89
Tamaño: ~11 MB

Commits realizados:
✓ 9b32720 - Add development guide
✓ 99fbcb9 - Add GitHub setup guide and gitattributes
✓ b2abde3 - Initial commit: Sistema de Gestión de Certificados Presupuestarios v1.0
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### "Permission denied (publickey)"
- Asegúrate que tienes SSH key configurada
- O usa HTTPS en lugar de SSH

### "fatal: remote origin already exists"
```bash
git remote rm origin
# Luego intenta agregar el nuevo remote
```

### "fatal: The remote end hung up unexpectedly"
- Puede ser problema de conexión
- Intenta nuevamente en unos momentos

### "fatal: You are not currently on a branch"
```bash
git checkout -b main
git push -u origin main
```

---

## 🎉 ¡LISTO!

Una vez hayas subido el código a GitHub, tendrás:

✅ Repositorio público/privado según tu elección  
✅ Código versionado en GitHub  
✅ Acceso desde cualquier dispositivo  
✅ Posibilidad de compartir con colaboradores  
✅ Historial de cambios guardado  
✅ Backup automático en la nube  

---

## 📞 PRÓXIMOS PASOS

1. **Agregar colaboradores** (GitHub Settings > Collaborators)
2. **Configurar protección de rama** (Settings > Branches)
3. **Activar GitHub Pages** (Settings > Pages) para documentación
4. **Crear releases** (Releases > Create a new release) para versiones

---

¿Tienes dudas? Revisa:
- GITHUB_SETUP.md - Guía general GitHub
- DEVELOPMENT.md - Guía de desarrollo

¡Buena suerte! 🚀
