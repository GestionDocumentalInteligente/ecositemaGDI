# Project Structure Documentation

> Detailed technical architecture of Ecosistema GDI Marketplace

## Directory Tree

```
marketplace/
├── .env.example              # Environment variables template
├── .gitignore               # Git ignore rules
├── package.json             # NPM dependencies and scripts
├── server.js                # Express.js server (backend)
├── start.bat                # Quick start script for Windows
├── README.md                # Project documentation
├── LICENSE                  # Proprietary license
├── AGENTS.md                # Internal maintenance guide
├── AGENTE_ESPECIALISTA_STARTUPS.md  # Detailed startup management guide
├── CONTRIBUTING.md          # Guide to add new solutions
├── DEPLOYMENT.md            # Railway deployment guide
├── DEPLOY.md                # Local deployment guide
├── STRUCTURE.md            # This file
├── railway.json             # Railway deployment config
│
├── data/                    # JSON database
│   └── solutions.json       # Solutions data (14 solutions)
│
└── public/                  # Static frontend assets
    ├── index.html           # Homepage
    ├── solutions.html       # Solutions catalog page
    ├── protocol.html        # Protocol explanation page
    ├── fundamentals.html    # Fundamentals guide page
    ├── contact.html         # Contact form page
    ├── admin.html           # Admin panel (CRUD interface)
    │
    ├── css/
    │   └── main.css         # Main stylesheet (navy theme, WCAG AAA)
    │
    ├── js/
    │   ├── i18n.js          # Internationalization system (ES/EN)
    │   ├── app.js           # Main app logic (homepage, modal)
    │   └── utils.js         # Utility functions (API calls, DOM helpers)
    │
    └── images/
        ├── site/            # Site images (logos, backgrounds, icons, static assets)
        │   ├── Logo.png
        │   ├── ecosistema.png
        │   ├── favicon.png
        │   ├── home.png
        │   ├── cero1.png
        │   ├── ciudad.png
        │   ├── protocol-bg.png
        │   ├── contact-bg.png
        │   ├── solutions-bg.png
        │   ├── fundamentals.png
        │   └── fundamentals1.png
        │
        └── solutions/       # Solution logos (versioned)
            ├── .gitkeep
            └── [solution-name].png  # Solution logos
```

## Backend Architecture

### server.js

**Technology**: Express.js (Node.js)

**Responsibilities**:
- Serve static files from `/public`
- RESTful API for solutions CRUD
- Image upload handling (Multer)
- Admin authentication (Bearer token)

**Key Endpoints**:

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/solutions` | Get all solutions | No |
| POST | `/api/solutions` | Create solution | Yes |
| PUT | `/api/solutions/:id` | Update solution | Yes |
| DELETE | `/api/solutions/:id` | Delete solution | Yes |
| POST | `/api/upload` | Upload images | Yes |

**Authentication**:
- Bearer token in `Authorization` header
- Token = `ADMIN_PASSWORD` from environment

**File Upload**:
- Max file size: 5MB per image
- Allowed types: JPEG, PNG, GIF, WebP
- Storage: `public/images/solutions/`
- Naming: UUID v4 + extension

### data/solutions.json

**Format**: JSON array

**Schema**:
```typescript
interface Solution {
  id: string;              // UUID v4
  name: string;            // Company name
  category: string;        // One of: mobility, public-space, fintech, legal-ai, data
  description: {
    es: string;           // Spanish (2-3 sentences)
    en: string;           // English (2-3 sentences)
  };
  website: string;        // Official website URL
  email: string | null;   // Contact email
  phone: string | null;   // Contact phone
  linkedinFounders: string[]; // LinkedIn profile URLs
  images: string[];       // Image paths (relative to /images/solutions/)
  createdAt: string;      // ISO 8601 timestamp
  updatedAt: string;      // ISO 8601 timestamp
}
```

**Example**:
```json
{
  "id": "a1b2c3d4-e5f6-4a5b-8c9d-0e1f2a3b4c5d",
  "name": "ETHIX",
  "category": "legal-ai",
  "description": {
    "es": "Plataforma de IA que actúa como copiloto en procesos de contratación pública...",
    "en": "AI-powered platform that serves as a copilot for public procurement processes..."
  },
  "website": "https://www.goethix.com/",
  "email": null,
  "phone": null,
  "linkedinFounders": [],
  "images": ["/images/solutions/placeholder-1.png"],
  "createdAt": "2025-10-21T21:30:00.000Z",
  "updatedAt": "2025-10-21T21:30:00.000Z"
}
```

## Frontend Architecture

### HTML Pages

All pages share:
- Same header (logo + nav + language toggle)
- Same footer (logo + links + contact)
- Bilingual support via `data-i18n` attributes
- Responsive design (mobile-first)

#### index.html
**Purpose**: Homepage / Landing page

**Sections**:
1. Hero - Main value proposition
2. Filters - Category buttons
3. Solutions Grid - Featured solutions (6-12 items)
4. Cities Section - Protocol explanation + diagram
5. Footer

**JavaScript**: `app.js`, `i18n.js`, `utils.js`

#### solutions.html
**Purpose**: Full solutions catalog

**Features**:
- All solutions displayed
- Category filtering
- Modal for solution details
- Responsive grid layout

#### protocol.html
**Purpose**: Explain the GDI protocol/ecosystem

**Content**:
- Protocol hero section
- 4 principles cards
- CTA to fundamentals

#### fundamentals.html
**Purpose**: Guide on "hacking the system"

**Content**:
- Hero with background image
- Problem statement
- Solution (infrastructure paralela)
- 4-step process
- Principles
- CTAs

#### contact.html
**Purpose**: Contact form (Google Forms embed)

**Features**:
- Google Form iframe
- Link to GDILatam.com

#### admin.html
**Purpose**: Admin panel for CRUD operations

**Features**:
- Password authentication
- Solution form (create/edit)
- Solution list with edit/delete
- Image upload (multiple files)
- Live preview

### CSS Architecture

#### main.css

**Organization**:
1. CSS Variables (color palette, spacing, typography)
2. Reset & Base styles
3. Layout (Container, Grid, Flexbox)
4. Components (Buttons, Cards, Modal, Forms)
5. Pages (specific page styles)
6. Utilities
7. Responsive breakpoints

**Design System**:

**Colors** (Navy Blue Theme):
```css
/* Backgrounds */
--bg-darkest: #020817
--bg-dark: #0a1628       /* Main */
--bg-medium: #0f1f3d     /* Cards */
--bg-light: #162a4d

/* Accents */
--accent-bright: #2563eb  /* Primary */
--accent-primary: #1e40af /* Secondary */
--accent-medium: #3b82f6
--accent-soft: #60a5fa

/* Text */
--text-white: #ffffff
--text-primary: #f0f4fc
--text-secondary: #c5d4f0
```

**Typography**:
- Font: Inter (system fallback)
- Base size: 16px
- Scale: 1.25 (Major Third)

**Spacing System**:
- Base unit: 0.25rem (4px)
- Scale: 0.5, 1, 1.5, 2, 3, 4, 6, 8, 12, 16 rem

**Responsive Breakpoints**:
```css
/* Mobile first */
@media (min-width: 768px)  /* Tablet */
@media (min-width: 1024px) /* Desktop */
@media (min-width: 1280px) /* Large desktop */
```

### JavaScript Architecture

#### i18n.js
**Purpose**: Internationalization system

**Features**:
- Two languages: Spanish (es), English (en)
- localStorage persistence
- Translation object with nested keys
- `updateElements()` - Updates all `[data-i18n]` elements
- `get(key)` - Get translation by dot notation key
- `setLanguage(lang)` - Change language
- Category name/emoji mapping

**Usage**:
```html
<h1 data-i18n="hero.title">Fallback Text</h1>
```

```javascript
i18n.get('hero.title') // Returns "El sistema no se reforma..."
i18n.setLanguage('en') // Switch to English
```

#### app.js
**Purpose**: Main application logic

**Responsibilities**:
- Fetch solutions from API
- Render solution cards
- Category filtering
- Modal open/close
- Solution detail display
- Language change handling

**Key Functions**:
- `fetchSolutions()` - GET /api/solutions
- `renderSolutions(solutions, category)` - Create cards
- `createSolutionCard(solution)` - Card HTML generator
- `openModal(solution)` - Show modal with details
- `closeModal()` - Close modal
- `filterByCategory(category)` - Filter display

#### utils.js
**Purpose**: Utility functions and helpers

**Key Functions**:
- `api.get()`, `api.post()`, `api.put()`, `api.delete()` - API wrappers
- `showLoading()`, `hideLoading()` - Loading state
- `showError(message)` - Error notifications
- DOM helpers

## Data Flow

### User Views Solution

```
1. User visits index.html
2. app.js calls fetchSolutions()
3. GET /api/solutions
4. server.js reads data/solutions.json
5. Returns JSON array
6. app.js renders cards
7. i18n.js applies translations
8. User clicks card
9. openModal() displays details
```

### Admin Creates Solution

```
1. Admin visits admin.html
2. Enters password (Bearer token auth)
3. Fills form + uploads images
4. JavaScript POST /api/solutions + POST /api/upload
5. server.js validates auth
6. Multer saves images to public/images/solutions/
7. server.js updates data/solutions.json
8. Returns success
9. Admin panel refreshes list
```

## Security Model

### Authentication
- **Method**: Bearer token
- **Header**: `Authorization: Bearer <ADMIN_PASSWORD>`
- **Scope**: All POST/PUT/DELETE endpoints

### Authorization
- No user accounts
- Single admin password
- All users can read (GET)
- Only admin can write (POST/PUT/DELETE)

### File Upload Security
- File type validation (MIME + extension)
- File size limit (5MB per file)
- UUID filenames (prevent overwrites)
- Stored outside web root (served by Express)

### Input Validation
- Server-side validation on all inputs
- JSON schema validation
- XSS prevention (no HTML in user input)
- SQL injection N/A (no SQL database)

## Performance Optimizations

### Backend
- Static file serving with Express
- JSON file caching (in-memory)
- Minimal middleware
- No database queries (JSON read)

### Frontend
- No frameworks (lightweight)
- CSS variables (fast theme changes)
- Image lazy loading ready
- Minimal JavaScript
- Event delegation

### Assets
- CSS in single file (fewer requests)
- JavaScript split by page (app.js, i18n.js, utils.js)
- Images optimized (<200KB recommended)
- WebP format support

## Deployment

### Railway Platform

**Configuration**:
- Builder: Nixpacks (auto-detected Node.js)
- Build: `npm install`
- Start: `npm start`
- Port: Auto-assigned (Railway provides `$PORT`)

**Environment Variables**:
```
PORT=<auto>
ADMIN_PASSWORD=<secure-password>
NODE_ENV=production
```

**Auto-Deploy**:
- Connected to GitHub repo
- Deploys on push to `main` branch
- Build logs accessible in Railway dashboard

### File Persistence

**Issue**: Railway uses ephemeral filesystem

**Solutions**:
1. **Current**: JSON file committed to repo
   - Changes via admin panel lost on restart
   - Manual commit required
2. **Future**: Persistent volume or external DB
   - Railway Volumes
   - PostgreSQL/MongoDB
   - S3 for images

## Development Workflow

### Local Development

```bash
# Setup
git clone https://github.com/GestionDocumentalInteligente/marketplace.git
cd marketplace
npm install
cp .env.example .env
# Edit .env

# Run
npm start

# Test
# Visit http://localhost:3000
```

### Making Changes

```bash
# Feature branch
git checkout -b feature/my-feature

# Make changes
# Test locally

# Commit
git add .
git commit -m "feat: description"
git push origin feature/my-feature

# Create PR
# Merge to main → auto-deploy
```

### Updating Solutions

**Method 1**: Admin Panel
- Easiest for non-technical users
- Changes not persisted on Railway (ephemeral)
- Need to commit `data/solutions.json` manually

**Method 2**: Direct JSON Edit
- For developers
- Edit `data/solutions.json` locally
- Commit and push to GitHub
- Railway auto-deploys

## Testing Strategy

### Manual Testing
- See checklist in README.md
- Test all CRUD operations
- Test on multiple devices/browsers
- Verify responsive design
- Check accessibility (WCAG)

### Automated Testing
- Currently: None
- Future: Jest unit tests, Playwright E2E

## Monitoring & Maintenance

### Logs
- Railway provides application logs
- Check for errors on deploy
- Monitor API response times

### Backups
- `data/solutions.json` backed up to Git
- Images should be backed up externally
- Railway snapshots (if available)

### Updates
- Update dependencies monthly (`npm outdated`)
- Security patches promptly
- Node.js LTS version

## Scalability Considerations

**Current Limits**:
- ~100 solutions (JSON file size)
- ~10 concurrent users (single Node process)
- Images served by Express (not optimized)

**Scaling Path**:
1. **Phase 1** (current): JSON + Express
2. **Phase 2**: PostgreSQL + Redis cache
3. **Phase 3**: CDN for images + Load balancer
4. **Phase 4**: Microservices architecture

## Accessibility (a11y)

- WCAG AAA color contrast
- Semantic HTML5
- ARIA labels where needed
- Keyboard navigation
- Screen reader friendly
- Focus indicators
- Responsive text sizing

## Browser Support

**Target**:
- Chrome/Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Mobile browsers (iOS Safari, Chrome Android)

**Features Used**:
- ES6+ JavaScript
- CSS Grid & Flexbox
- CSS Variables
- Fetch API

**Not supported**: IE11

---

**Document Version**: 1.1.0
**Last Updated**: 2025-11-06
**Maintained by**: GDI Latam Development Team
