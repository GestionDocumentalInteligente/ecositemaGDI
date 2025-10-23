# Deployment Guide - Railway

> **Guía completa para deploy del Ecosistema GDI Marketplace en Railway**

Esta guía te llevará paso a paso por el proceso de deployment en Railway, desde la configuración inicial hasta el monitoreo en producción.

## 📋 Tabla de Contenidos

- [Pre-requisitos](#pre-requisitos)
- [Configuración de Railway](#configuración-de-railway)
- [Variables de Entorno](#variables-de-entorno)
- [Primer Deploy](#primer-deploy)
- [Configuración de Dominio](#configuración-de-dominio)
- [Monitoreo y Logs](#monitoreo-y-logs)
- [Troubleshooting](#troubleshooting)
- [Rollback](#rollback)

## 🚀 Pre-requisitos

Antes de comenzar, asegúrate de tener:

- ✅ Cuenta en [Railway](https://railway.app)
- ✅ Repositorio en GitHub con el código del proyecto
- ✅ Node.js 18+ instalado localmente (para testing)
- ✅ Proyecto funcionando localmente (`npm start`)

## 🔧 Configuración de Railway

### Paso 1: Crear Proyecto en Railway

1. **Accede a Railway**
   - Ve a [railway.app](https://railway.app)
   - Inicia sesión con tu cuenta de GitHub

2. **Crear Nuevo Proyecto**
   - Click en "New Project"
   - Selecciona "Deploy from GitHub repo"
   - Autoriza Railway para acceder a tus repositorios

3. **Seleccionar Repositorio**
   - Busca: `ecosistema-gdi-marketplace`
   - Click en el repositorio
   - Railway comenzará a detectar automáticamente la configuración

### Paso 2: Configuración Automática

Railway detectará automáticamente:

```json
{
  "builder": "NIXPACKS",
  "buildCommand": "npm install",
  "startCommand": "npm start"
}
```

**Verifica en la configuración:**
- ✅ Build Command: `npm install`
- ✅ Start Command: `npm start` (desde package.json)
- ✅ Node Version: 18.x (desde engines en package.json)

## 🔐 Variables de Entorno

### Configurar Variables

1. **En el Dashboard de Railway**
   - Ve a tu proyecto
   - Click en la pestaña "Variables"
   - Click en "New Variable"

2. **Agregar Variables Requeridas**

| Variable | Valor Recomendado | Descripción |
|----------|-------------------|-------------|
| `PORT` | `3000` | Puerto del servidor (Railway lo asigna automáticamente si no se especifica) |
| `NODE_ENV` | `production` | Modo de producción |

**Ejemplo:**
```bash
PORT=3000
NODE_ENV=production
```

### Variables Opcionales

| Variable | Valor | Cuándo usarla |
|----------|-------|---------------|
| `ADMIN_PASSWORD` | `tu-password-seguro` | Si tienes admin panel |
| `LOG_LEVEL` | `info` | Para debugging |

**⚠️ Importante**: Railway inyecta automáticamente algunas variables:
- `RAILWAY_ENVIRONMENT` - Entorno actual
- `RAILWAY_GIT_COMMIT_SHA` - SHA del commit desplegado
- `RAILWAY_GIT_BRANCH` - Branch desplegada

## 🎯 Primer Deploy

### Deploy Automático

Railway hace deploy automático cuando:
- ✅ Haces push a la rama `main`
- ✅ Merges un Pull Request a `main`

### Deploy Manual

Si prefieres deploy manual:

1. **Desactiva Auto-Deploy**
   - Settings > Deploy > Auto Deploy: OFF

2. **Deploy Manual**
   - Ve a Deployments
   - Click en "Deploy Now"

### Proceso de Build

Railway ejecutará:

```bash
# 1. Clone del repositorio
git clone <tu-repo>

# 2. Install de dependencias
npm install

# 3. Start del servidor
npm start
```

**Tiempo estimado**: 2-5 minutos

### Verificar Deploy

1. **Check de Status**
   - En Dashboard, verifica que el status sea "Active" (verde)
   - Revisa los logs en tiempo real

2. **Probar la URL**
   ```bash
   # Railway genera una URL automática
   https://ecosistema-gdi-production-XXXX.up.railway.app
   ```

3. **Verificaciones Básicas**
   - ✅ Homepage carga correctamente
   - ✅ Soluciones se muestran
   - ✅ Filtros funcionan
   - ✅ Imágenes se cargan
   - ✅ Links funcionan
   - ✅ Cambio de idioma funciona

## 🌐 Configuración de Dominio

### Dominio Personalizado

Si quieres usar tu propio dominio:

1. **Agregar Dominio en Railway**
   - Settings > Domains
   - Click en "Add Custom Domain"
   - Ingresa: `ecosistema.gdilatam.com`

2. **Configurar DNS**

En tu proveedor de DNS (Cloudflare, GoDaddy, etc.):

```
Type: CNAME
Name: ecosistema (or @)
Value: <tu-proyecto>.up.railway.app
TTL: Auto or 3600
```

**Ejemplo para Cloudflare:**
```
CNAME  ecosistema  ecosistema-gdi-production-xxxx.up.railway.app
```

3. **Esperar Propagación**
   - Tiempo: 5 minutos a 48 horas
   - Verifica: `nslookup ecosistema.gdilatam.com`

4. **SSL Automático**
   - Railway provisiona SSL automáticamente
   - Certificado Let's Encrypt
   - No requiere configuración adicional

## 📊 Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Opción 1: Dashboard de Railway
# Settings > View Logs

# Opción 2: Railway CLI
railway logs
```

### Métricas Importantes

Railway Dashboard muestra:
- **CPU Usage**: Debería estar < 50% en normal operation
- **Memory Usage**: Node.js típicamente usa 100-300 MB
- **Network**: Requests por minuto
- **Uptime**: Debería ser 99.9%+

### Alertas Recomendadas

Configura alertas para:
- ❗ CPU > 80% por más de 5 minutos
- ❗ Memory > 500 MB
- ❗ Deploy failures
- ❗ Response time > 2 segundos

## 🔍 Troubleshooting

### Deploy Falla

**Síntoma**: Deploy failure, estado "Failed"

**Soluciones**:

1. **Revisar Logs**
   ```bash
   railway logs
   ```

2. **Verificar package.json**
   ```json
   {
     "scripts": {
       "start": "node server.js"
     },
     "engines": {
       "node": ">=18.0.0"
     }
   }
   ```

3. **Verificar dependencias**
   ```bash
   # Localmente
   rm -rf node_modules package-lock.json
   npm install
   npm start
   ```

### App No Responde

**Síntoma**: 503 Service Unavailable

**Soluciones**:

1. **Check PORT**
   ```javascript
   // server.js
   const PORT = process.env.PORT || 3000;
   app.listen(PORT, '0.0.0.0', () => {
     console.log(`Server running on port ${PORT}`);
   });
   ```

2. **Restart Service**
   - Dashboard > Settings > Restart

3. **Check Memory**
   - Si memory > 512 MB, considera upgrade de plan

### Imágenes No Cargan

**Síntoma**: Logos no se muestran

**Soluciones**:

1. **Verificar paths**
   ```json
   {
     "images": ["/images/solutions/logo.png"]
   }
   ```
   - Path debe ser absoluto desde `/`
   - Archivo debe existir en `public/images/solutions/`

2. **Verificar .gitignore**
   ```bash
   # Asegúrate de que las imágenes NO estén ignoradas
   # Si están en .gitignore, Railway no las tendrá
   ```

3. **Check permissions**
   ```bash
   ls -la public/images/solutions/
   # Todos los archivos deben tener permisos de lectura
   ```

### Performance Lento

**Síntoma**: Página carga lento (>3 segundos)

**Optimizaciones**:

1. **Comprimir imágenes**
   ```bash
   # Todas las imágenes < 200KB
   du -h public/images/solutions/*
   ```

2. **Minimize JSON**
   ```bash
   # data/solutions.json debe ser < 100 KB
   ls -lh data/solutions.json
   ```

3. **Enable caching**
   ```javascript
   // server.js
   app.use(express.static('public', {
     maxAge: '1d', // Cache for 1 day
     etag: true
   }));
   ```

## ⏪ Rollback

Si un deploy introduce bugs:

### Opción 1: Rollback en Railway

1. **Dashboard > Deployments**
2. **Buscar el deployment anterior exitoso**
3. **Click en los 3 puntos > "Redeploy"**

### Opción 2: Rollback via Git

```bash
# 1. Identificar commit anterior
git log --oneline

# 2. Revert al commit anterior
git revert HEAD

# 3. Push
git push origin main

# Railway auto-deploy
```

### Opción 3: Rollback de Datos

Si `solutions.json` tiene errores:

```bash
# 1. Restaurar desde backup
cp data/solutions.json.backup data/solutions.json

# 2. Commit y push
git add data/solutions.json
git commit -m "fix: rollback solutions.json to working version"
git push origin main
```

## 🔒 Seguridad

### Best Practices

1. **No commitas secrets**
   ```bash
   # .gitignore debe incluir:
   .env
   .env.local
   .env.*.local
   ```

2. **Environment Variables**
   - Usa Railway Variables para secrets
   - Nunca hardcodees passwords

3. **HTTPS Only**
   - Railway habilita HTTPS automáticamente
   - Fuerza HTTPS en production:
   ```javascript
   if (process.env.NODE_ENV === 'production') {
     app.use((req, res, next) => {
       if (req.header('x-forwarded-proto') !== 'https') {
         res.redirect(`https://${req.header('host')}${req.url}`);
       } else {
         next();
       }
     });
   }
   ```

## 📈 Escalabilidad

### Cuando Escalar

Considera escalar si:
- 🔥 CPU > 70% consistentemente
- 🔥 Memory > 400 MB
- 🔥 > 1000 requests/minuto
- 🔥 Response time > 1 segundo

### Opciones de Escalado en Railway

1. **Vertical Scaling**
   - Settings > Resources
   - Upgrade plan para más CPU/RAM

2. **Horizontal Scaling**
   - Railway Pro: Multiple replicas
   - Load balancing automático

## 🆘 Soporte

### Recursos de Railway

- **Docs**: [docs.railway.app](https://docs.railway.app)
- **Discord**: [Railway Community](https://discord.gg/railway)
- **Status**: [status.railway.app](https://status.railway.app)

### Soporte del Proyecto

- **GitHub Issues**: [Reportar bug](https://github.com/GDILatam/ecosistema-gdi-marketplace/issues)
- **Email**: Via contact form
- **Docs**: Este repositorio

## 📝 Checklist de Deploy

Antes de cada deploy a production:

```markdown
## Pre-Deploy
- [ ] Código funciona localmente (`npm start`)
- [ ] Tests pasan (si existen)
- [ ] JSON válido (`data/solutions.json`)
- [ ] Imágenes optimizadas (<200KB cada una)
- [ ] Variables de entorno configuradas
- [ ] Branch actualizada con main

## Post-Deploy
- [ ] Deploy exitoso (status "Active")
- [ ] Homepage carga correctamente
- [ ] Todas las categorías filtran bien
- [ ] Modal de soluciones funciona
- [ ] Imágenes se cargan
- [ ] Links externos funcionan
- [ ] Cambio de idioma funciona
- [ ] Responsive design ok (mobile)
- [ ] Logs sin errores críticos

## Monitoreo (primeros 30 min)
- [ ] CPU < 50%
- [ ] Memory < 300 MB
- [ ] Sin errores en logs
- [ ] Response time < 1s
```

---

**Última actualización**: Octubre 2025
**Mantenido por**: GDI Latam Development Team

**Deploy URL**: [https://ecosistema-gdi.railway.app](https://ecosistema-gdi.railway.app)

*"No pidas permiso. Construí."*
