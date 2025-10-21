# Cero1 - Marketplace de Soluciones para Ciudades

Marketplace simple y estático de soluciones tecnológicas para ciudades inteligentes.

## Stack

- **Frontend**: HTML, CSS, JavaScript vanilla
- **Backend**: Node.js + Express (mínimo)
- **Database**: JSON file
- **Hosting**: Railway

## Características

- ✅ Home page con grid de soluciones
- ✅ Filtro por 5 categorías
- ✅ Modal de detalles
- ✅ Panel admin para CRUD
- ✅ Upload de imágenes
- ✅ Bilingüe (ES/EN)
- ✅ Responsive design
- ✅ Blue navy color scheme (#0A2463)

## Instalación Local

```bash
# Instalar dependencias
npm install

# Configurar variables de entorno
cp .env.example .env
# Editar .env y configurar ADMIN_PASSWORD

# Iniciar servidor
npm start
```

Acceder a:
- **Home**: http://localhost:3000
- **Admin**: http://localhost:3000/admin.html

## Deployment en Railway

1. Conectar repositorio de GitHub
2. Railway detecta automáticamente Node.js
3. Configurar variable de entorno:
   - `ADMIN_PASSWORD`: tu password de admin

Railway ejecuta automáticamente `npm start`.

## Uso del Admin

1. Ir a `/admin.html`
2. Ingresar password de admin
3. Agregar/editar/eliminar soluciones
4. Upload de hasta 4 imágenes por solución (5MB máx c/u)

## Estructura del Proyecto

```
/
├── server.js              # Node.js server
├── package.json
├── data/
│   └── solutions.json     # Base de datos
├── public/
│   ├── index.html         # Home page
│   ├── admin.html         # Admin panel
│   ├── css/               # Estilos
│   ├── js/                # JavaScript
│   └── images/
│       └── solutions/     # Imágenes subidas
└── REQUIREMENTS.md        # Especificación completa
```

## Categorías

1. 🚗 Movilidad
2. 🏛️ Espacio Público
3. 💰 Fintech
4. ⚖️ LegalIA
5. 📊 Datos

## Licencia

MIT - Open Source
