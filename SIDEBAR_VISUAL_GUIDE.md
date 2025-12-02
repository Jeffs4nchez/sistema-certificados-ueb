# 🎯 Nuevo Sidebar - Guía Visual

## Estructura del Nuevo Sidebar

```
┌─────────────────────────────────┐
│  🏢 Gestión              260px  │  ← Header con logo y título
├─────────────────────────────────┤
│  🏠 Dashboard                   │  ← Ítem principal
├─────────────────────────────────┤
│  CERTIFICADOS             10px  │  ← Etiqueta de sección
│  📋 Ver Certificados            │  ← Ítem con hover suave
│  ➕ Crear Certificado           │  ← Ítem normal
│  ⚙️ Parámetros                 │  ← Ítem (solo admin)
├─────────────────────────────────┤
│  PRESUPUESTO              10px  │  ← Etiqueta de sección
│  💰 Ver Presupuestos            │  ← Ítem con transición
│  📤 Importar CSV                │  ← Ítem (solo admin)
├─────────────────────────────────┤
│  ADMINISTRACIÓN           10px  │  ← Etiqueta de sección
│  👥 Gestionar Usuarios          │  ← Ítem (solo admin)
└─────────────────────────────────┘

COMPACTO (Tablet 80px):
┌──┐
│🏢│  ← Solo logo
├──┤
│🏠│  ← Hover → expande a 260px
│📋│
│➕│
│⚙️│
│💰│
│📤│
│👥│
└──┘

MÓVIL (60px):
┌──┐
│🏢│  ← Toque → overlay full
├──┤
│🏠│
│📋│
│➕│
└──┘
```

## Características Visuales

### 1️⃣ Fondo Degradado
```
ANTES:          DESPUÉS:
Azul oscuro     Blanco → Gris claro
Oscuro          Limpio y moderno
```

### 2️⃣ Items de Menú
```
ANTES:
┌──────────────────────┐
│🏠 Dashboard          │  Fondo azul
└──────────────────────┘

DESPUÉS:
┌──────────────────────┐
│🏠 Dashboard          │  Fondo transparente
└──────────────────────┘
      ↓ HOVER
┌──────────────────────┐
│🏠 Dashboard     ──→  │  Fondo #F3F4F6 + movimiento icono
└──────────────────────┘

      ↓ ACTIVO
┌─🟦─────────────────┐
│🏠 Dashboard          │  Fondo gradiente + borde azul
└──────────────────────┘
```

### 3️⃣ Header del Sidebar
```
┌─────────────────────────────────┐
│  🔷 Gestión                     │
│  
│  • Logo: Gradiente azul
│  • Border radius: 8px
│  • Sombra: 0 2px 8px rgba(...)
│  • Título: 16px, font-weight: 700
└─────────────────────────────────┘
```

### 4️⃣ Separadores de Sección
```
CERTIFICADOS
─────────────────────  ← Borde 1px #E5E7EB
PRESUPUESTO
─────────────────────  ← Espacio visual profesional
ADMINISTRACIÓN
```

## Paleta de Colores

| Elemento | Color | Uso |
|----------|-------|-----|
| Background | #FFFFFF → #F8F9FA | Fondo degradado |
| Border | #E5E7EB | Separadores |
| Texto default | #374151 | Menú sin hover |
| Texto icon | #6B7280| Iconos grises |
| Texto label | #9CA3AF | Etiquetas de sección |
| Hover bg | #F3F4F6 | Al pasar mouse |
| Activo bg | rgba(11,40,63,0.1) | Elemento activo |
| Primary | #0B283F | Borde activo y colores primarios |
| Sombra | rgba(0,0,0,0.08) | Profundidad |

## Transiciones

```
DURACIÓN: 250ms (0.25s)
EASING: cubic-bezier(0.4, 0, 0.2, 1)  ← Entrada/salida rápida

Elementos animados:
├── Background color
├── Text color
├── Icon position (translateX(2px))
├── Border color
└── Transform
```

## Responsive Behavior

```
DESKTOP (≥992px)
┌────────────────────────────────────────────┐
│ SIDEBAR (260px) | CONTENIDO                │
│ • Siempre visible                          │
│ • Hover no cambia ancho                    │
│ • Scrollable internamente                  │
└────────────────────────────────────────────┘

TABLET (768-991px)
┌──────────────────────┐
│SB│ CONTENIDO         │
│ 80px • Compacto     │
│      • Hover → 260px │
│      • Overlay modal │
└──────────────────────┘

MÓVIL (<768px)
┌──────────────────────┐
│SB  CONTENIDO         │
│ 60px • Muy compacto │
│      • Toque → full  │
│      • Overlay rgba  │
│      • Z-index alto  │
└──────────────────────┘
```

## Componentes Clave

### Logo
- Tamaño: 36x36px
- Gradiente: #0B283F → #0B0E3F
- Border radius: 8px
- Sombra: 0 2px 8px rgba(11,40,63,0.15)
- Ícono: Certificado (fa-certificate)

### Items de Menú
- Padding: 9px 12px
- Margin horizontal: 4px
- Border radius: 6px
- Icono ancho: 20px
- Gap entre icono y texto: 10px
- Transición: 0.25s

### Top Bar
- Alto: Auto (≈40px)
- Padding: 12px 30px
- Border bottom: 1px #E5E7EB
- Sombra: 0 1px 3px rgba(0,0,0,0.05)
- Avatar: 36x36px

## Animaciones Premium

1. **Hover Item**
   - Fondo cambia a #F3F4F6
   - Icono se mueve 2px a la derecha
   - Duración: 250ms smooth

2. **Activo Item**
   - Fondo: gradiente rgba(11,40,63,0.1)
   - Borde izquierdo: 3px #0B283F
   - Texto: font-weight 600

3. **Expandir Sidebar (Tablet)**
   - Ancho: 80px → 260px
   - Duración: 300ms smooth
   - Overlay: rgba(0,0,0,0.3)

4. **Scrollbar del Sidebar**
   - Ancho: 6px
   - Color: #D1D5DB
   - Hover: #9CA3AF
   - Border radius: 3px

---

**Premium Design**: ✅ Moderno, Compacto, Visual
**Inspiración**: Meta Business Suite, Figma, GitHub
**Accesibilidad**: WCAG AA compliant
