# 🤖 Guía del Agente Especialista - Gestión de Startups en Ecosistema GDI

## 📋 Índice
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Estructura de Datos](#estructura-de-datos)
3. [Proceso: Agregar Startup](#proceso-agregar-startup)
4. [Proceso: Actualizar Startup](#proceso-actualizar-startup)
5. [Proceso: Eliminar Startup](#proceso-eliminar-startup)
6. [Investigación Web](#investigación-web)
7. [Validaciones Críticas](#validaciones-críticas)
8. [Checklist de Operaciones](#checklist-de-operaciones)

---

## 🎯 Resumen Ejecutivo

### Base de Datos
- **Archivo:** `data/solutions.json`
- **Formato:** JSON Array
- **Ubicación actual:** 14 startups
- **Límite recomendado:** ~100 startups

### Archivos de Imágenes
- **Directorio:** `public/images/solutions/`
- **Formato:** PNG (preferido) o JPG
- **Tamaño:** < 200KB recomendado
- **Dimensiones:** 400x400px a 800x800px (cuadrado)
- **Naming:** lowercase, ej: `companyname.png`

### Categorías Disponibles (6)

| Code | Emoji | Nombre ES | Nombre EN | Descripción |
|------|-------|-----------|-----------|-------------|
| `government` | 🏛️ | Gobierno | Government | GovTech, transparencia, contratación pública |
| `identity` | 🆔 | Identidad | Identity | Identidad digital, credenciales, verificación |
| `fintech` | 💰 | Fintech | Fintech | Pagos, inclusión financiera, dinero digital |
| `health` | 🏥 | Salud | Health | HealthTech, salud mental, bienestar |
| `city` | 🌆 | Ciudad | City | Tecnología urbana, medio ambiente, espacios públicos |
| `mobility` | 🚗 | Movilidad | Mobility | Movilidad urbana, transporte, estacionamiento |

---

## 📊 Estructura de Datos

### Esquema JSON Completo

```json
{
  "id": "uuid-v4-generado",
  "name": "Nombre de la Empresa",
  "category": "government|identity|fintech|health|city|mobility",
  "description": {
    "es": "Descripción en español de 150-250 caracteres. 2-3 oraciones concisas.",
    "en": "English description of 150-250 characters. 2-3 concise sentences."
  },
  "website": "https://company.com",
  "email": "contact@company.com",
  "phone": "+54 11 1234-5678",
  "linkedinFounders": [
    "https://www.linkedin.com/in/founder1/",
    "https://www.linkedin.com/in/founder2/"
  ],
  "images": ["/images/solutions/companyname.png"],
  "createdAt": "2025-10-23T00:00:00.000Z",
  "updatedAt": "2025-10-23T00:00:00.000Z"
}
```

### Reglas de Validación

| Campo | Requerido | Tipo | Reglas |
|-------|-----------|------|--------|
| `id` | ✅ SÍ | String (UUID v4) | Debe ser único, generar con `uuid.v4()` |
| `name` | ✅ SÍ | String | 2-50 caracteres, nombre oficial de la empresa |
| `category` | ✅ SÍ | Enum | Exactamente uno de los 6 códigos válidos |
| `description.es` | ✅ SÍ | String | 150-250 chars, 2-3 oraciones, sin marketing fluff |
| `description.en` | ✅ SÍ | String | 150-250 chars, traducción precisa del español |
| `website` | ✅ SÍ | URL | Debe empezar con `http://` o `https://` |
| `email` | ❌ NO | String\|null | Formato email válido o `null` |
| `phone` | ❌ NO | String\|null | Incluir código de país, ej: `+54 11 1234-5678` |
| `linkedinFounders` | ❌ NO | Array | URLs de LinkedIn válidas, puede ser `[]` |
| `images` | ❌ NO | Array | Paths relativos a `/images/solutions/`, puede ser `[]` |
| `createdAt` | ✅ SÍ | String (ISO 8601) | Timestamp de creación |
| `updatedAt` | ✅ SÍ | String (ISO 8601) | Timestamp de última actualización |

---

## ➕ Proceso: AGREGAR Startup

### FASE 1: Recibir Información del Usuario

**Input Mínimo Necesario:**
1. ✅ **URL del sitio web** de la startup
2. ✅ **Imagen/logo** (archivo PNG/JPG)

**Opcional (el agente puede investigar):**
- Categoría (el agente puede inferir)
- Email y teléfono (el agente puede buscar)
- LinkedIn de founders (el agente puede encontrar)

---

### FASE 2: Investigación Web (Automatizada)

#### 2.1 Fetch del Sitio Web

```bash
# Usar WebFetch tool con el URL proporcionado
WebFetch(url: "https://company.com", prompt: "Extract company information")
```

**Información a Extraer:**
- ✅ Nombre oficial de la empresa
- ✅ Descripción del producto/servicio (qué hacen)
- ✅ Tecnologías utilizadas
- ✅ Métricas de impacto (usuarios, ciudades, países)
- ✅ Propuesta de valor principal
- ✅ Sector/industria
- ❓ Email de contacto
- ❓ Teléfono
- ❓ Enlaces a LinkedIn de founders

#### 2.2 Análisis de Contenido

**Prompt Sugerido para WebFetch:**
```
Analiza este sitio web de una startup y extrae:

1. Nombre oficial de la empresa
2. ¿Qué problema resuelven? (1-2 oraciones)
3. ¿Cómo lo resuelven? (tecnología/método específico)
4. Métricas de impacto (usuarios, ciudades, ingresos, etc.)
5. Sector principal (gobierno, identidad digital, fintech, salud, ciudad inteligente, movilidad)
6. Información de contacto (email, teléfono)
7. URLs de LinkedIn de fundadores (buscar en sección "Team", "About", "Nosotros")

Formato de respuesta:
- Nombre: [nombre]
- Problema: [descripción]
- Solución: [descripción]
- Métricas: [cifras específicas]
- Sector: [sector inferido]
- Email: [email o "No encontrado"]
- Teléfono: [teléfono o "No encontrado"]
- Founders LinkedIn: [URLs o "No encontrado"]
```

#### 2.3 Determinar Categoría

**Mapeo de Sectores a Categorías:**

| Palabras Clave | Categoría Sugerida |
|----------------|-------------------|
| gobierno, contratación pública, transparencia, licitaciones, procurement, civic tech, gov tech | `government` |
| identidad digital, credenciales, verificación, KYC, blockchain identity, self-sovereign | `identity` |
| pagos, finanzas, inclusión financiera, dinero digital, wallet, remesas, banking | `fintech` |
| salud mental, telemedicina, wellness, diagnóstico, hospital, clínica, pacientes | `health` |
| ciudad inteligente, medio ambiente, incendios, aire, reciclaje, sostenibilidad, urbano | `city` |
| transporte, movilidad, estacionamiento, tráfico, micro-movilidad, rutas, viajes | `mobility` |

**Regla de Decisión:**
- Si hay duda entre 2 categorías, elegir la más específica
- Si es multi-sector, elegir el impacto principal (ejemplo: "fintech para gobierno" → `government`)

---

### FASE 3: Generar Descripciones

#### 3.1 Formato de Descripción

**Estructura Recomendada (3 oraciones):**
```
[Qué hace la empresa]. [Cómo lo hace / tecnología usada]. [Impacto / métrica relevante].
```

**Ejemplo 1 - SOVRA:**
```
ES: "The Digital Trust Stack para instituciones. Creadores de QuarkID y OS City.
     Stack completo con SovraGov, SovraID, y SovraWallet.
     Implementado en Buenos Aires con 700,000+ ciudadanos usando QuarkID."

EN: "The Digital Trust Stack for institutions. Creators of QuarkID and OS City.
     Complete stack with SovraGov, SovraID, and SovraWallet.
     Implemented in Buenos Aires with 700,000+ citizens using QuarkID."
```

**Ejemplo 2 - B2Gov:**
```
ES: "Inteligencia de datos para transparencia pública. Agrega y estandariza millones
     de datos fragmentados de compras públicas en LATAM y Caribe, transformándolos en
     información centralizada y en tiempo real. Ayuda a abrir y analizar procesos de
     compra gubernamental."

EN: "Data intelligence for public transparency. Aggregates and standardizes millions of
     fragmented public procurement data points across Latin America and the Caribbean,
     transforming them into centralized real-time information. Helps open and analyze
     government purchasing processes."
```

#### 3.2 Reglas de Escritura

**✅ SÍ:**
- Usar métricas específicas (700,000+ ciudadanos, 30+ ciudades)
- Mencionar tecnologías concretas (IA, blockchain, satélites, etc.)
- Enfocarse en valor, no en marketing
- Ser conciso y directo
- Incluir impacto medible

**❌ NO:**
- Marketing fluff ("la mejor solución", "revolucionario")
- Palabras vacías ("innovador", "disruptivo" sin contexto)
- Descripciones genéricas
- Más de 250 caracteres
- Jerga técnica innecesaria

#### 3.3 Traducción ES → EN

**Puntos Clave:**
- Mantener el mismo nivel de detalle
- No agregar ni quitar información
- Traducir métricas exactamente igual
- Mantener nombres de productos en original (ej: "QuarkID" se mantiene igual)

---

### FASE 4: Preparar Imagen

#### 4.1 Recibir Imagen del Usuario

**Formatos Aceptados:**
- PNG (preferido - soporta transparencia)
- JPG/JPEG

**Validaciones:**
- Tamaño: < 5MB (Railway upload limit)
- Dimensiones recomendadas: 400x400 a 800x800 px (cuadrado)
- Aspecto: Preferentemente cuadrado (1:1)

#### 4.2 Nombrar Archivo

**Convención de Nombres:**
```bash
# Formato: nombre-empresa-lowercase.png
# Ejemplos correctos:
sovra.png
b2gov.png
quienxmi.png  # Sin espacios ni caracteres especiales
satellitesonfire.png  # Sin guiones ni espacios

# Ejemplos incorrectos:
Sovra.png  # ❌ Mayúscula
B2 Gov.png  # ❌ Espacio
sovra_new.png  # ❌ Guión bajo innecesario
```

**Comando para Copiar Imagen:**
```bash
# El usuario proporcionará la ruta de origen
cp /path/to/logo.png public/images/solutions/companyname.png
```

---

### FASE 5: Generar UUID

**Método en Node.js:**
```bash
node -e "console.log(require('crypto').randomUUID())"
# Output ejemplo: f3e4d5c6-a7b8-4c9d-0e1f-2a3b4c5d6e7f
```

**Importante:**
- Generar UUID v4 (aleatorio)
- Verificar que sea único en `solutions.json`
- Guardar para usar en el objeto JSON

---

### FASE 6: Construir Objeto JSON

**Template:**
```json
{
  "id": "[UUID generado en Fase 5]",
  "name": "[Nombre extraído del sitio web]",
  "category": "[Categoría determinada en Fase 2.3]",
  "description": {
    "es": "[Descripción generada en español]",
    "en": "[Descripción traducida al inglés]"
  },
  "website": "[URL proporcionado por el usuario]",
  "email": "[Email encontrado o null]",
  "phone": "[Teléfono encontrado o null]",
  "linkedinFounders": ["[URLs encontradas o array vacío]"],
  "images": ["/images/solutions/[nombre-archivo].png"],
  "createdAt": "[Timestamp ISO 8601 actual]",
  "updatedAt": "[Mismo timestamp que createdAt]"
}
```

**Generar Timestamps:**
```bash
node -e "console.log(new Date().toISOString())"
# Output: 2025-10-23T15:30:00.000Z
```

---

### FASE 7: Insertar en JSON

#### 7.1 Leer Archivo Actual

```bash
# Leer data/solutions.json completo
Read(file_path: "data/solutions.json")
```

#### 7.2 Determinar Posición

**Opciones de Inserción:**
1. **Al final del array** (más simple, recomendado)
2. **Por orden alfabético** (si se desea mantener orden)
3. **Agrupado por categoría** (requiere más lógica)

**Recomendación:** Insertar al final antes del `]` final

#### 7.3 Editar Archivo

**Usando Edit Tool:**
```javascript
// Encontrar el último objeto en el array
// Agregar una coma después del último }
// Insertar el nuevo objeto
// Mantener el ] final

Edit(
  file_path: "data/solutions.json",
  old_string: "  }\n]",  // Último objeto del array
  new_string: "  },\n  {\n    [nuevo objeto aquí]\n  }\n]"
)
```

**Formato de Indentación:**
- 2 espacios por nivel
- Sin tabs
- Mantener consistencia con archivo existente

---

### FASE 8: Validar JSON

**Validación de Sintaxis:**
```bash
node -e "JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8')); console.log('✓ JSON is valid')"
```

**Si hay error:**
- Revisar comas finales
- Verificar comillas dobles (no simples)
- Comprobar corchetes y llaves balanceadas
- Escapar caracteres especiales en strings (`\"`, `\n`)

---

### FASE 9: Commit y Push

#### 9.1 Git Add

```bash
git add data/solutions.json public/images/solutions/[companyname].png
```

#### 9.2 Git Commit

**Formato de Mensaje:**
```bash
git commit -m "feat: add [CompanyName] to [Category] category

Added new solution to ecosystem:
- Name: [CompanyName]
- Category: [Category]
- Website: [URL]
- Key features: [brief description]

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

#### 9.3 Verificar y Opcional Push

```bash
# Verificar estado
git status

# Si el usuario desea desplegar inmediatamente:
git push origin main
```

**Nota:** Railway auto-despliega en 2-5 minutos tras push a `main`

---

## 🔄 Proceso: ACTUALIZAR Startup

### FASE 1: Identificar Startup

**Por Nombre:**
```bash
# Buscar por nombre en solutions.json
Grep(pattern: "\"name\": \"CompanyName\"", path: "data/solutions.json")
```

**Por ID:**
```bash
# Buscar por UUID
Grep(pattern: "\"id\": \"uuid-aquí\"", path: "data/solutions.json")
```

### FASE 2: Determinar Campos a Actualizar

**Campos Comúnmente Actualizados:**
- ✏️ `description` - Nueva descripción o corrección
- 🔗 `website` - Nueva URL
- 📧 `email` - Nuevo contacto
- 📞 `phone` - Nuevo teléfono
- 👥 `linkedinFounders` - Agregar/actualizar founders
- 🖼️ `images` - Nuevo logo
- 🏷️ `category` - Re-categorización (raro)

### FASE 3: Si se Actualiza Descripción

**Repetir Fases 2-3 del proceso de AGREGAR:**
1. Fetch del sitio web nuevamente
2. Generar nueva descripción
3. Traducir al inglés

### FASE 4: Editar JSON

**Usando Edit Tool:**
```javascript
Edit(
  file_path: "data/solutions.json",
  old_string: "[objeto completo antiguo]",
  new_string: "[objeto completo actualizado]"
)
```

**IMPORTANTE:**
- Actualizar campo `updatedAt` con nuevo timestamp
- Mantener `createdAt` sin cambios
- Preservar el `id` (nunca cambiar)

### FASE 5: Si se Actualiza Imagen

**Pasos:**
1. Recibir nueva imagen del usuario
2. Copiar con el mismo nombre (sobreescribir) o nuevo nombre
3. Si es nuevo nombre, actualizar campo `images` en JSON
4. Opcional: eliminar imagen antigua si cambió el nombre

```bash
# Copiar nueva imagen
cp /path/to/new-logo.png public/images/solutions/companyname.png

# Si cambió el nombre, eliminar antigua
rm public/images/solutions/old-name.png
```

### FASE 6: Validar, Commit, Push

**Igual que FASE 8-9 de AGREGAR:**
1. Validar JSON
2. Git add
3. Git commit con mensaje:
   ```
   fix: update [CompanyName] [field] information

   Updated [specific change description]
   ```

---

## ❌ Proceso: ELIMINAR Startup

### FASE 1: Confirmar con Usuario

**Antes de eliminar, preguntar:**
- ¿Estás seguro de eliminar [CompanyName]?
- Razón de eliminación (para documentar)

### FASE 2: Identificar Startup

**Por Nombre:**
```bash
Grep(pattern: "\"name\": \"CompanyName\"", path: "data/solutions.json", output_mode: "content", -B: 2, -A: 20)
```

**Información a Recopilar:**
- ID de la startup
- Path de la imagen (campo `images`)
- Categoría (para estadísticas post-eliminación)

### FASE 3: Eliminar del JSON

**Usando Edit Tool:**
```javascript
// Si está al inicio o medio del array
Edit(
  file_path: "data/solutions.json",
  old_string: "  {\n    [objeto completo],\n  },\n  {",
  new_string: "  {"
)

// Si está al final del array
Edit(
  file_path: "data/solutions.json",
  old_string: "  },\n  {\n    [objeto completo]\n  }\n]",
  new_string: "  }\n]"
)
```

**IMPORTANTE:**
- Mantener sintaxis JSON válida
- Eliminar comas huérfanas
- Preservar formato e indentación

### FASE 4: Eliminar Imagen

```bash
rm public/images/solutions/[companyname].png
```

**Si no existe:**
- No es error crítico
- Documentar en commit message

### FASE 5: Validar JSON

```bash
node -e "JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8')); console.log('✓ JSON is valid')"
```

### FASE 6: Commit

**Formato:**
```bash
git commit -m "chore: remove [CompanyName] from ecosystem

Removed startup from [Category] category.
Reason: [razón proporcionada por usuario]

Changes:
- Removed from data/solutions.json
- Deleted logo: [filename].png

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## 🔍 Investigación Web - Guía Avanzada

### Técnicas de Extracción

#### 1. Sitio Web Principal

**Secciones a Revisar:**
- Homepage (hero section)
- About / Nosotros / Acerca de
- Solutions / Soluciones / Productos
- Team / Equipo / Nosotros
- Contact / Contacto

**Datos a Extraer:**
```
✅ Nombre oficial (header, footer, meta tags)
✅ Tagline o eslogan (suele resumir qué hacen)
✅ Descripción de producto (sección "What we do")
✅ Métricas de impacto (buscar números: "1M+ users", "30 cities")
✅ Casos de uso (sección "Use cases" o "Clientes")
✅ Tecnología (sección "How it works" o "Technology")
✅ Email (footer, contacto, info@, hello@, contact@)
✅ Teléfono (footer, contacto)
✅ LinkedIn founders (sección "Team" - buscar íconos de LinkedIn)
```

#### 2. Inferir Información Faltante

**Si no hay email explícito:**
- Probar patrones comunes: `info@domain.com`, `contact@domain.com`, `hello@domain.com`
- Indicar en descripción: "No encontrado en sitio web"

**Si no hay LinkedIn de founders:**
- Buscar nombres de founders en el sitio
- Construir URLs de búsqueda de LinkedIn (no buscar directamente)
- Dejar array vacío si no se encuentra

**Si la descripción es vaga:**
- Buscar en footer del sitio (suele haber descripción resumida)
- Buscar meta description tags
- Buscar en sección "About"

#### 3. Validar Información

**Checklist de Calidad:**
- [ ] ¿La descripción explica QUÉ hace la empresa?
- [ ] ¿La descripción explica CÓMO lo hace?
- [ ] ¿Hay métricas concretas de impacto?
- [ ] ¿El nombre es el oficial (no un nickname)?
- [ ] ¿El website es el dominio principal (no subdomain interno)?
- [ ] ¿El email y teléfono son de contacto oficial (no personal)?

---

## ✅ Validaciones Críticas

### Pre-Inserción

**Antes de agregar al JSON, verificar:**

```bash
# 1. ¿El nombre ya existe?
Grep(pattern: "\"name\": \"[CompanyName]\"", path: "data/solutions.json")
# Debe retornar 0 resultados

# 2. ¿El UUID es único?
Grep(pattern: "\"id\": \"[UUID]\"", path: "data/solutions.json")
# Debe retornar 0 resultados

# 3. ¿El website ya existe?
Grep(pattern: "\"website\": \"[URL]\"", path: "data/solutions.json")
# Debe retornar 0 resultados (evitar duplicados)

# 4. ¿La imagen existe?
ls public/images/solutions/[companyname].png
# Debe existir el archivo

# 5. ¿La imagen es < 5MB?
du -h public/images/solutions/[companyname].png
# Debe ser < 5M
```

### Post-Inserción

**Después de editar, verificar:**

```bash
# 1. JSON sintácticamente válido
node -e "JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8'))"

# 2. Contar total de startups
node -e "console.log(JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8')).length)"

# 3. Verificar que la nueva startup esté incluida
Grep(pattern: "\"name\": \"[CompanyName]\"", path: "data/solutions.json", output_mode: "content")
```

### Validaciones de Calidad

**Descripción:**
- ✅ Longitud: 150-250 caracteres (verificar con `.length`)
- ✅ No contiene HTML o markdown
- ✅ Sin emojis (se agregan automáticamente por categoría)
- ✅ Sin saltos de línea innecesarios (ok usar `\n` entre oraciones)
- ✅ Traducción EN es diferente de ES (no copiar-pegar)

**URLs:**
- ✅ Website empieza con `http://` o `https://`
- ✅ LinkedIn URLs empiezan con `https://www.linkedin.com/in/` o `https://linkedin.com/in/`
- ✅ No hay URLs rotas (404)

**Categoría:**
- ✅ Es uno de los 6 códigos válidos (government, identity, fintech, health, city, mobility)
- ✅ Es la categoría MÁS ESPECÍFICA (no usar "city" para todo)

---

## 📋 Checklist de Operaciones

### ✅ AGREGAR Startup - Checklist Completo

```
PREPARACIÓN:
[ ] Recibir URL del sitio web del usuario
[ ] Recibir archivo de imagen/logo del usuario

INVESTIGACIÓN:
[ ] Ejecutar WebFetch en el sitio web
[ ] Extraer nombre oficial
[ ] Extraer descripción de qué hace la empresa
[ ] Extraer métricas de impacto
[ ] Determinar categoría (government/identity/fintech/health/city/mobility)
[ ] Buscar email de contacto
[ ] Buscar teléfono de contacto
[ ] Buscar LinkedIn de founders

GENERAR CONTENIDO:
[ ] Escribir descripción en español (150-250 chars, 2-3 oraciones)
[ ] Traducir descripción al inglés
[ ] Validar que ambas descripciones sean similares en contenido
[ ] Verificar que incluyan métricas específicas

PREPARAR ARCHIVOS:
[ ] Generar UUID v4 único
[ ] Verificar que UUID no exista en solutions.json
[ ] Nombrar imagen: lowercase, sin espacios (ej: companyname.png)
[ ] Copiar imagen a public/images/solutions/
[ ] Verificar que imagen sea < 5MB

CONSTRUIR OBJETO JSON:
[ ] Crear objeto con todos los campos requeridos
[ ] Campo "id": UUID generado
[ ] Campo "name": Nombre oficial extraído
[ ] Campo "category": Categoría determinada
[ ] Campo "description": Objeto con "es" y "en"
[ ] Campo "website": URL proporcionado por usuario
[ ] Campo "email": Email encontrado o null
[ ] Campo "phone": Teléfono encontrado o null
[ ] Campo "linkedinFounders": Array de URLs o []
[ ] Campo "images": Array con path de imagen
[ ] Campo "createdAt": Timestamp ISO 8601 actual
[ ] Campo "updatedAt": Mismo timestamp que createdAt

EDITAR JSON:
[ ] Leer data/solutions.json completo
[ ] Insertar nuevo objeto al final del array (antes de ])
[ ] Agregar coma después del objeto anterior
[ ] Mantener indentación de 2 espacios
[ ] Sin tabs, solo espacios

VALIDAR:
[ ] Ejecutar validación de sintaxis JSON
[ ] Verificar que no hay errores
[ ] Contar que el total de startups aumentó en 1
[ ] Grep para confirmar que la nueva startup está incluida

GIT COMMIT:
[ ] git add data/solutions.json public/images/solutions/[name].png
[ ] Crear commit con mensaje: "feat: add [Name] to [Category] category"
[ ] Incluir descripción detallada en el commit message
[ ] git status para verificar
[ ] (Opcional) git push origin main para desplegar

POST-OPERACIÓN:
[ ] Informar al usuario que la startup fue agregada exitosamente
[ ] Proporcionar resumen: nombre, categoría, URL
[ ] Indicar que cambios están en commit, listos para push
```

### ✅ ACTUALIZAR Startup - Checklist Completo

```
IDENTIFICACIÓN:
[ ] Recibir nombre o ID de la startup a actualizar
[ ] Buscar startup en solutions.json (por nombre o ID)
[ ] Confirmar que existe
[ ] Identificar campos a actualizar

ACTUALIZACIÓN DE DESCRIPCIÓN (si aplica):
[ ] Ejecutar WebFetch en el sitio web nuevamente
[ ] Generar nueva descripción en español
[ ] Traducir nueva descripción al inglés
[ ] Verificar longitud (150-250 caracteres)

ACTUALIZACIÓN DE OTROS CAMPOS (si aplica):
[ ] Nuevo website: validar formato URL
[ ] Nuevo email: validar formato email
[ ] Nuevo teléfono: incluir código de país
[ ] Nuevos founders: validar URLs de LinkedIn
[ ] Nueva categoría: validar que sea uno de los 6 códigos

ACTUALIZACIÓN DE IMAGEN (si aplica):
[ ] Recibir nueva imagen del usuario
[ ] Copiar a public/images/solutions/ (mismo nombre o nuevo)
[ ] Si es nuevo nombre, actualizar campo "images" en JSON
[ ] Si es nuevo nombre, eliminar imagen antigua
[ ] Verificar que nueva imagen sea < 5MB

EDITAR JSON:
[ ] Leer objeto completo actual de la startup
[ ] Modificar campos necesarios
[ ] Actualizar campo "updatedAt" con nuevo timestamp
[ ] Mantener campo "createdAt" sin cambios
[ ] Mantener campo "id" sin cambios
[ ] Usar Edit tool para reemplazar objeto completo

VALIDAR:
[ ] Ejecutar validación de sintaxis JSON
[ ] Verificar que no hay errores
[ ] Grep para confirmar que cambios se aplicaron

GIT COMMIT:
[ ] git add data/solutions.json (y imagen si aplica)
[ ] Crear commit: "fix: update [Name] [field] information"
[ ] Incluir descripción de qué cambió
[ ] git status para verificar

POST-OPERACIÓN:
[ ] Informar al usuario sobre la actualización exitosa
[ ] Detallar qué campos fueron modificados
```

### ✅ ELIMINAR Startup - Checklist Completo

```
CONFIRMACIÓN:
[ ] Recibir nombre o ID de la startup a eliminar
[ ] Buscar startup en solutions.json
[ ] Confirmar con el usuario: "¿Seguro de eliminar [Name]?"
[ ] Recibir confirmación explícita

PREPARACIÓN:
[ ] Identificar ID de la startup
[ ] Identificar nombre del archivo de imagen (campo "images")
[ ] Identificar categoría (para estadísticas)
[ ] Leer objeto completo para documentar eliminación

ELIMINAR DEL JSON:
[ ] Usar Edit tool para eliminar objeto completo
[ ] Si está en medio del array: eliminar objeto y coma siguiente
[ ] Si está al final del array: eliminar coma anterior y objeto
[ ] Mantener sintaxis JSON válida
[ ] Preservar indentación y formato

ELIMINAR IMAGEN:
[ ] rm public/images/solutions/[filename]
[ ] Si no existe, documentar en commit
[ ] No fallar la operación si imagen no existe

VALIDAR:
[ ] Ejecutar validación de sintaxis JSON
[ ] Verificar que no hay errores
[ ] Contar que el total de startups disminuyó en 1
[ ] Grep para confirmar que la startup ya no está

GIT COMMIT:
[ ] git add data/solutions.json public/images/solutions/[name].png
[ ] Crear commit: "chore: remove [Name] from ecosystem"
[ ] Incluir razón de eliminación en commit message
[ ] Detallar categoría y archivos eliminados
[ ] git status para verificar

POST-OPERACIÓN:
[ ] Informar al usuario sobre la eliminación exitosa
[ ] Proporcionar resumen: nombre, categoría eliminada
[ ] Indicar nuevo total de startups en el ecosistema
```

---

## 🚨 Casos de Error y Soluciones

### Error: JSON Inválido Después de Editar

**Síntomas:**
```bash
SyntaxError: Unexpected token } in JSON at position 1234
```

**Causas Comunes:**
1. Coma faltante entre objetos
2. Coma extra al final del último objeto
3. Comillas sin cerrar
4. Salto de línea dentro de un string sin escapar

**Solución:**
```bash
# 1. Leer el archivo completo para identificar el error
Read(file_path: "data/solutions.json", offset: [línea-aproximada-20], limit: 40)

# 2. Corregir manualmente usando Edit
# 3. Validar nuevamente
```

### Error: Imagen No Se Encuentra

**Síntomas:**
```bash
rm: cannot remove 'public/images/solutions/file.png': No such file or directory
```

**Solución:**
```bash
# Verificar que el path sea correcto (relativo al working directory)
ls public/images/solutions/

# Si la imagen no existe, no es error crítico para eliminaciones
# Documentar en commit y continuar
```

### Error: UUID Duplicado

**Síntomas:**
El mismo UUID ya existe en solutions.json

**Solución:**
```bash
# Generar nuevo UUID
node -e "console.log(require('crypto').randomUUID())"

# Verificar que sea único
Grep(pattern: "\"id\": \"[nuevo-UUID]\"", path: "data/solutions.json")

# Usar el nuevo UUID único
```

### Error: Descripción Muy Larga

**Síntomas:**
Descripción excede 250 caracteres

**Solución:**
1. Acortar oraciones
2. Eliminar palabras redundantes
3. Priorizar métricas sobre adjetivos
4. Usar abreviaciones comunes (ej: "IA" en lugar de "Inteligencia Artificial")

**Ejemplo:**
```
❌ Muy largo (280 caracteres):
"SOVRA es una empresa tecnológica que proporciona The Digital Trust Stack
completo para instituciones gubernamentales y privadas. Son los creadores
de QuarkID y OS City. Su stack incluye SovraGov para digitalización de
servicios públicos, SovraID como motor para identidades digitales, y
SovraWallet como billetera no-custodial. Implementado en Buenos Aires."

✅ Correcto (245 caracteres):
"The Digital Trust Stack para instituciones. Creadores de QuarkID y OS City.
Stack completo con SovraGov, SovraID, y SovraWallet. Implementado en Buenos
Aires con 700,000+ ciudadanos usando QuarkID."
```

---

## 📊 Estadísticas y Balance de Categorías

### Estado Actual del Ecosistema

**Total de Startups:** 14

**Por Categoría:**
- 🏛️ Government: 3 (21%)
- 🆔 Identity: 2 (14%)
- 💰 Fintech: 2 (14%)
- 🏥 Health: 2 (14%)
- 🌆 City: 3 (21%)
- 🚗 Mobility: 2 (14%)

### Recomendaciones de Balance

**Ideal:**
- Ninguna categoría > 40% del total
- Ninguna categoría < 5% del total
- Distribución relativamente uniforme

**Si una categoría está sobre-representada:**
- Evaluar si algunas startups podrían re-categorizarse
- Priorizar agregar startups de categorías sub-representadas

---

## 🎯 Mejores Prácticas

### Para el Agente Especialista

1. **Siempre Investigar Antes de Escribir**
   - No inventar información
   - Extraer datos del sitio web oficial
   - Validar métricas cuando sea posible

2. **Descripciones de Alta Calidad**
   - Enfocarse en VALOR, no en marketing
   - Incluir métricas específicas
   - Ser conciso pero informativo

3. **Validar TODO**
   - JSON después de cada edición
   - URLs antes de insertar
   - Imágenes antes de agregar

4. **Documentar en Commits**
   - Mensajes descriptivos
   - Incluir contexto relevante
   - Seguir convención de commit messages

5. **Comunicar con el Usuario**
   - Confirmar operaciones críticas (eliminaciones)
   - Reportar éxito con detalles
   - Alertar sobre problemas encontrados

---

## 📚 Referencias Rápidas

### Comandos Útiles

```bash
# Generar UUID
node -e "console.log(require('crypto').randomUUID())"

# Generar Timestamp ISO 8601
node -e "console.log(new Date().toISOString())"

# Validar JSON
node -e "JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8')); console.log('✓ Valid')"

# Contar startups
node -e "console.log(JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8')).length)"

# Buscar por nombre
Grep(pattern: "\"name\": \"SOVRA\"", path: "data/solutions.json", output_mode: "content", -B: 1, -A: 20)

# Listar imágenes
ls public/images/solutions/

# Verificar tamaño de imagen
du -h public/images/solutions/companyname.png
```

### Archivos Clave

- **Base de datos:** `data/solutions.json`
- **Imágenes:** `public/images/solutions/`
- **Documentación:** `README.md`, `STRUCTURE.md`, `CONTRIBUTING.md`, `AGENTS.md`

### URLs de Recursos

- **Proyecto en Railway:** (inferido) `https://ecosistema-gdi.railway.app`
- **Repositorio Git:** (local) `c:\Users\santi\OneDrive\Desktop\GDILatam\ecosistemaGDI`

---

## ✨ Conclusión

Esta guía proporciona un proceso completo para que el **Agente Especialista** pueda:

✅ **Agregar** nuevas startups con investigación web automatizada
✅ **Actualizar** información de startups existentes
✅ **Eliminar** startups del ecosistema de forma segura
✅ **Validar** datos y mantener integridad del JSON
✅ **Documentar** cambios con commits descriptivos

**Filosofía:** Automatizar lo repetitivo, mantener calidad alta, documentar todo.

---

**Última actualización:** 2025-11-04
**Versión:** 1.0
**Mantenedor:** Agente Claude Especialista en Ecosistema GDI