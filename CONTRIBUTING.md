# Contributing to Ecosistema GDI Marketplace

> **Guía paso a paso para agregar nuevas soluciones al marketplace**

Gracias por tu interés en contribuir al Ecosistema GDI! Este documento te guiará a través del proceso completo para agregar tu solución al marketplace.

## 📋 Requisitos

Antes de comenzar, verifica que tu solución cumpla con estos criterios:

- ✅ **Relevancia**: Soluciona un problema real para ciudades, gobiernos o comunidades
- ✅ **Empresa activa**: Sitio web funcional con actualizaciones recientes
- ✅ **Presencia LATAM**: Foco en Latinoamérica o presencia regional
- ✅ **Propuesta de valor clara**: Beneficio tangible y medible
- ✅ **Categoría definida**: Encaja en una de nuestras 6 categorías

## 🎯 Categorías Disponibles

Selecciona la categoría que mejor describa tu solución:

| Categoría | Código | Descripción | Ejemplos |
|-----------|--------|-------------|----------|
| 🏛️ Gobierno | `government` | GovTech, transparencia, procurement | ETHIX, Kleros, B2Gov, Cero1, X-Road |
| 🆔 Identidad | `identity` | Identidad digital, credenciales | SOVRA, POK |
| 💰 Fintech | `fintech` | Pagos, inclusión financiera | Quién x Mí |
| 🏥 Salud | `health` | HealthTech, salud mental | EnLite, CUX |
| 🌆 Ciudad | `city` | Tech urbana, medio ambiente | Satellites on Fire, Hashi |
| 🚗 Movilidad | `mobility` | Transporte, movilidad urbana | Izi Park, Ualabee |

## 🚀 Métodos de Contribución

### Método 1: Formulario de Contacto (Recomendado)

**El más fácil para usuarios no técnicos**

1. Ve a [https://ecosistema-gdi.railway.app/contact.html](https://ecosistema-gdi.railway.app/contact.html)
2. Completa el formulario con la información de tu solución
3. El equipo de GDI revisará y agregará tu solución en 2-5 días hábiles

### Método 2: Pull Request (Para desarrolladores)

**Proceso técnico completo**

#### Paso 1: Preparar la Información

Reúne toda la información necesaria:

```json
{
  "name": "Nombre de tu Empresa",
  "category": "category-code",
  "description": {
    "es": "Descripción en español (2-3 oraciones, 150-250 caracteres)",
    "en": "English description (2-3 sentences, 150-250 characters)"
  },
  "website": "https://tuempresa.com",
  "email": "contacto@tuempresa.com",
  "phone": "+54 11 1234-5678",
  "linkedinFounders": [
    "https://www.linkedin.com/in/founder1",
    "https://www.linkedin.com/in/founder2"
  ]
}
```

**Formato recomendado para descripciones:**
```
[Qué hace la empresa]. [Tecnología/Método específico]. [Impacto/Métrica relevante].
```

**Ejemplo:**
```
"Plataforma de detección temprana de incendios forestales en tiempo real.
Combina satélites con información cada 10 minutos y cámaras con IA propia.
Desarrollada por expertos que trabajaron en proyectos con NASA y ESA."
```

#### Paso 2: Preparar el Logo

**Requisitos de imagen:**
- Formato: PNG (preferido) o JPG
- Tamaño máximo: 200KB
- Dimensiones recomendadas: 400x400px a 800x800px
- Fondo: Transparente (PNG) o blanco
- Calidad: Alta resolución, nítida

**Nombre del archivo:**
- Usa el nombre de tu empresa en minúsculas
- Sin espacios (usa guiones bajos o sin espacios)
- Ejemplo: `tuempresa.png` o `tu_empresa.png`

#### Paso 3: Fork y Clone del Repositorio

```bash
# 1. Haz fork del repositorio en GitHub
# https://github.com/GDILatam/ecosistema-gdi-marketplace

# 2. Clona tu fork
git clone https://github.com/TU-USUARIO/ecosistema-gdi-marketplace.git
cd ecosistema-gdi-marketplace

# 3. Crea una nueva rama
git checkout -b add-solution-tuempresa

# 4. Instala dependencias
npm install
```

#### Paso 4: Agregar el Logo

```bash
# Copia tu logo a la carpeta de soluciones
cp /ruta/a/tu/logo.png public/images/solutions/tuempresa.png

# Verifica que el archivo esté en el lugar correcto
ls -lh public/images/solutions/tuempresa.png
```

#### Paso 5: Editar solutions.json

**Abre el archivo:**
```bash
# Linux/Mac
nano data/solutions.json

# O usa tu editor favorito
code data/solutions.json
```

**Genera un UUID v4:**
```bash
# Opción 1: En línea de comandos (Linux/Mac)
uuidgen | tr '[:upper:]' '[:lower:]'

# Opción 2: Node.js
node -e "console.log(require('uuid').v4())"

# Opción 3: Online
# https://www.uuidgenerator.net/version4
```

**Agrega tu solución al final del array (antes del corchete de cierre):**

```json
  {
    "id": "tu-uuid-v4-generado-aqui",
    "name": "Tu Empresa",
    "category": "category-code",
    "description": {
      "es": "Tu descripción en español aquí",
      "en": "Your English description here"
    },
    "website": "https://tuempresa.com",
    "email": "contacto@tuempresa.com",
    "phone": "+54 11 1234-5678",
    "linkedinFounders": [
      "https://www.linkedin.com/in/founder1"
    ],
    "images": ["/images/solutions/tuempresa.png"],
    "createdAt": "2025-10-23T00:00:00.000Z",
    "updatedAt": "2025-10-23T00:00:00.000Z"
  }
]
```

**⚠️ Importante:**
- Asegúrate de agregar una coma `,` después de la solución anterior
- No agregues coma después de tu solución (es la última)
- Mantén la indentación consistente (2 espacios)
- Verifica que el JSON sea válido

**Validar JSON:**
```bash
# Opción 1: Node.js
node -e "JSON.parse(require('fs').readFileSync('data/solutions.json', 'utf8'))"

# Opción 2: Online
# Copia el contenido a https://jsonlint.com/
```

#### Paso 6: Probar Localmente

```bash
# 1. Inicia el servidor
npm start

# 2. Abre en el navegador
# http://localhost:3000

# 3. Verifica que:
# ✅ Tu solución aparece en la página principal
# ✅ El logo se muestra correctamente
# ✅ Los filtros por categoría funcionan
# ✅ El modal con detalles se abre correctamente
# ✅ Los enlaces funcionan (website, email, LinkedIn)
# ✅ Las descripciones en español e inglés son correctas
```

#### Paso 7: Commit y Push

```bash
# 1. Agrega los archivos modificados
git add data/solutions.json
git add public/images/solutions/tuempresa.png

# 2. Crea un commit descriptivo
git commit -m "feat: add [TuEmpresa] to [Categoría] category

- Add company logo (tuempresa.png)
- Add bilingual description
- Include website and contact info
- Add founders' LinkedIn profiles
"

# 3. Push a tu fork
git push origin add-solution-tuempresa
```

#### Paso 8: Crear Pull Request

1. Ve a tu fork en GitHub
2. Haz clic en "Compare & pull request"
3. Usa este template para el PR:

```markdown
## Nueva Solución: [Nombre de tu Empresa]

### Información
- **Categoría**: [Gobierno/Identidad/Fintech/Salud/Ciudad/Movilidad]
- **Website**: https://tuempresa.com
- **Descripción breve**: [1 línea]

### Checklist
- [ ] Logo agregado (PNG/JPG, <200KB)
- [ ] Descripción en español completa
- [ ] Descripción en inglés completa
- [ ] Website funcional verificado
- [ ] JSON validado (sin errores de sintaxis)
- [ ] Probado localmente (npm start)
- [ ] Categoría correcta asignada

### Screenshots
[Agrega una captura de pantalla mostrando tu solución en el marketplace]

### Notas adicionales
[Cualquier información relevante]
```

4. Submit el PR
5. Espera la revisión del equipo (1-3 días)

## ✅ Criterios de Aprobación

Tu PR será aprobado si cumple con:

### Técnicos
- ✅ JSON válido sin errores de sintaxis
- ✅ UUID v4 único y válido
- ✅ Imagen optimizada (<200KB)
- ✅ Descripción dentro del rango (150-250 caracteres)
- ✅ URLs válidas y funcionales
- ✅ Formato consistente con las demás soluciones

### Contenido
- ✅ Descripción clara y concisa
- ✅ Traducción correcta al inglés
- ✅ Categoría apropiada
- ✅ Información de contacto completa
- ✅ Logo de buena calidad

### Calidad
- ✅ Sin errores ortográficos
- ✅ Sin promotional/marketing language excesivo
- ✅ Información verificable
- ✅ Empresa activa y funcional

## ❌ Razones de Rechazo

Tu PR puede ser rechazado si:

- ❌ JSON inválido (rompe el sitio)
- ❌ Empresa inactiva o sitio caído
- ❌ No relevante para ciudades/gobiernos
- ❌ Información incorrecta o engañosa
- ❌ Logo de baja calidad o con copyright issues
- ❌ Descripción muy larga o muy corta
- ❌ Spam o promotional content
- ❌ Duplicado de solución existente

## 🔄 Proceso de Revisión

1. **Automático** (segundos): GitHub Actions verifica sintaxis
2. **Manual** (1-3 días): Equipo revisa contenido y calidad
3. **Feedback** (si necesario): Solicitud de cambios
4. **Aprobación** (final): Merge a main branch
5. **Deploy** (automático): Railway deploys en 2-5 minutos

## 🆘 ¿Necesitas Ayuda?

### Preguntas Frecuentes

**P: ¿Puedo agregar múltiples imágenes?**
R: Sí, el array `images` acepta múltiples rutas. Agrega todas las que necesites.

**P: ¿Qué hago si no tengo founders en LinkedIn?**
R: Deja el array vacío: `"linkedinFounders": []`

**P: ¿Puedo actualizar mi solución después?**
R: Sí, envía un nuevo PR con los cambios.

**P: ¿Cuánto tarda en aparecer en el sitio?**
R: Una vez aprobado el PR, 2-5 minutos (deploy automático de Railway).

### Soporte

- **GitHub Issues**: [Crear issue](https://github.com/GDILatam/ecosistema-gdi-marketplace/issues)
- **Email**: Via formulario en `/contact.html`
- **Website**: [GDILatam.com](https://GDILatam.com)

## 📚 Recursos Adicionales

- [README.md](./README.md) - Overview del proyecto
- [AGENTS.md](./AGENTS.md) - Guía para mantenimiento
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment en Railway
- [STRUCTURE.md](./STRUCTURE.md) - Arquitectura técnica

## 🙏 Gracias por Contribuir

Cada solución agregada fortalece el ecosistema y ayuda a más ciudades a descubrir herramientas que mejoran la vida de sus comunidades.

---

**Última actualización**: Noviembre 2025
**Mantenido por**: GDI Latam Development Team

**Total Soluciones Activas**: 14

*"El secreto del cambio es enfocar toda tu energía, no en luchar contra lo viejo, sino en construir lo nuevo." - Sócrates*
