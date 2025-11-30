# Guía de Estilos Corporativos - Sistema de Gestión

## 📋 Identidad Visual

### Colores Corporativos

#### Primarios
- **Azul Corporativo 1 (Principal)**: `#001F3F` - CMYK 26/19/20/2
  - Uso: Fondo del sidebar, headers, botones primarios
  - Texto: Blanco
  
- **Azul Corporativo 2 (Secundario)**: `#0D47A1` - CMYK 100/76/39/51
  - Uso: Hover states, backgrounds secundarios
  
- **Azul Corporativo 3 (Terciario)**: `#1565C0` - CMYK 45/61/22/6
  - Uso: Botones info, enlaces

#### Acentos
- **Rojo Corporativo 1 (Principal)**: `#C1272D` - CMYK 27/100/91/31
  - Uso: Acentos, bordes, iconos importantes
  - Texto: Blanco
  
- **Rojo Corporativo 2 (Hover)**: `#E63946` - CMYK 0/100/100/0
  - Uso: Estados hover, alerts críticos

#### Neutros
- **Gris Oscuro**: `#2E3C4F` - K 90% (para textos)
- **Gris Claro**: `#F5F7FA` - Fondo principal
- **Gris Medio**: `#E8E9EB` - Bordes, separadores
- **Blanco**: `#FFFFFF` - Fondos limpios

### Tipografía

**Fuente Principal**: Open Sans (Google Fonts)
- Reemplaza a Argentum Sans (no disponible en web)
- Pesos disponibles: 300, 400, 500, 600, 700

**Uso de pesos**:
- **Títulos (H1-H6)**: 700 (Bold)
- **Labels y Headers**: 600 (Semibold)
- **Texto regular**: 400 (Regular)
- **Pequeño/Hint**: 300 (Light)

**Tamaños**:
- H1: 32px (desktop), 24px (móvil)
- H2: 24px (desktop), 20px (móvil)
- H3: 20px (desktop), 18px (móvil)
- H4: 16px
- Cuerpo: 14px
- Pequeño: 12px

## 🎨 Componentes

### Botones
```html
<!-- Primario (Azul) -->
<button class="btn btn-primary">Acción Principal</button>

<!-- Peligro (Rojo) -->
<button class="btn btn-danger">Eliminar</button>

<!-- Éxito (Verde) -->
<button class="btn btn-success">Guardar</button>

<!-- Tamaño -->
<button class="btn btn-primary btn-sm">Pequeño</button>
<button class="btn btn-primary btn-lg">Grande</button>
```

### Tarjetas
```html
<div class="card">
    <div class="card-header">Título</div>
    <div class="card-body">Contenido</div>
</div>
```

### Alertas
```html
<div class="alert alert-success">✓ Operación exitosa</div>
<div class="alert alert-danger">✗ Error en la operación</div>
<div class="alert alert-warning">⚠ Advertencia</div>
<div class="alert alert-info">ℹ Información</div>
```

### Badges
```html
<span class="badge badge-primary">Admin</span>
<span class="badge badge-danger">Activo</span>
<span class="badge badge-success">Completado</span>
```

### Tablas
- Header: Fondo Azul (#001F3F), texto blanco
- Filas alternas: Hover con fondo Azul translúcido
- Bordes: Gris medio

### Formularios
- Inputs: Border gris medio, radius 6px
- Focus: Border Azul oscuro, shadow azul translúcido
- Labels: Peso 600, color azul oscuro

## 📱 Responsive Design

### Breakpoints
- **Desktop**: 1024px+
- **Tablet**: 768px - 1023px
- **Mobile**: < 768px

### Sidebar
- **Desktop**: 280px fijo a la izquierda
- **Tablet**: 250px colapsable
- **Mobile**: 70px con drawer slide-in

### Layout
- **Desktop**: Sidebar fijo + contenido main
- **Tablet**: Sidebar colapsable (menu hamburguesa)
- **Mobile**: Sidebar como drawer (oculto por defecto)

## 🖥️ Archivos Principales

### Estilos
- `public/css/style.css` - Estilos globales corporativos
- `app/views/layout/sidebar.php` - CSS del sidebar embebido

### Scripts
- `public/js/main.js` - Interactividad general
- Funciones clave:
  - `toggleSidebar()` - Alternar colapso
  - `toggleSidebarMobile()` - Drawer móvil
  - `showNotification()` - Notificaciones elegantes

### Layout
- `app/views/layout/sidebar.php` - Header + Sidebar
- `app/views/layout/sidebar-footer.php` - Footer + Cierre
- Todas las vistas van entre estos dos archivos

## 🔧 Personalización

### Variables CSS
En `sidebar.php` se definen como variables CSS (`:root`):
```css
:root {
    --azul-1: #001F3F;
    --azul-2: #0D47A1;
    --azul-3: #1565C0;
    --rojo-1: #C1272D;
    --rojo-2: #E63946;
    --gris-oscuro: #2E3C4F;
    --gris-claro: #F5F7FA;
    --blanco: #FFFFFF;
}
```

Uso: `color: var(--azul-1);`

### Tema
- **Color primario**: Azul #001F3F
- **Color de acento**: Rojo #C1272D
- **Modo**: Light (fondo claro)

## ✅ Checklist de Consistencia

- [ ] Todos los botones usan `btn btn-primary/danger/success`
- [ ] Títulos usan h1-h6 para SEO y estructura
- [ ] Colores solo de la paleta corporativa
- [ ] Tipografía Open Sans en todos lados
- [ ] Espaciado consistente (múltiplos de 5px)
- [ ] Responsive funciona en 320px+
- [ ] Sidebar se colapsa en móvil
- [ ] Contraste WCAG AA mínimo
- [ ] Animaciones suaves (0.3s)
- [ ] Iconos Font Awesome consistentes

## 🚀 Próximas Mejoras Posibles

1. Modo oscuro (dark theme)
2. Selector de temas
3. Accesibilidad mejorada (WCAG AAA)
4. Animaciones avanzadas
5. Layouts alternativos
6. Sistema de notificaciones mejorado
7. Modales y popovers personalizados
8. Micro-interacciones

---

**Última actualización**: Noviembre 2024
**Versión**: 1.0
**Autor**: Sistema Corporativo
