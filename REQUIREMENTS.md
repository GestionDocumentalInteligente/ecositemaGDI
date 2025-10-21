# Cero1 - Marketplace de Soluciones para Ciudades

## Overview

Sitio web simple y estático que muestra un catálogo de ~50 soluciones tecnológicas para ciudades inteligentes. Enfoque minimalista: HTML/CSS/JS + JSON como base de datos.

## Stack Tecnológico

- **Frontend**: HTML, CSS, JavaScript vanilla
- **Backend**: Node.js mínimo (solo para servir estáticos + 1 endpoint para admin)
- **Base de Datos**: JSON file (`data/solutions.json`)
- **Hosting**: Railway
- **Imágenes**: Servidas estáticamente desde `/public/images/`

## Características Principales

### 1. Home Page (Pública)

**Layout**:
- Header con logo "Cero1" y selector de idioma (ES/EN)
- Grid responsive de soluciones (cards)
- Filtro por categoría
- Footer simple

**Card de Solución**:
- Imagen principal (primera de las 4 disponibles)
- Nombre
- Categoría con emoji
- Descripción corta (truncada a 120 caracteres)
- Click → Modal con detalles completos

**Modal de Detalles**:
- Galería de imágenes (hasta 4)
- Nombre completo
- Categoría
- Descripción completa
- Website (link externo)
- Email (si existe)
- Teléfono (si existe)
- LinkedIn Founders (hasta 5 links)

### 2. Panel Admin (Privado)

**Autenticación**:
- Login simple con password hardcodeado en variable de entorno
- Sin registro, sin usuarios, solo 1 password de admin

**Funcionalidades**:
- Listar todas las soluciones
- Agregar nueva solución (formulario)
- Editar solución existente
- Eliminar solución
- Upload de imágenes (hasta 4 por solución)

### 3. Categorías (5)

1. 🚗 **Movilidad** (Mobility)
2. 🏛️ **Espacio Público** (Public Space)
3. 💰 **Fintech** (Fintech)
4. ⚖️ **LegalIA** (LegalAI)
5. 📊 **Datos** (Data)

## Modelo de Datos

### Solution Object

```json
{
  "id": "uuid-v4",
  "name": "Nombre de la Solución",
  "category": "mobility|public-space|fintech|legal-ai|data",
  "description": {
    "es": "Descripción en español...",
    "en": "Description in English..."
  },
  "website": "https://example.com",
  "email": "contact@example.com",
  "phone": "+54 11 1234-5678",
  "linkedinFounders": [
    "https://linkedin.com/in/founder1",
    "https://linkedin.com/in/founder2"
  ],
  "images": [
    "/images/solutions/uuid-1.jpg",
    "/images/solutions/uuid-2.jpg"
  ],
  "createdAt": "2025-10-21T00:00:00Z",
  "updatedAt": "2025-10-21T00:00:00Z"
}
```

**Validaciones**:
- `name`: requerido, 3-100 caracteres
- `category`: requerido, debe ser una de las 5 categorías
- `description.es`: requerido, 10-1000 caracteres
- `description.en`: requerido, 10-1000 caracteres
- `website`: requerido, URL válida
- `email`: opcional, email válido si presente
- `phone`: opcional, string
- `linkedinFounders`: opcional, array de URLs (máximo 5)
- `images`: opcional, array de paths (máximo 4)

## Diseño Visual

### Paleta de Colores

```css
--primary: #0A2463;        /* Azul Marino (Blue Navy) */
--secondary: #3E92CC;      /* Azul Claro */
--accent: #D4AF37;         /* Dorado suave para hover */
--background: #F8F9FA;     /* Gris muy claro */
--card-bg: #FFFFFF;        /* Blanco */
--text-primary: #212529;   /* Negro suave */
--text-secondary: #6C757D; /* Gris medio */
```

### Tipografía

- **Headings**: Inter, sans-serif, peso 700
- **Body**: Inter, sans-serif, peso 400
- **Fallback**: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif

### Responsive Breakpoints

- Mobile: < 768px (1 columna)
- Tablet: 768px - 1024px (2 columnas)
- Desktop: > 1024px (3 columnas)

## Internacionalización (i18n)

### Idiomas Soportados

- Español (ES) - default
- English (EN)

### Implementación

- Archivo `js/i18n.js` con traducciones
- Toggle en header para cambiar idioma
- Persistencia en `localStorage`
- Traducciones para:
  - UI labels (filtros, botones, placeholders)
  - Nombres de categorías
  - Contenido de soluciones (description)

## API Endpoints

### Públicos (GET)

- `GET /api/solutions` - Listar todas las soluciones
- `GET /api/solutions/:id` - Obtener una solución específica

### Admin (POST, autenticación requerida)

- `POST /api/admin/login` - Login con password
- `POST /api/admin/solutions` - Crear nueva solución
- `PUT /api/admin/solutions/:id` - Actualizar solución
- `DELETE /api/admin/solutions/:id` - Eliminar solución
- `POST /api/admin/upload` - Upload de imagen

**Autenticación**: Header `Authorization: Bearer {ADMIN_PASSWORD}`

## Estructura del Proyecto

```
cero1/
├── server.js                 # Node.js server (minimal)
├── package.json              # Dependencies
├── .env.example              # Environment variables template
├── .gitignore
├── README.md
├── REQUIREMENTS.md           # Este archivo
│
├── public/                   # Archivos estáticos servidos públicamente
│   ├── index.html            # Home page
│   ├── admin.html            # Admin panel
│   │
│   ├── css/
│   │   ├── main.css          # Estilos principales
│   │   └── admin.css         # Estilos del admin panel
│   │
│   ├── js/
│   │   ├── app.js            # Lógica home page
│   │   ├── admin.js          # Lógica admin panel
│   │   ├── i18n.js           # Traducciones
│   │   └── utils.js          # Funciones compartidas
│   │
│   └── images/
│       ├── logo.png          # Logo Cero1
│       └── solutions/        # Imágenes de soluciones (gitignored)
│           └── .gitkeep
│
└── data/
    └── solutions.json        # Base de datos
```

## Deployment (Railway)

### Variables de Entorno

```bash
NODE_ENV=production
PORT=3000
ADMIN_PASSWORD=tu-password-seguro-aqui
```

### Railway Configuration

- **Build Command**: `npm install`
- **Start Command**: `node server.js`
- **Port**: Detectado automáticamente de `process.env.PORT`

### Persistent Storage

- Railway provee filesystem efímero
- Considerar Railway Volume para `/public/images/solutions/` y `/data/` si se requiere persistencia entre deployments
- Alternativa: migrar a Railway PostgreSQL + file storage externo (futuro)

## Roadmap de Implementación

### Fase 1: Setup Base
1. Crear `server.js` con Express mínimo
2. Crear `package.json` con dependencias
3. Estructura de carpetas

### Fase 2: Home Page
1. `index.html` con estructura básica
2. `main.css` con diseño responsive
3. `app.js` para cargar y mostrar soluciones
4. Filtro por categoría
5. Modal de detalles

### Fase 3: Admin Panel
1. `admin.html` con formularios
2. `admin.css` para estilos del panel
3. `admin.js` para CRUD operations
4. Sistema de autenticación simple
5. Upload de imágenes

### Fase 4: i18n
1. `i18n.js` con traducciones ES/EN
2. Integrar en toda la UI
3. Toggle de idioma en header

### Fase 5: Deployment
1. Configurar Railway
2. Variables de entorno
3. Testing en producción
4. Ajustes finales

## Notas Importantes

- **Simplicidad First**: Si algo complica, se saca
- **No Over-Engineering**: JSON file es suficiente para 50 soluciones
- **Mobile First**: Diseñar primero para mobile
- **Accesibilidad**: Usar HTML semántico, alt tags, ARIA labels básicos
- **Performance**: Lazy loading de imágenes, minificación en producción
- **Open Source**: Todo el código es público en GitHub

## Futuras Mejoras (Out of Scope por Ahora)

- Auth0 SSO (demasiado complejo para MVP)
- Base de datos real (PostgreSQL)
- Cloudinary/S3 para imágenes
- Server-side rendering
- GraphQL API
- Admin con roles y permisos
- Analytics
- SEO optimization avanzado
