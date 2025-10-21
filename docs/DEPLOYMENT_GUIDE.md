# Guía de Despliegue - Cero1 Marketplace

## 🎯 Objetivo

Desplegar Cero1 Marketplace en Railway desde GitHub, con MySQL persistente, Auth0 y volumen para imágenes.

---

## 📋 Prerequisitos

Antes de empezar, asegúrate de tener:

- [x] Cuenta en [Railway.app](https://railway.app) (Plan Pro recomendado)
- [x] Cuenta en [GitHub](https://github.com)
- [x] Auth0 Tenant configurado con:
  - Domain: `gdilatam.us.auth0.com`
  - Client ID: `rBIyrJCFZa6DKuCAEfgax1PchQ7XvDA0`
  - Client Secret: `sotsWYm65mjv9wHqfdwJ31EH676MzAWGUbbwINeNWNbjuIDPbKoNwPheaqHTgCV6`

---

## 🚀 Paso 1: Subir Código a GitHub

### 1.1 Inicializar Git

Abre tu terminal en la carpeta del proyecto:

```bash
cd "C:\Users\santi\OneDrive\Desktop\GDILatam -\HivePressWordPress"
```

Inicializa el repositorio:

```bash
git init
git add .
git commit -m "feat: initial Cero1 marketplace setup with HivePress + Auth0"
```

### 1.2 Crear Repositorio en GitHub

1. Ve a [github.com/new](https://github.com/new)
2. Nombre del repo: `cero1-marketplace`
3. Descripción: `Cero1 - El Marketplace de Soluciones para Ciudades`
4. Visibility: `Public` o `Private` (tu elección)
5. **NO** inicialices con README, .gitignore o licencia (ya los tienes)
6. Click "Create repository"

### 1.3 Push a GitHub

Copia los comandos que GitHub te muestra (sección "...or push an existing repository"):

```bash
git remote add origin https://github.com/[tu-usuario]/cero1-marketplace.git
git branch -M main
git push -u origin main
```

✅ **Checkpoint:** Tu código debe estar visible en GitHub.

---

## 🛤️ Paso 2: Crear Proyecto en Railway

### 2.1 Nuevo Proyecto

1. Ve a [railway.app/dashboard](https://railway.app/dashboard)
2. Click **"New Project"**
3. Selecciona **"Deploy from GitHub repo"**
4. Si es primera vez:
   - Click "Configure GitHub App"
   - Autoriza Railway en tu cuenta GitHub
   - Selecciona "All repositories" o solo `cero1-marketplace`
5. Selecciona el repo: `[tu-usuario]/cero1-marketplace`
6. Railway empieza a detectar el Dockerfile automáticamente

### 2.2 Agregar MySQL Database

Railway NO crea la base de datos automáticamente, debes agregarla:

1. En el proyecto Railway, click botón **"+ New"** (arriba a la derecha)
2. Selecciona **"Database"**
3. Elige **"Add MySQL"**
4. Railway crea el servicio MySQL y genera credenciales automáticamente

**Importante:** Railway inyecta estas variables automáticamente en tu servicio WordPress:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLDATABASE`

**NO las configures manualmente**, el `wp-config.php` las lee automáticamente.

---

## ⚙️ Paso 3: Configurar Variables de Entorno

### 3.1 Obtener la URL de Railway

Primero necesitas saber la URL que Railway generó para tu app:

1. En Railway dashboard, click en tu servicio WordPress (no el MySQL)
2. Ve a la pestaña **"Settings"**
3. Scroll hasta **"Domains"**
4. Si no hay dominio, click **"Generate Domain"**
5. Railway genera algo como: `cero1-production-xxxx.up.railway.app`

**Copia esta URL**, la necesitarás para los siguientes pasos.

### 3.2 Agregar Variables de Entorno

En Railway dashboard → Tu servicio WordPress → Pestaña **"Variables"**:

Click **"+ New Variable"** y agrega las siguientes (una por una o usa "Raw Editor"):

#### Variables de WordPress

```bash
WP_ENV=production
WP_HOME=https://cero1-production-xxxx.up.railway.app
WP_SITEURL=https://cero1-production-xxxx.up.railway.app
WP_DEBUG=false
WP_DEBUG_LOG=false
WP_DEBUG_DISPLAY=false
```

**Reemplaza** `cero1-production-xxxx.up.railway.app` con tu URL real de Railway.

#### Variables de Admin

```bash
WP_ADMIN_EMAIL=sistema.gdi.abierto@gmail.com
WP_ADMIN_PASSWORD=TuPasswordSuperSeguro123!
WP_ADMIN_USER=admin
```

**⚠️ IMPORTANTE:** Cambia `WP_ADMIN_PASSWORD` por una contraseña segura (mínimo 12 caracteres, mayúsculas, números, símbolos).

#### Variables de Auth0

```bash
AUTH0_DOMAIN=gdilatam.us.auth0.com
AUTH0_CLIENT_ID=rBIyrJCFZa6DKuCAEfgax1PchQ7XvDA0
AUTH0_CLIENT_SECRET=sotsWYm65mjv9wHqfdwJ31EH676MzAWGUbbwINeNWNbjuIDPbKoNwPheaqHTgCV6
AUTH0_REDIRECT_URI=https://cero1-production-xxxx.up.railway.app/wp-login.php
```

**Reemplaza** la URL en `AUTH0_REDIRECT_URI` con tu URL de Railway.

#### Variables de Seguridad (WordPress Salts)

Ve a: https://api.wordpress.org/secret-key/1.1/salt/

Copia las 8 líneas generadas y pégalas en Railway Variables (puedes usar el "Raw Editor" para pegar todas juntas):

```bash
AUTH_KEY='tu-key-generado-unico-xxxxxxxxx'
SECURE_AUTH_KEY='tu-key-generado-unico-xxxxxxxxx'
LOGGED_IN_KEY='tu-key-generado-unico-xxxxxxxxx'
NONCE_KEY='tu-key-generado-unico-xxxxxxxxx'
AUTH_SALT='tu-salt-generado-unico-xxxxxxxxx'
SECURE_AUTH_SALT='tu-salt-generado-unico-xxxxxxxxx'
LOGGED_IN_SALT='tu-salt-generado-unico-xxxxxxxxx'
NONCE_SALT='tu-salt-generado-unico-xxxxxxxxx'
```

#### Variables de Performance

```bash
WP_MEMORY_LIMIT=256M
WP_MAX_MEMORY_LIMIT=512M
WP_POST_REVISIONS=5
UPLOAD_MAX_FILESIZE=5M
POST_MAX_SIZE=10M
MAX_EXECUTION_TIME=300
```

### 3.3 Verificar Variables

En total deberías tener **~20 variables** configuradas. Verifica que:
- Todas las URLs apunten a tu Railway domain
- Las credenciales Auth0 estén correctas
- Los WordPress salts sean únicos (nunca uses los de ejemplo)

---

## 💾 Paso 4: Configurar Railway Volume

Railway necesita un volumen persistente para que las imágenes subidas NO se pierdan al redeploy.

### 4.1 Agregar Volume

1. En Railway dashboard → Tu servicio WordPress → Pestaña **"Settings"**
2. Scroll hasta la sección **"Volumes"**
3. Click **"+ Add Volume"**
4. Configuración:
   - **Mount Path:** `/var/www/html/wp-content/uploads`
   - **Size:** `5GB` (puedes ajustar después si necesitas más)
5. Click **"Add"**

✅ **Checkpoint:** Deberías ver el volumen listado en "Volumes".

---

## 🚢 Paso 5: Deploy Inicial

Railway debería haber iniciado automáticamente el primer deploy al detectar el Dockerfile.

### 5.1 Monitorear el Build

1. En Railway dashboard → Tu servicio WordPress → Pestaña **"Deployments"**
2. Haz click en el deployment más reciente (en progreso)
3. Verás los logs en tiempo real

**Proceso esperado:**
```
Building Dockerfile...
Step 1/15 : FROM wordpress:6.4-apache
...
Successfully built xxxxx
Deploying...
Deployment live at: https://cero1-production-xxxx.up.railway.app
```

**Tiempo estimado:** 3-5 minutos para el primer deploy.

### 5.2 Verificar que Levantó

Una vez que el deploy dice "SUCCESS":

1. Abre tu navegador
2. Ve a: `https://cero1-production-xxxx.up.railway.app`

**Deberías ver:**
- La home de WordPress (puede estar vacía o con errores de theme, es normal en el primer deploy)
- O un mensaje de error de conexión DB → revisar sección Troubleshooting

---

## 🔐 Paso 6: Configurar Auth0 Callback URLs

Para que Auth0 funcione, debes agregar tu Railway URL como callback permitido.

### 6.1 En Auth0 Dashboard

1. Ve a [Auth0 Dashboard](https://manage.auth0.com)
2. Applications → **"Marketplace"** (o el nombre de tu app)
3. Settings tab
4. En **"Application URIs"**, agrega:

**Allowed Callback URLs:**
```
https://cero1-production-xxxx.up.railway.app/wp-login.php
```

**Allowed Logout URLs:**
```
https://cero1-production-xxxx.up.railway.app
```

**Allowed Web Origins:**
```
https://cero1-production-xxxx.up.railway.app
```

5. Scroll hasta abajo y click **"Save Changes"**

✅ **Checkpoint:** Auth0 ahora acepta tu Railway domain.

---

## ✅ Paso 7: Validar Instalación

### 7.1 Acceder al Admin

1. Ve a: `https://cero1-production-xxxx.up.railway.app/wp-admin`

**Escenario 1:** Redirige a Auth0
- Si querés entrar como admin: `https://cero1-production-xxxx.up.railway.app/wp-admin?native_login=1`
- Usuario: `admin`
- Password: el que configuraste en `WP_ADMIN_PASSWORD`

**Escenario 2:** Muestra pantalla de instalación de WordPress
- Significa que el `entrypoint.sh` no corrió (ver Troubleshooting)

### 7.2 Verificar que HivePress se Instaló

Una vez dentro del admin:

1. Ve a **Plugins**
2. Deberías ver:
   - ✅ HivePress (activo)
   - ✅ Polylang (activo)
   - ✅ Cero1 - HivePress Auth0 Integration (activo)

3. Ve a **Appearance → Themes**
   - Tema activo: **"Cero1 - Marketplace Child"**

4. Ve a **Listings → Categories**
   - Deberías ver las 5 categorías:
     - 🚗 Movilidad
     - 🏛️ Espacio Público
     - 💰 Fintech
     - ⚖️ LegalIA
     - 📊 Datos

### 7.3 Probar Auth0 Login

1. Abre una ventana de incógnito
2. Ve a: `https://cero1-production-xxxx.up.railway.app/wp-admin`
3. Debería redirigir a Auth0
4. Loguéate con tu cuenta Auth0 (Google, email, etc.)
5. Después del login, deberías volver a WordPress

Si funciona:
- Ve a **Users** en el admin
- Deberías ver tu usuario creado automáticamente con rol "Contributor"

---

## 🐛 Troubleshooting

### Error: "Error establishing a database connection"

**Causa:** Railway no inyectó las variables de MySQL, o el servicio MySQL no está corriendo.

**Solución:**
1. Ve a Railway dashboard → Proyecto → Verifica que el servicio MySQL esté **"Active"**
2. En Variables del servicio WordPress, verifica que existan las variables:
   - `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
3. Si no existen, Railway NO las detectó. Solución:
   - Ve al servicio MySQL → Settings → **"Connect"**
   - Copia las credenciales y agrégalas manualmente en el servicio WordPress

### Error: "Auth0 authentication failed"

**Causa:** Callback URL no configurado en Auth0, o credenciales incorrectas.

**Solución:**
1. Verifica que en Auth0 Dashboard → Marketplace → Settings:
   - Allowed Callback URLs incluya tu Railway URL + `/wp-login.php`
2. Verifica en Railway Variables que:
   - `AUTH0_DOMAIN`, `AUTH0_CLIENT_ID`, `AUTH0_CLIENT_SECRET` sean correctos
   - `AUTH0_REDIRECT_URI` apunte a tu Railway URL

### WordPress muestra pantalla de instalación

**Causa:** El script `entrypoint.sh` no corrió, o falló.

**Solución:**
1. Ve a Railway → Tu servicio → Deployments → Logs
2. Busca líneas con:
   - `🚀 Cero1 - Starting WordPress setup...`
   - `✅ WordPress installed successfully!`
3. Si NO aparecen, el entrypoint falló. Posibles causas:
   - El Dockerfile no copió el `entrypoint.sh` correctamente
   - Permisos incorrectos (`chmod +x` faltante)

**Fix:** Redeploy forzado:
```bash
git commit --allow-empty -m "trigger redeploy"
git push
```

### Imágenes no persisten después de redeploy

**Causa:** Railway Volume no configurado, o path incorrecto.

**Solución:**
1. Verifica en Railway → Settings → Volumes:
   - Mount Path: `/var/www/html/wp-content/uploads` (exacto)
2. Si está mal, bórralo y créalo de nuevo
3. Redeploy para que tome el cambio

### Tema no se activa (muestra tema default)

**Causa:** El child theme no se copió correctamente en el Dockerfile.

**Solución:**
1. Ve a Railway logs y busca: `wp theme activate hivepress-child`
2. Si dice "theme not found":
   - Verifica que la carpeta `wp-content/themes/hivepress-child/` exista en tu repo
   - Verifica que el Dockerfile tenga la línea:
     ```dockerfile
     COPY wp-content/themes/hivepress-child /var/www/html/wp-content/themes/hivepress-child
     ```

---

## 🎉 ¡Deploy Exitoso!

Si llegaste hasta acá y todo funciona:

1. ✅ WordPress corriendo en Railway
2. ✅ MySQL persistente
3. ✅ Auth0 login funcional
4. ✅ HivePress instalado con 5 categorías
5. ✅ Child theme activo
6. ✅ Volumen para imágenes configurado

**Próximos pasos:**
- Crear tus primeras soluciones como admin
- Invitar a usuarios para que publiquen (quedarán en "pending")
- Aprobar soluciones desde wp-admin

---

## 📞 Soporte

Si algo no funciona:
1. Revisa los logs de Railway (Deployments → Click en el deploy → Logs)
2. Verifica las variables de entorno (compara con `.env.example`)
3. Contacta a: sistema.gdi.abierto@gmail.com

**Referencias:**
- [Railway Docs](https://docs.railway.app/)
- [Auth0 Docs](https://auth0.com/docs)
- [HivePress Docs](https://hivepress.io/docs/)

---

**Última actualización:** 2025-10-21
**Versión de la guía:** 1.0.0
