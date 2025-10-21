# Cero1 - El Marketplace de Soluciones para Ciudades

> Plataforma tipo "vidriera" de soluciones tecnológicas para gobiernos y ciudades. Construida con HivePress (WordPress), Auth0 y desplegada en Railway.

---

## 🚀 Estado del Proyecto

**Fase Actual:** ✅ Código Base Completo (Fase 0 + Fase 1)
**Siguiente Paso:** Deploy manual en Railway → Validación

---

## 📚 Documentación

### Documentos Principales

1. **[REQUIREMENTS.md](./docs/REQUIREMENTS.md)** - Especificación funcional completa
   - Casos de uso
   - Modelo de datos
   - Campos custom
   - Criterios de aceptación

2. **[ARCHITECTURE.md](./docs/ARCHITECTURE.md)** - Arquitectura técnica
   - Stack tecnológico
   - Diagramas de infraestructura
   - Decisiones de diseño
   - Variables de entorno

3. **[PLAN.md](./docs/PLAN.md)** - Roadmap de implementación
   - 5 Fases detalladas (Fase 0 → Fase 5)
   - Tareas específicas con código
   - Cronograma estimado (3-5 días)
   - Risks & mitigación

4. **[AGENTS.md](./docs/AGENTS.md)** - Equipo de agentes
   - 6 agentes especializados
   - Matriz RACI
   - Workflow y handoffs
   - Métricas de éxito

---

## 🎯 Características Principales

### Funcionalidades MVP

- ✅ **Marketplace sin comisiones** - "Vidriera" de soluciones
- ✅ **Login vía Auth0** - Sin registro nativo de WordPress
- ✅ **5 Categorías** - Movilidad, Espacio Público, Fintech, LegalIA, Datos
- ✅ **Sistema de verificación** - Badges "Sin Verificar" / "Verificada"
- ✅ **Campos personalizados:**
  - Título, Detalle (1000 chars)
  - Hasta 4 imágenes (1200x800px)
  - Hasta 5 LinkedIn Founders
  - Web, Email, Teléfono
- ✅ **Bilingüe** - Español / Inglés
- ✅ **Deploy automático** - Push to GitHub → Railway

---

## 🛠️ Stack Tecnológico

| Layer | Tecnología |
|-------|-----------|
| **CMS** | WordPress 6.4+ |
| **Marketplace** | HivePress (free) |
| **Auth** | Auth0 (OAuth 2.0) |
| **Database** | MySQL 8.0 (Railway managed) |
| **Hosting** | Railway (auto-deploy) |
| **i18n** | Polylang |
| **Theme** | Custom child theme (azul marino #0A2463) |

---

## 📦 Estructura del Proyecto

```
hivepress-marketplace/
├── docs/                    # Documentación completa
│   ├── REQUIREMENTS.md
│   ├── ARCHITECTURE.md
│   ├── PLAN.md
│   └── AGENTS.md
│
├── docker/                  # Configuración Docker
│   ├── Dockerfile
│   └── docker-compose.yml
│
├── config/                  # Configuración WordPress
│   ├── wp-config.php
│   └── .htaccess
│
├── scripts/                 # Scripts de setup
│   ├── entrypoint.sh
│   ├── install-plugins.sh
│   ├── configure-hivepress.php
│   └── seed-categories.php
│
├── wp-content/
│   ├── themes/
│   │   └── hivepress-child/    # Child theme custom
│   │
│   └── plugins/
│       └── hivepress-auth0/    # Plugin Auth0 custom
│
├── .env.example
├── railway.json
└── README.md (este archivo)
```

---

## 🚦 Quick Start (Desarrollo Local)

### Prerequisites

- Docker + Docker Compose
- Auth0 account (credenciales)
- GitHub account

### Setup

```bash
# 1. Clonar repo
git clone https://github.com/[usuario]/hivepress-marketplace.git
cd hivepress-marketplace

# 2. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales Auth0

# 3. Levantar servicios
docker-compose up -d

# 4. Acceder a WordPress
open http://localhost:8080

# 5. Login admin
# User: admin
# Pass: [ver .env]
```

---

## 🚀 Deploy en Railway (Manual)

### Pasos Detallados

#### 1. Push a GitHub
```bash
# En tu terminal, dentro del proyecto
git init
git add .
git commit -m "feat: initial Cero1 marketplace setup"

# Crear repo en GitHub y conectar
git remote add origin https://github.com/[tu-usuario]/cero1-marketplace.git
git branch -M main
git push -u origin main
```

#### 2. Crear Proyecto en Railway
- Ve a [railway.app](https://railway.app)
- Click "New Project"
- Selecciona "Deploy from GitHub repo"
- Autoriza acceso a GitHub y selecciona el repo `cero1-marketplace`

#### 3. Agregar MySQL Database
- Dentro del proyecto Railway, click "+ New"
- Selecciona "Database" → "Add MySQL"
- Railway auto-genera credenciales (no las copies, se inyectan automáticamente)

#### 4. Configurar Variables de Entorno
En Railway dashboard → Tu servicio (WordPress) → Variables:

**IMPORTANTE:** Primero obtén la URL generada por Railway para tu app (ej: `cero1-production-xxxx.up.railway.app`), luego agrega:

```bash
# WordPress Core
WP_ENV=production
WP_HOME=https://cero1-production-xxxx.up.railway.app
WP_SITEURL=https://cero1-production-xxxx.up.railway.app
WP_DEBUG=false

# Admin (usa tu email)
WP_ADMIN_EMAIL=sistema.gdi.abierto@gmail.com
WP_ADMIN_PASSWORD=[genera-password-seguro]
WP_ADMIN_USER=admin

# Auth0 (YA ESTÁN EN .env.example, cópialas)
AUTH0_DOMAIN=gdilatam.us.auth0.com
AUTH0_CLIENT_ID=rBIyrJCFZa6DKuCAEfgax1PchQ7XvDA0
AUTH0_CLIENT_SECRET=sotsWYm65mjv9wHqfdwJ31EH676MzAWGUbbwINeNWNbjuIDPbKoNwPheaqHTgCV6
AUTH0_REDIRECT_URI=https://cero1-production-xxxx.up.railway.app/wp-login.php

# Security Keys - GENERA NUEVOS EN: https://api.wordpress.org/secret-key/1.1/salt/
AUTH_KEY=genera-aqui-tu-key-unica
SECURE_AUTH_KEY=genera-aqui-tu-key-unica
LOGGED_IN_KEY=genera-aqui-tu-key-unica
NONCE_KEY=genera-aqui-tu-key-unica
AUTH_SALT=genera-aqui-tu-salt-unico
SECURE_AUTH_SALT=genera-aqui-tu-salt-unico
LOGGED_IN_SALT=genera-aqui-tu-salt-unico
NONCE_SALT=genera-aqui-tu-salt-unico

# Performance
WP_MEMORY_LIMIT=256M
WP_MAX_MEMORY_LIMIT=512M
UPLOAD_MAX_FILESIZE=5M
POST_MAX_SIZE=10M
```

**Nota:** Railway inyecta automáticamente las variables de MySQL (`MYSQLHOST`, `MYSQLUSER`, etc.), NO las agregues manualmente.

#### 5. Configurar Railway Volume (Persistencia de Imágenes)
- En Railway dashboard → Tu servicio → Settings
- Scroll hasta "Volumes"
- Click "+ Add Volume"
  - Mount Path: `/var/www/html/wp-content/uploads`
  - Size: 5GB (suficiente para inicio)
- Click "Add"

#### 6. Deploy!
- Railway detecta automáticamente el `Dockerfile`
- Inicia el build (puede tardar 3-5 minutos)
- Una vez completado, accede a tu URL: `https://cero1-production-xxxx.up.railway.app`

#### 7. Configurar Auth0 Callback URL
- Ve a Auth0 Dashboard → Applications → Marketplace
- En "Application URIs":
  - **Allowed Callback URLs:** `https://cero1-production-xxxx.up.railway.app/wp-login.php`
  - **Allowed Logout URLs:** `https://cero1-production-xxxx.up.railway.app`
  - **Allowed Web Origins:** `https://cero1-production-xxxx.up.railway.app`
- Save Changes

#### 8. Validar Instalación
- Accede a `https://cero1-production-xxxx.up.railway.app`
- Deberías ver la home de Cero1
- Ve a `/wp-admin` → debería redirigir a Auth0 login
- Para login admin nativo: `/wp-admin?native_login=1`
  - User: `admin`
  - Pass: el que configuraste en `WP_ADMIN_PASSWORD`

### Troubleshooting

**Error: "Database connection error"**
- Verifica que el servicio MySQL esté corriendo en Railway
- Railway debe inyectar automáticamente las variables de DB

**Error: "Auth0 authentication failed"**
- Verifica las credenciales en Railway Variables
- Verifica que el Callback URL esté configurado en Auth0

**Imágenes no se guardan después de redeploy**
- Verifica que el Railway Volume esté montado en `/var/www/html/wp-content/uploads`

Ver guía completa en: `docs/ARCHITECTURE.md`

---

## 👥 Equipo de Agentes

Este proyecto se estructura con **6 agentes especializados**:

1. **ARCHITECT** - Orquestador y tech lead
2. **INFRA AGENT** - DevOps (Railway, Docker)
3. **BACKEND AGENT** - WordPress + HivePress
4. **SECURITY AGENT** - Auth0 integration
5. **FRONTEND AGENT** - Child theme + UX
6. **QA AGENT** - Testing E2E

Ver roles detallados en: [docs/AGENTS.md](./docs/AGENTS.md)

---

## 📋 Roadmap

### Fase 0: Setup Inicial ✅
- [x] Verificar accesos (Railway, Auth0)
- [x] Crear estructura de carpetas
- [x] .gitignore y .env.example

### Fase 1: WordPress Base ✅
- [x] Dockerfile funcional
- [x] docker-compose.yml (dev local)
- [x] wp-config.php con env vars
- [x] Scripts de entrypoint

### Fase 2: HivePress ✅
- [x] Scripts de instalación de plugins
- [x] Campos custom (Website, Email, Phone, LinkedIn Founders)
- [x] Script para crear 5 categorías
- [x] Child theme completo con estilos

### Fase 3: Auth0 ✅
- [x] Plugin custom completo
- [x] OAuth 2.0 flow implementado
- [x] Auto-creación de usuarios como Contributors
- [x] Login nativo para admin

### Fase 4: Frontend ✅ (Base Completa)
- [x] Child theme con branding Cero1
- [x] Estilos azul marino (#0A2463)
- [x] CSS para badges (Verificada/Sin Verificar)
- [x] Responsive design (mobile/tablet/desktop)
- [x] Must-use plugins (configuración global)

### Fase 5: Deploy + Validación ⏳ (PRÓXIMO)
- [ ] Push a GitHub
- [ ] Deploy manual en Railway
- [ ] Configurar Auth0 callbacks
- [ ] Validación E2E por usuario
- [ ] **MVP LIVE 🚀**

**Progreso:** 80% completado | Listo para deploy

Ver plan detallado en: [docs/PLAN.md](./docs/PLAN.md)

---

## 🎨 Diseño

### Colores
- **Primario:** Azul Marino `#0A2463`
- **Secundario:** `#3E92CC`
- **Badges:**
  - Sin Verificar: `#FFA500` (naranja)
  - Verificada: `#28A745` (verde)

### Referencia Visual
Inspirado en: [ListingHive Demo](https://listinghive.hivepress.io/)

---

## 📝 Licencia

MIT License (pendiente definir)

---

## 📧 Contacto

- **Organización:** GDI Latam
- **Email:** sistema.gdi.abierto@gmail.com
- **Technical Lead:** Claude (Architect Agent + Team)

---

## 🔗 Links Útiles

- [HivePress Docs](https://hivepress.io/docs/)
- [Auth0 WordPress Guide](https://auth0.com/docs/quickstart/webapp/wordpress)
- [Railway Docs](https://docs.railway.app/)
- [WP-CLI Handbook](https://make.wordpress.org/cli/handbook/)
- [WordPress Salts Generator](https://api.wordpress.org/secret-key/1.1/salt/)

---

## 📜 Licencia

MIT License - Open Source

---

**Última actualización:** 2025-10-21
**Versión:** 1.0.0 (Ready for MVP Deploy)
**Build:** Cero1 Marketplace - GDI Latam
