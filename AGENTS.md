# AGENTS.md - Internal Maintenance Protocol

> 🤖 Guía interna para el equipo de agentes de mantenimiento del Ecosistema GDI Marketplace

**Confidencial** - Solo para uso interno del equipo de desarrollo

---

## 📋 Propósito

Este documento define la metodología, roles y procedimientos para el mantenimiento continuo del marketplace de soluciones del Ecosistema GDI. Sirve como referencia para agentes humanos y futuros sistemas automatizados.

## 🎯 Objetivos del Equipo

1. **Calidad de Datos**: Mantener información precisa y actualizada de todas las soluciones
2. **Disponibilidad**: Garantizar que todos los links funcionen
3. **Consistencia**: Estandarizar descripciones, categorías e imágenes
4. **Crecimiento**: Identificar y agregar nuevas soluciones relevantes
5. **Performance**: Optimizar carga de imágenes y datos

## 👥 Roles y Especialistas

### 1. Content Curator (Curador de Contenido)
**Responsabilidad**: Validación y mejora de descripciones

**Skills requeridas:**
- Redacción técnica (ES/EN)
- Conocimiento del ecosistema de startups LATAM
- SEO básico

**Tareas:**
- Revisar descripciones para claridad y precisión
- Estandarizar formato de descripciones (2-3 oraciones)
- Validar traducciones ES ↔ EN
- Optimizar para búsqueda (keywords relevantes)

**Tools:**
- Claude Code (para búsqueda web)
- Google Translate (verificación)
- DeepL (traducciones de calidad)

### 2. Link Validator (Validador de Enlaces)
**Responsabilidad**: Verificar disponibilidad de URLs

**Skills requeridas:**
- Scripting básico (Node.js/Python)
- HTTP status codes
- Web scraping ético

**Tareas:**
- Verificar que website URLs respondan 200
- Validar LinkedIn profiles activos
- Detectar redirects o dominios expirados
- Actualizar URLs cambiadas

**Tools:**
- `fetch` / `axios` / `curl`
- Scripts automatizados
- Uptime monitoring services

### 3. Image Specialist (Especialista en Imágenes)
**Responsabilidad**: Calidad visual del marketplace

**Skills requeridas:**
- Diseño gráfico básico
- Optimización de imágenes
- Web scraping de imágenes

**Tareas:**
- Buscar logos oficiales de alta calidad
- Optimizar tamaño (idealmente < 200KB)
- Convertir a formatos modernos (WebP)
- Mantener aspect ratios consistentes
- Reemplazar placeholders

**Tools:**
- Puppeteer/Playwright (screenshots)
- ImageMagick / Sharp (optimización)
- Google Images API
- Company websites

### 4. Category Manager (Gestor de Categorías)
**Responsabilidad**: Taxonomía del marketplace

**Skills requeridas:**
- Análisis de productos tech
- Conocimiento del sector público
- Clasificación de datos

**Tareas:**
- Asignar categorías correctas a cada solución
- Identificar necesidades de nuevas categorías
- Evitar overlapping entre categorías
- Mantener balance entre categorías

**Categories actuales (6):**
- `government` - 🏛️ Gobierno: GovTech, transparencia, procurement público
- `identity` - 🆔 Identidad: Identidad digital, credenciales, verificación
- `fintech` - 💰 Fintech: Pagos, inclusión financiera, dinero digital
- `health` - 🏥 Salud: HealthTech, salud mental, bienestar
- `city` - 🌆 Ciudad: Tech urbana, medio ambiente, espacios públicos
- `mobility` - 🚗 Movilidad: Movilidad urbana, transporte, estacionamiento

### 5. Research Agent (Agente de Investigación)
**Responsabilidad**: Descubrir nuevas soluciones

**Skills requeridas:**
- Investigación en línea
- Networking con startups
- Análisis de tendencias

**Tareas:**
- Monitorear acceleradoras LATAM
- Seguir premios e incubadoras
- Revisar listados de GovTech
- Contactar fundadores para inclusión

**Sources:**
- IDB Lab
- 500 Startups LATAM
- Y Combinator (LATAM)
- Endeavor
- NXTP Labs
- LinkedIn
- Product Hunt

## 🔄 Metodología de Mantenimiento

### Ciclo de Actualización (Manual)

#### Frecuencia Recomendada
- **Diaria**: Verificación de links críticos
- **Semanal**: Revisión de nuevas soluciones potenciales
- **Mensual**: Auditoría completa de datos
- **Trimestral**: Revisión de categorías y estructura

#### Proceso Paso a Paso

##### 1. Pre-Review (10 min)
```bash
# Pull latest changes
git pull origin main

# Check solutions count
node -e "console.log(require('./data/solutions.json').length)"

# Verify server starts
npm start
```

##### 2. Link Validation (30 min)
```javascript
// Pseudocódigo para validación
for each solution in solutions.json:
  - Fetch solution.website
  - Check HTTP status (expect 200, 301, 302)
  - If 404/500/timeout:
    - Flag for manual review
    - Try to find new URL via Google
    - Contact founder if possible
  - Validate LinkedIn URLs (expect profile found)
```

##### 3. Content Review (60 min)
- Leer descripción en español
- Verificar que tenga 2-3 oraciones
- Confirmar que sea clara y específica
- Validar traducción al inglés
- Corregir errores gramaticales
- Estandarizar formato

**Template ideal de descripción:**
```
[Qué hace la empresa]. [Tecnología/Método específico que usa]. [Impacto/Métrica relevante].
```

**Ejemplo:**
```
"Plataforma de detección temprana de incendios forestales en tiempo real.
Combina satélites con información cada 10 minutos y cámaras con IA propia.
Desarrollada por expertos que trabajaron en proyectos con NASA y ESA."
```

##### 4. Image Optimization (45 min)
```bash
# For each solution missing images:
1. Visit website
2. Find logo (usually in header/footer or /about)
3. Preferred sources:
   - og:image meta tag
   - Press kit / Media page
   - LinkedIn company page
4. Download highest quality version
5. Optimize:
   - Resize to max 800x800px
   - Compress to <200KB
   - Convert to WebP if possible
6. Upload via admin panel
```

##### 5. Category Audit (20 min)
- Review each solution's category assignment
- Check if category accurately describes main value prop
- Look for miscategorized solutions
- Identify gaps (solutions we're missing)

##### 6. New Solutions Research (variable)
**Weekly quota sugerido**: 2-5 nuevas soluciones

**Process:**
1. Identify candidate (from sources above)
2. Visit website
3. Validate it fits criteria:
   - ✅ Relevant to government/cities
   - ✅ Active company (recent updates)
   - ✅ LATAM presence or focus
   - ✅ Clear value proposition
4. Gather data:
   - Name
   - Category
   - Description (ES/EN)
   - Website
   - Contact info
   - Founders' LinkedIn
   - Logo/images
5. Add via admin panel
6. Test that it displays correctly

##### 7. Post-Update (15 min)
```bash
# Test locally
npm start
# Visit http://localhost:3000
# Test filters, search, modal

# Commit changes
git add data/solutions.json
git add public/images/solutions/*
git commit -m "chore: update solutions database - [brief description]"
git push origin main

# Verify deployment on Railway
```

## 🛠️ Technical Reference

### Project Tech Stack

**Backend:**
- Node.js v18+
- Express.js 4.18
- Multer (file uploads)
- UUID (ID generation)

**Frontend:**
- Vanilla JavaScript (ES6+)
- CSS3 (variables, grid, flexbox)
- No frameworks (performance)

**Database:**
- JSON file (`data/solutions.json`)
- Simple, versionable, portable

**Deployment:**
- Railway.app
- GitHub Actions ready
- Auto-deploy on push to main

### Key Files

| File | Purpose | Edit Frequency |
|------|---------|---------------|
| `data/solutions.json` | Master database | High |
| `public/js/i18n.js` | Translations | Low |
| `public/css/main.css` | Styles | Low |
| `server.js` | Backend logic | Very Low |

### Data Schema

```typescript
interface Solution {
  id: string;              // UUID v4
  name: string;            // Company name
  category: Category;      // One of: government, identity, fintech, health, city, mobility
  description: {
    es: string;           // Spanish description (2-3 sentences)
    en: string;           // English description (2-3 sentences)
  };
  website: string;        // Official website (required)
  email: string | null;   // Contact email (optional)
  phone: string | null;   // Contact phone (optional)
  linkedinFounders: string[]; // Array of LinkedIn profile URLs
  images: string[];       // Array of image paths (relative to /images/solutions/)
  createdAt: string;      // ISO 8601 timestamp
  updatedAt: string;      // ISO 8601 timestamp
}
```

### Admin Panel

**Access:** `/admin.html`
**Auth:** Bearer token (set in `.env`)

**Features:**
- Create solution
- Edit solution
- Delete solution
- Upload images
- Live preview

## 📊 Quality Metrics

### KPIs to Track

1. **Data Completeness**
   - Target: 100% solutions have descriptions
   - Target: 90%+ solutions have images
   - Target: 100% have working website links

2. **Link Health**
   - Target: 95%+ websites return 200 status
   - Target: 0 broken images

3. **Content Quality**
   - Average description length: 150-250 characters
   - Translation accuracy: Manual spot checks
   - Category distribution: No category >40% or <5%

4. **Growth**
   - Target: +10 solutions/month
   - Coverage of top GovTech companies in LATAM

### Monthly Audit Checklist

```markdown
## [Month Year] Audit Report

### Stats
- Total solutions: X
- New this month: +Y
- Updated this month: Z
- Removed: W

### Link Health
- Working links: X/Y (Z%)
- Broken links found: [list]
- Fixed links: [list]

### Content Quality
- Solutions with images: X/Y (Z%)
- Avg description length: X chars
- Categories balance: [distribution]

### Actions Taken
- [ ] Validated all links
- [ ] Updated N descriptions
- [ ] Added M new solutions
- [ ] Optimized P images
- [ ] Fixed Q bugs

### Next Month Goals
- [ ] Goal 1
- [ ] Goal 2
- [ ] Goal 3
```

## 🚨 Escalation Procedures

### When to Escalate to Lead Developer

1. **Critical Issues**
   - Server down / deployment failed
   - Data corruption in solutions.json
   - Security vulnerability discovered
   - Admin panel not accessible

2. **Feature Requests**
   - Need new category
   - Need new field in schema
   - Performance issues (slow load times)
   - UI/UX improvements needed

3. **External Communications**
   - Company requests removal
   - Legal/copyright issues
   - Partnership opportunities

## 🔐 Security & Best Practices

### Do's ✅
- Always pull latest changes before editing
- Test locally before pushing
- Use admin panel for adding solutions
- Keep backups of solutions.json
- Verify image sources are legitimate
- Respect robots.txt when scraping
- Give attribution when required

### Don'ts ❌
- Don't commit `.env` file
- Don't push node_modules
- Don't edit solutions.json directly (use admin panel)
- Don't add unverified/spam solutions
- Don't use copyrighted images without permission
- Don't expose admin credentials
- Don't make breaking changes to data schema

## 📚 Resources & Tools

### Essential Reading
- [README.md](./README.md) - Project overview
- [STRUCTURE.md](./STRUCTURE.md) - Architecture details
- Railway docs: https://docs.railway.app

### Useful Tools
- **JSONLint**: Validate JSON syntax
- **Broken Link Checker**: https://www.brokenlinkcheck.com
- **Image Compressor**: TinyPNG, Squoosh
- **LinkedIn Helper**: LinkedIn Sales Navigator (for finding founders)

### Communication
- **Internal**: GitHub Issues for bugs/features
- **External**: Contact form at /contact.html

## 🎓 Training & Onboarding

### New Agent Checklist

- [ ] Read README.md completely
- [ ] Read AGENTS.md (this file)
- [ ] Set up local development environment
- [ ] Get admin panel credentials
- [ ] Do a practice update (supervised)
- [ ] Complete first solo weekly review
- [ ] Attend monthly team sync

### Estimated Time to Competency
- **Week 1**: Familiarization with codebase
- **Week 2-3**: Supervised maintenance tasks
- **Week 4+**: Independent operation

## 📅 Maintenance Schedule Template

### Weekly Tasks (Every Monday)
```
[ ] Pull latest code
[ ] Run link validator
[ ] Review 5-10 solution descriptions
[ ] Research 2-3 new potential solutions
[ ] Check Railway deployment status
```

### Monthly Tasks (First Monday of month)
```
[ ] Full link audit (all solutions)
[ ] Category balance review
[ ] Image quality check
[ ] Generate monthly report
[ ] Plan next month's additions
```

### Quarterly Tasks (Q1, Q2, Q3, Q4)
```
[ ] Complete data audit
[ ] Review and update this AGENTS.md
[ ] Evaluate new categories
[ ] Archive inactive solutions
[ ] Performance optimization review
```

## 📝 Change Log Template

Keep a log of significant changes in this file:

```markdown
## 2025-10-22 - Initial Setup
- Created AGENTS.md
- Defined roles and methodology
- Established maintenance schedule
```

---

**Document Version**: 1.0.0
**Last Updated**: 2025-10-22
**Next Review**: 2026-01-22

**Maintained by**: GDI Latam Development Team
**Contact**: Via GitHub Issues or internal Slack

---

*"El secreto del cambio es enfocar toda tu energía, no en luchar contra lo viejo, sino en construir lo nuevo."*
