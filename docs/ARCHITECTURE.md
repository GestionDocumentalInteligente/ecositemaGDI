# Arquitectura Técnica - Marketplace de Soluciones para Ciudades

## 1. Stack Tecnológico

### 1.1 Core
- **CMS:** WordPress 6.4+ (latest stable)
- **Marketplace Plugin:** HivePress 1.7+ (free version)
- **PHP:** 8.1+
- **Database:** MySQL 8.0 (Railway managed service)
- **Web Server:** Apache/Nginx (contenedor)

### 1.2 Autenticación
- **Provider:** Auth0
- **Plugin:** miniOrange Auth0 SSO o custom integration
- **Flow:** OAuth 2.0 / OIDC

### 1.3 Infraestructura
- **Hosting:** Railway
- **Storage:** Railway Volumes (uploads)
- **Deploy:** GitHub Actions + Railway auto-deploy
- **SSL:** Railway managed (automatic)

### 1.4 Multilenguaje
- **Plugin:** Polylang (free) o WPML Lite
- **Idiomas:** ES (default) + EN

---

## 2. Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                         USUARIO                             │
│                    (Browser / Mobile)                       │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        │ HTTPS
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    RAILWAY PLATFORM                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │          WordPress Container (Docker)                │   │
│  │                                                       │   │
│  │  ┌──────────────┐      ┌─────────────────────────┐  │   │
│  │  │   Apache/    │◄────►│   WordPress Core        │  │   │
│  │  │   Nginx      │      │   + HivePress Plugin    │  │   │
│  │  │              │      │   + Auth0 Plugin        │  │   │
│  │  └──────────────┘      │   + Polylang            │  │   │
│  │                        │   + Custom Child Theme  │  │   │
│  │                        └──────────┬──────────────┘  │   │
│  │                                   │                  │   │
│  │                                   │ MySQL Driver     │   │
│  │                                   ▼                  │   │
│  │                        ┌──────────────────────────┐  │   │
│  │                        │  Railway Volume          │  │   │
│  │                        │  /wp-content/uploads/    │  │   │
│  │                        └──────────────────────────┘  │   │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │            MySQL 8.0 Service (Railway)                │  │
│  │                                                        │  │
│  │  - Database: wordpress_db                             │  │
│  │  - Persistent storage                                 │  │
│  │  - Auto backups (Railway feature)                     │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │         Environment Variables (.env)                   │  │
│  │  - AUTH0_DOMAIN, CLIENT_ID, SECRET                    │  │
│  │  - DB_HOST, DB_NAME, DB_USER, DB_PASS                 │  │
│  │  - WP_HOME, WP_SITEURL                                │  │
│  └───────────────────────────────────────────────────────┘  │
└────────────────────────┬─────────────────────────────────────┘
                         │
                         │ OAuth 2.0 / OIDC
                         ▼
              ┌──────────────────────┐
              │   Auth0 Service      │
              │   (External SaaS)    │
              └──────────────────────┘
```

---

## 3. Estructura del Repositorio

```
hivepress-marketplace/
├── .github/
│   └── workflows/
│       └── deploy.yml              # CI/CD opcional (Railway autodeploy)
│
├── docker/
│   ├── Dockerfile                  # Imagen WordPress + HivePress
│   └── docker-compose.yml          # Para desarrollo local
│
├── config/
│   ├── wp-config-template.php      # Template con env vars
│   └── .htaccess                   # Apache rules
│
├── scripts/
│   ├── init-wordpress.sh           # Setup inicial (WP-CLI)
│   ├── install-plugins.sh          # Instalar HivePress, Auth0, etc
│   ├── configure-hivepress.php     # WP-CLI: crear campos custom
│   └── seed-categories.php         # Crear las 5 categorías
│
├── wp-content/
│   ├── themes/
│   │   └── hivepress-child/        # Child theme customizado
│   │       ├── style.css           # Colores azul marino
│   │       ├── functions.php       # Customizaciones
│   │       ├── templates/          # Overrides de HivePress
│   │       └── languages/          # Traducciones ES/EN
│   │
│   ├── plugins/
│   │   └── hivepress-auth0/        # Plugin custom integración Auth0
│   │       ├── hivepress-auth0.php
│   │       └── includes/
│   │           ├── class-auth0-login.php
│   │           └── class-user-sync.php
│   │
│   └── mu-plugins/                 # Must-use plugins
│       └── custom-config.php       # Configuraciones forzadas
│
├── docs/
│   ├── REQUIREMENTS.md             # Este documento
│   ├── ARCHITECTURE.md             # Arquitectura
│   ├── PLAN.md                     # Roadmap
│   └── AGENTS.md                   # División de tareas
│
├── .env.example                    # Template de variables
├── .gitignore
├── railway.json                    # Railway config (opcional)
└── README.md                       # Setup instructions
```

---

## 4. Flujo de Deploy (GitHub → Railway)

```
┌──────────────┐
│ Developer    │
│ (git push)   │
└──────┬───────┘
       │
       ▼
┌─────────────────────┐
│  GitHub Repository  │
│  (main branch)      │
└──────┬──────────────┘
       │ webhook
       ▼
┌─────────────────────────────────────┐
│       Railway Platform              │
│  1. Detecta cambios en main         │
│  2. Clona repo                      │
│  3. Lee Dockerfile                  │
│  4. Build imagen Docker             │
│  5. Inyecta env vars                │
│  6. Deploy contenedor               │
│  7. Conecta a MySQL service         │
│  8. Monta Volume en /wp-content/... │
│  9. Run init scripts (si es 1° vez) │
│ 10. Servicio disponible en URL      │
└─────────────────────────────────────┘
```

### 4.1 Primer Deploy (Setup Inicial)
1. Railway detecta Dockerfile
2. Build imagen con WordPress + plugins
3. Crea MySQL database automáticamente
4. Script `init-wordpress.sh` ejecuta:
   - `wp core install` (admin nativo)
   - `wp plugin activate hivepress`
   - `wp plugin activate auth0-integration`
   - `wp theme activate hivepress-child`
   - `wp eval-file configure-hivepress.php` (crear campos)
   - `wp eval-file seed-categories.php` (5 categorías)
5. WordPress listo para usar

### 4.2 Deploys Subsiguientes
1. Solo rebuild imagen
2. NO resetea DB (persiste en Railway MySQL)
3. NO resetea uploads (persiste en Volume)
4. Actualiza código (temas/plugins si cambiaron)

---

## 5. Base de Datos - Schema Crítico

### 5.1 Tablas de WordPress (Core)
- `wp_posts` → Listings (custom post type `hp_listing`)
- `wp_postmeta` → Campos custom de HivePress
- `wp_users` → Usuarios (Auth0 sync)
- `wp_terms` → Categorías

### 5.2 Campos Custom (HivePress)

Registrados en código vía `hivepress/v1/fields`:

```php
// Ejemplo simplificado
'linkedin_founders' => [
    'type' => 'repeater',
    'max_items' => 5,
    'fields' => [
        'url' => [
            'type' => 'url',
            'placeholder' => 'https://linkedin.com/in/username'
        ]
    ]
],
'website' => [
    'type' => 'url',
    'required' => true,
],
'contact_email' => [
    'type' => 'email',
    'required' => false,
],
// etc...
```

### 5.3 Categorías (Taxonomy)

HivePress usa `hp_listing_category`:

```
┌────────────────┬──────────┐
│ Categoría      │ Slug     │
├────────────────┼──────────┤
│ Movilidad      │ movilidad│
│ Espacio Público│ espacio  │
│ Fintech        │ fintech  │
│ LegalIA        │ legalia  │
│ Datos          │ datos    │
└────────────────┴──────────┘
```

---

## 6. Autenticación - Flujo Detallado

```
┌─────────┐
│ Usuario │
└────┬────┘
     │
     │ 1. Click "Login"
     ▼
┌──────────────────┐
│ WordPress        │
│ (wp-login.php)   │
└────┬─────────────┘
     │
     │ 2. Redirect to Auth0
     ▼
┌──────────────────────┐
│ Auth0 Universal Login│
│ (auth0.com)          │
└────┬─────────────────┘
     │
     │ 3. Usuario autentica
     ▼
┌──────────────────────┐
│ Auth0 Callback       │
│ (con token)          │
└────┬─────────────────┘
     │
     │ 4. Plugin procesa token
     ▼
┌────────────────────────────┐
│ Plugin Auth0 Integration   │
│  - Valida token            │
│  - Busca usuario en WP     │
│  - Si no existe: crea      │
│  - Asigna rol: Contributor │
│  - Login en WordPress      │
└────┬───────────────────────┘
     │
     │ 5. Redirect a dashboard
     ▼
┌──────────────────────┐
│ WordPress Dashboard  │
│ (puede crear listing)│
└──────────────────────┘
```

### 6.1 Mapeo de Datos Auth0 → WordPress

| Auth0 Claim | WordPress Field | Notas |
|-------------|-----------------|-------|
| `sub` | `user_meta: auth0_id` | Único identificador |
| `email` | `user_email` | Único |
| `name` | `display_name` | Nombre completo |
| `nickname` | `user_login` | Username (sanitizado) |
| N/A | `role` | Hardcoded: `contributor` |

---

## 7. Temas y Customización

### 7.1 Child Theme Hierarchy

```
wp-content/themes/
├── hivepress/              # Parent (HivePress default)
└── hivepress-child/        # Child (nuestro custom)
    ├── style.css
    ├── functions.php
    ├── templates/
    │   ├── listing/
    │   │   ├── view/listing.php           # Detalle (agregar badge)
    │   │   └── view/page/listing-category.php  # Listado
    │   └── home.php                       # Home custom
    └── assets/
        ├── css/
        │   └── custom.css                 # Azul marino #0A2463
        └── js/
            └── custom.js
```

### 7.2 Customizaciones CSS (Azul Marino)

```css
:root {
  --hivepress-primary-color: #0A2463;
  --hivepress-accent-color: #3E92CC;
}

.hp-listing__badge--pending {
  background: #FFA500;
  color: #fff;
}

.hp-listing__badge--published {
  background: #28A745;
  color: #fff;
}
```

---

## 8. Persistencia y Backups

### 8.1 Railway Volumes
- **Path:** `/var/www/html/wp-content/uploads`
- **Size:** 10GB (Railway free tier)
- **Backup:** Manual (Railway CLI o custom script)

### 8.2 MySQL Backups
- **Automático:** Railway snapshot diario (pro plan)
- **Manual:** `mysqldump` vía Railway CLI

### 8.3 Código
- **Source of Truth:** GitHub repo
- **Branches:** `main` (production), `dev` (opcional)

---

## 9. Variables de Entorno (.env)

```bash
# WordPress Core
WP_ENV=production
WP_HOME=https://{app-name}.railway.app
WP_SITEURL=${WP_HOME}
WP_DEBUG=false

# Database (Railway auto-provides)
DB_HOST=${MYSQLHOST}
DB_NAME=${MYSQLDATABASE}
DB_USER=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}
DB_PORT=${MYSQLPORT}

# Auth0
AUTH0_DOMAIN=xxx.us.auth0.com
AUTH0_CLIENT_ID=xxxxxxxxxxxx
AUTH0_CLIENT_SECRET=xxxxxxxxxxxxxxxx
AUTH0_REDIRECT_URI=${WP_HOME}/wp-login.php

# Security
AUTH_KEY=generate-unique-key
SECURE_AUTH_KEY=generate-unique-key
LOGGED_IN_KEY=generate-unique-key
NONCE_KEY=generate-unique-key
# ... (usar https://api.wordpress.org/secret-key/1.1/salt/)

# Opcional
WP_MEMORY_LIMIT=256M
WP_MAX_MEMORY_LIMIT=512M
```

---

## 10. Plugins Requeridos

### 10.1 Core Functionality
1. **HivePress** (Free)
   - Version: Latest stable
   - Source: WordPress.org plugin repo
   - Config: Via código (no UI)

2. **HivePress Auth0 Integration** (Custom)
   - Desarrollado ad-hoc
   - Ubicación: `wp-content/plugins/hivepress-auth0/`

3. **Polylang** (Free)
   - Version: Latest stable
   - Idiomas: ES (default), EN
   - Strings traducibles vía PO files

### 10.2 Opcional (Performance)
4. **WP Super Cache** o **LiteSpeed Cache**
   - Para producción
   - Config: Minimal (solo page cache)

---

## 11. Decisiones Técnicas y Trade-offs

### 11.1 ¿Por qué WordPress?
✅ HivePress gratuito y potente
✅ Ecosistema maduro
✅ Fácil customización sin código
❌ Overhead (más pesado que JAMstack)

### 11.2 ¿Por qué Railway?
✅ MySQL managed incluido
✅ Auto-deploy desde GitHub
✅ Volumes persistentes
❌ Costo (tier gratis limitado)

### 11.3 ¿Por qué MySQL y no SQLite?
✅ Railway lo ofrece managed
✅ Más robusto para concurrent writes
❌ SQLite no soportado bien por WordPress en producción

### 11.4 ¿Por qué Polylang y no WPML?
✅ Gratis
✅ Suficiente para traducir interfaz
❌ WPML es más potente pero pago

### 11.5 ¿Por qué Custom Plugin Auth0?
✅ Control total del flujo
✅ No depender de plugins abandonados
❌ Más trabajo inicial (pero reutilizable)

---

## 12. Seguridad

### 12.1 Checklist
- [x] HTTPS forzado (Railway SSL)
- [x] Auth0 tokens firmados (JWT)
- [x] Admin login separado (nativo WP)
- [x] Secrets en `.env` (no en repo)
- [x] WordPress salts únicos
- [ ] Rate limiting (Railway feature)
- [ ] CORS headers (configurar en .htaccess)

### 12.2 Roles y Capabilities

```php
// Contributor (Auth0 users)
add_role('contributor', 'Contributor', [
    'read' => true,
    'edit_posts' => true,          // Sus propios listings
    'delete_posts' => false,
    'publish_posts' => false,      // Solo pending
    'upload_files' => true,
]);

// Admin (nativo)
// Tiene ALL capabilities por defecto
```

---

## 13. Performance Targets

### 13.1 Métricas
- **Time to First Byte:** < 800ms
- **First Contentful Paint:** < 2s
- **Page Size:** < 2MB (con imágenes)

### 13.2 Optimizaciones
1. **Images:** WordPress auto-resize (1200x800 → thumbnails)
2. **Caching:** WP Super Cache (page cache)
3. **CDN:** Opcional (Cloudflare free tier)
4. **DB:** Index en `post_type`, `post_status`

---

## 14. Monitoreo y Logs

### 14.1 Railway Logs
- **Acceso:** Railway dashboard o CLI
- **Retención:** 7 días (free tier)

### 14.2 WordPress Debug Log
```php
// wp-config.php (solo desarrollo)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 14.3 Uptime Monitoring
- **Tool:** UptimeRobot (free) o Railway built-in
- **Endpoint:** `{app}.railway.app/wp-login.php`

---

## 15. Siguiente Fase (Post-MVP)

- [ ] Dominio custom + DNS
- [ ] Google Analytics / Plausible
- [ ] Email transaccional (SendGrid)
- [ ] Sistema de favoritos (custom plugin)
- [ ] Export CSV de listings
- [ ] API REST pública
- [ ] PWA (Progressive Web App)
- [ ] Caché avanzado (Redis)

---

## 16. Referencias Técnicas

- [HivePress Docs](https://hivepress.io/docs/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Auth0 WordPress Integration](https://auth0.com/docs/quickstart/webapp/wordpress)
- [Railway Docs](https://docs.railway.app/)
- [WP-CLI Handbook](https://make.wordpress.org/cli/handbook/)
