# Guía de Deploy Local - Ecosistema GDI

## Puerto por Defecto: 3000

## Requisitos Previos
- Node.js >= 18.0.0
- npm

## Instalación

```bash
npm install
```

## Deploy Local

### Opción 1: Puerto por Defecto (8599)

```bash
node server.js
```

El servidor usa la variable de entorno `PORT` y por defecto arranca en puerto **3000**.

Para usar un puerto personalizado (ejemplo: **8599**), ejecuta:

**Windows (CMD):**
```cmd
set PORT=8599 && node server.js
```

**Windows (PowerShell):**
```powershell
$env:PORT=8599; node server.js
```

**Linux/Mac:**
```bash
PORT=8599 node server.js
```

### Opción 2: Script de Deploy Rápido

Edita `package.json` y agrega:

```json
"scripts": {
  "start": "node server.js",
  "dev": "node server.js",
  "deploy": "set PORT=8599 && node server.js"
}
```

Luego ejecuta:
```bash
npm run deploy
```

## Verificar Deploy

Una vez iniciado el servidor, deberías ver:

```
🚀 Cero1 Marketplace running on port 3000
📁 Data file: [...]/data/solutions.json
🖼️  Images directory: [...]/public/images/solutions
```

Abre en tu navegador:
```
http://localhost:3000
```

## Páginas Disponibles

- **Home:** http://localhost:3000/
- **Soluciones:** http://localhost:3000/solutions.html
- **Protocolo:** http://localhost:3000/protocol.html
- **Fundamentos:** http://localhost:3000/fundamentals.html
- **Contacto:** http://localhost:3000/contact.html
- **Admin:** http://localhost:3000/admin.html

## Admin Panel

**Credenciales por defecto:**
- Password: `admin123`

⚠️ **IMPORTANTE:** Cambia el password en producción usando la variable de entorno `ADMIN_PASSWORD`.

## Variables de Entorno

| Variable | Default | Descripción |
|----------|---------|-------------|
| `PORT` | `3000` | Puerto del servidor |
| `ADMIN_PASSWORD` | `admin123` | Password del panel admin |

## Detener el Servidor

Presiona `Ctrl + C` en la terminal donde está corriendo el servidor.

## Matar Procesos por Puerto (si es necesario)

**Windows:**
```cmd
# Ver qué proceso usa el puerto 3000
netstat -ano | findstr :3000

# Matar el proceso (reemplaza PID con el número que aparece)
taskkill /F /PID <PID>
```

**Linux/Mac:**
```bash
# Ver qué proceso usa el puerto 3000
lsof -i :3000

# Matar el proceso
kill -9 <PID>
```

## Estructura de Archivos

```
ecosistemaGDI/
├── public/           # Archivos estáticos
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── index.html
│   └── ...
├── data/            # Base de datos JSON
│   └── solutions.json
├── server.js        # Servidor Express
├── package.json
└── DEPLOY.md       # Este archivo
```

## Troubleshooting

### Error: Puerto en uso
```
Error: listen EADDRINUSE: address already in use :::3000
```

**Solución:** Mata el proceso que está usando el puerto o usa otro puerto.

### Error: No se encuentra el archivo solutions.json

El servidor crea automáticamente el archivo `data/solutions.json` si no existe.

### Imágenes no cargan

Verifica que existan los directorios:
- `public/images/site/` - Imágenes del sitio
- `public/images/solutions/` - Imágenes de soluciones (creadas por uploads)

## Notas

- El puerto **3003** está reservado para otro equipo, NO lo uses.
- Puerto por defecto: **3000**
- Puedes usar cualquier otro puerto libre con la variable de entorno `PORT`
- Los datos se guardan en `data/solutions.json` (formato JSON)
- Las imágenes subidas se guardan en `public/images/solutions/`
