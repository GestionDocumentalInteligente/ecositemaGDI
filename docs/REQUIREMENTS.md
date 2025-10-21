# Especificación Funcional - Marketplace de Soluciones para Ciudades

## 1. Visión del Proyecto

Marketplace tipo "vidriera" de soluciones tecnológicas para ciudades, construido con HivePress (WordPress), desplegado automáticamente en Railway desde GitHub, con autenticación vía Auth0.

---

## 2. Casos de Uso Principales

### 2.1 Usuario Visitante (No Autenticado)
- Ver home con 6 soluciones aleatorias (2 filas x 3 columnas)
- Buscar soluciones por nombre/descripción
- Filtrar por categoría (5 botones)
- Ver detalle completo de una solución
- Leer página "Sobre Nosotros"
- Ver badge "Sin Verificar" o "Verificada" en cada solución

### 2.2 Usuario Autenticado (Contributor)
- Login vía Auth0 (SSO)
- Crear nueva solución (todos los campos)
- Ver sus propias soluciones publicadas
- Editar sus soluciones
- NO puede aprobar/verificar soluciones

### 2.3 Administrador (Usuario Nativo WordPress)
- Login nativo de WordPress (seguridad)
- Aprobar soluciones (cambiar estado de "pending" a "published")
- Crear/editar/eliminar cualquier solución
- Gestionar categorías
- Crear soluciones iniciales manualmente

---

## 3. Modelo de Datos

### 3.1 Solución (Listing)

| Campo | Tipo | Obligatorio | Validación | Notas |
|-------|------|-------------|------------|-------|
| **Título** | Text (short) | ✅ Sí | Max 200 chars | Nombre de la solución |
| **Detalle** | Textarea | ✅ Sí | Max 1000 chars | Descripción completa |
| **Imágenes** | Gallery | ✅ Sí | 1-4 archivos, 1200x800px recomendado | JPEG/PNG |
| **Categoría** | Select (single) | ✅ Sí | 1 de 5 opciones | Ver 3.2 |
| **LinkedIn Founders** | Repeater Field | ❌ No | Hasta 5 URLs válidas | URLs de perfiles LinkedIn |
| **Sitio Web** | URL | ✅ Sí | URL válida | Website oficial |
| **Email** | Email | ❌ No | Email válido | Contacto |
| **Teléfono** | Text | ❌ No | - | Formato libre |
| **Estado** | Status | Auto | pending/published | Ver 3.3 |

### 3.2 Categorías

1. **Movilidad**
2. **Espacio Público**
3. **Fintech**
4. **LegalIA**
5. **Datos**

### 3.3 Estados de Solución

| Estado | WordPress Status | Visible Públicamente | Badge Mostrado |
|--------|------------------|----------------------|----------------|
| **Sin Verificar** | `pending` | ✅ Sí | 🟡 "Sin Verificar" |
| **Verificada** | `published` | ✅ Sí | ✅ "Verificada" |

**Flujo:**
1. Usuario crea solución → Estado `pending` (Sin Verificar)
2. Admin aprueba → Estado `published` (Verificada)
3. Ambos estados son visibles públicamente con badges diferentes

---

## 4. Páginas del Sitio

### 4.1 Home (`/`)
- Header con logo + botón "Login/Agregar Listing"
- Buscador central
- 5 botones de categorías (horizontal o grid)
- Grid 2x3 con 6 soluciones aleatorias
- Footer con link a "Sobre Nosotros"

### 4.2 Listado por Categoría (`/categoria/{nombre}`)
- Todas las soluciones de esa categoría
- Filtros (opcional): estado verificado
- Mismo layout de cards que home

### 4.3 Detalle de Solución (`/listing/{slug}`)
- Título + Badge (Verificada/Sin Verificar)
- Galería de hasta 4 imágenes (carousel)
- Descripción completa (1000 chars)
- Datos de contacto: Web, Email, Tel
- LinkedIn Founders (lista de links clicables)
- Categoría (chip/tag)

### 4.4 Crear/Editar Solución (`/submit-listing/`)
- Formulario con todos los campos
- Validaciones frontend
- Upload de imágenes con preview
- Campos repetibles para Founders (hasta 5)

### 4.5 Sobre Nosotros (`/sobre-nosotros/`)
- Texto placeholder: "Texto Sobre Nosotros"
- Editable desde WordPress

---

## 5. Autenticación y Roles

### 5.1 Auth0 Integration
- **Provider:** Auth0 (tenant existente)
- **Flujo:** Universal Login (redirect)
- **Registro:** Automático en WordPress al primer login
- **Rol asignado:** `Contributor` (WordPress native role)
- **No se usa:** Registro nativo de WordPress

### 5.2 Roles de WordPress

| Rol | Permisos | Login Method |
|-----|----------|--------------|
| **Contributor** | Crear soluciones (quedan en pending), editar propias | Auth0 |
| **Administrator** | Todo (aprobar, editar cualquier cosa, manage) | WordPress nativo |

### 5.3 Variables de Entorno (Railway)
```
AUTH0_DOMAIN=xxx.auth0.com
AUTH0_CLIENT_ID=xxxxx
AUTH0_CLIENT_SECRET=xxxxx
AUTH0_REDIRECT_URI=https://{railway-app}.railway.app/wp-login.php
```

---

## 6. Diseño y Branding

### 6.1 Colores
- **Primario:** Azul Marino `#0A2463`
- **Secundario:** (derivado del primario)
- **Badges:**
  - Sin Verificar: Amarillo/Orange
  - Verificada: Verde/Azul

### 6.2 Referencia Visual
- Inspiración: [ListingHive Demo](https://listinghive.hivepress.io/)
- **SIN:** Geolocalización, mapas
- **CON:** Cards limpias, grid, badges

### 6.3 Imágenes
- **Formato:** JPEG/PNG
- **Tamaño recomendado:** 1200x800px (landscape)
- **Optimización:** WordPress auto-genera thumbnails

---

## 7. Idiomas

**Bilingüe:** Español (ES) / Inglés (EN)

### 7.1 Traducción
- Plugin: WPML o Polylang (definir en arquitectura)
- Interfaz traducible
- Contenido: admin decide idioma al crear

---

## 8. Infraestructura

### 8.1 Hosting
- **Plataforma:** Railway
- **Deploy:** Auto desde GitHub (push to main)
- **Dominio:** `*.railway.app` (inicialmente)

### 8.2 Base de Datos
- **Motor:** MySQL 8.0 (servicio de Railway)
- **Persistencia:** Railway Database Service

### 8.3 Archivos
- **Uploads:** `/wp-content/uploads/` en Railway Volume
- **Plugins/Temas:** En repo Git

### 8.4 Secretos
- `.env` en Railway (NO en repo)
- Auth0 credentials
- DB credentials (autogeneradas por Railway)

---

## 9. Funcionalidades NO Incluidas (Fase 1)

- ❌ Pagos / Ecommerce
- ❌ Sistema de reviews/ratings
- ❌ Geolocalización / Mapas
- ❌ Mensajería entre usuarios
- ❌ Notificaciones por email (transaccionales)
- ❌ Panel de analytics
- ❌ API pública

---

## 10. Criterios de Aceptación

### Para considerar el proyecto completo:

1. ✅ Deploy exitoso en Railway desde GitHub
2. ✅ Auth0 login funcional (crear usuario automático)
3. ✅ 5 categorías creadas y funcionando
4. ✅ CRUD completo de soluciones (crear, editar, ver, aprobar)
5. ✅ Home con 6 soluciones random + buscador
6. ✅ Filtro por categoría funcional
7. ✅ Badges de verificación visibles
8. ✅ Campo repetible para hasta 5 LinkedIn Founders
9. ✅ Galería de hasta 4 imágenes funcional
10. ✅ Tema hijo con colores azul marino
11. ✅ Bilingüe ES/EN (interfaz)
12. ✅ Admin puede aprobar/rechazar desde WordPress
13. ✅ Contributors solo crean (quedan pending)

---

## 11. Supuestos y Restricciones

### Supuestos:
- Railway account ya existe y está configurada
- Auth0 tenant existe con credenciales disponibles
- Usuario admin creará las primeras soluciones manualmente
- Imágenes serán proporcionadas en formato correcto

### Restricciones:
- **No configuración por UI:** Todo desde código/CLI
- **Despliegue automático:** Push = deploy
- **Sin emails:** Por ahora sin SMTP
- **Gratis/Open Source:** HivePress versión gratuita

---

## 12. Siguientes Pasos (Post-Launch)

- [ ] Dominio custom
- [ ] Analytics (Google/Plausible)
- [ ] SEO optimization
- [ ] Emails transaccionales
- [ ] Sistema de favoritos
- [ ] Exportar listado a CSV/JSON
