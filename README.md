# Ecosistema GDI - Marketplace

> **El sistema no se reforma. Se HACKEA.**

Ecosistema GDI es un marketplace y protocolo de soluciones tecnológicas para ciudades inteligentes. Plataforma que conecta innovadores con gobiernos locales a través de un ecosistema API-first descentralizado.

## 🚀 Features

- **Marketplace Bilingüe (ES/EN)**: Catálogo completo de soluciones con soporte i18n
- **6 Categorías Especializadas**: Gobierno, Identidad, Fintech, Salud, Ciudad, Movilidad
- **Filtrado Dinámico**: Sistema de filtros por categoría con UI intuitiva
- **Galería de Soluciones**: Modal detallado con información completa de cada solución
- **Responsive Design**: Optimizado para mobile, tablet y desktop
- **Dark Theme**: Paleta de azul marino (#0f1c35) con WCAG AAA compliance
- **SEO Optimizado**: Meta tags, structured data, sitemap ready
- **Sistema de Imágenes**: Logos optimizados para cada solución

## 🛠️ Tech Stack

### Backend
- **Node.js** v18+
- **Express.js** - Web framework minimalista
- **Multer** - File upload handling (admin features)
- **UUID** - Unique identifiers

### Frontend
- **Vanilla JavaScript** - No frameworks, máxima performance
- **CSS3** - Variables, Grid, Flexbox
- **HTML5** - Semantic markup
- **i18n System** - Internacionalización completa ES/EN

### Database
- **JSON File System** - Simple, portable, versionable (`data/solutions.json`)

### Deployment
- **Railway** - Continuous deployment from GitHub
- **GitHub Actions** ready

## 📦 Installation

### Prerequisites
- Node.js 18+
- npm or yarn
- Git

### Local Setup

1. **Clone the repository**
```bash
git clone https://github.com/GDILatam/ecosistema-gdi-marketplace.git
cd ecosistema-gdi-marketplace
npm install
```

2. **Configure environment variables**
```bash
cp .env.example .env
# Edit .env with your configuration
```

3. **Start development server**
```bash
npm start
```

4. **Open browser**
```
http://localhost:3000
```

## 🔐 Environment Variables

Create a `.env` file based on `.env.example`:

| Variable | Description | Default |
|----------|-------------|---------|
| `PORT` | Server port | `3000` |
| `NODE_ENV` | Environment mode | `production` |

## 📁 Project Structure

```
ecosistema-gdi-marketplace/
├── data/
│   └── solutions.json         # Database of all solutions (16 solutions)
├── public/
│   ├── css/
│   │   └── main.css          # Main stylesheet with navy blue theme
│   ├── js/
│   │   ├── i18n.js           # Internationalization (ES/EN)
│   │   ├── app.js            # Main application logic
│   │   └── utils.js          # Utility functions
│   ├── images/
│   │   ├── site/             # Site images (logo, ciudad.png, etc.)
│   │   └── solutions/        # Solution logos (versioned)
│   ├── index.html            # Homepage
│   ├── solutions.html        # Full catalog page
│   ├── protocol.html         # Protocol/Ecosystem page
│   ├── fundamentals.html     # Fundamentals guide
│   └── contact.html          # Contact form
├── server.js                 # Express server
├── package.json              # Dependencies
├── .env.example              # Environment template
├── .gitignore                # Git ignore rules
├── README.md                 # This file
├── CONTRIBUTING.md           # Guide to add new solutions
├── DEPLOYMENT.md             # Railway deployment guide
├── AGENTS.md                 # Maintenance team guide
├── STRUCTURE.md              # Detailed architecture
└── LICENSE                   # Copyright license
```

See [STRUCTURE.md](./STRUCTURE.md) for detailed architecture.

## 📊 Current Solution Categories

The marketplace organizes solutions into 6 categories:

| Category | Icon | Description | Count |
|----------|------|-------------|-------|
| **Gobierno** | 🏛️ | GovTech, transparency, public procurement | 4 |
| **Identidad** | 🆔 | Digital identity, credentials, verification | 2 |
| **Fintech** | 💰 | Payments, financial inclusion, digital money | 2 |
| **Salud** | 🏥 | HealthTech, mental health, wellness | 3 |
| **Ciudad** | 🌆 | Urban tech, environment, public spaces | 3 |
| **Movilidad** | 🚗 | Urban mobility, transport, parking | 2 |

**Total Solutions**: 16

## 🤝 Contributing

Want to add your solution to the marketplace?

1. Read [CONTRIBUTING.md](./CONTRIBUTING.md) for step-by-step instructions
2. Fill out the contact form at [/contact.html](https://ecosistema-gdi.railway.app/contact.html)
3. Submit a PR with your solution data

For internal maintenance team, see [AGENTS.md](./AGENTS.md) for detailed protocols.

## 🚀 Deployment

### Railway Deployment

This project is configured for Railway deployment. See [DEPLOYMENT.md](./DEPLOYMENT.md) for complete instructions.

**Quick Deploy:**
1. Connect your GitHub repository to Railway
2. Configure environment variables (PORT, NODE_ENV)
3. Railway auto-deploys on push to `main` branch

## 🎨 Design System

- **Primary Color**: Navy Blue (#0f1c35)
- **Accent Color**: Blue (#2563eb)
- **Background**: Dark gradient (#0a1526 to #0f1c35)
- **Text**: White/Light gray for contrast
- **Typography**: System fonts for performance
- **Icons**: Emoji-based for universal support

## 📞 Support

- **Website**: [GDILatam.com](https://GDILatam.com)
- **Email**: Via contact form at `/contact.html`
- **Issues**: GitHub Issues for bugs and feature requests

## 📄 License

Copyright © 2025 GDI Latam. All rights reserved. See [LICENSE](./LICENSE) for details.

This is a proprietary project. Unauthorized copying, distribution, or modification is prohibited.

## 🙏 Acknowledgments

Built with modern web standards and best practices. Special thanks to all solution providers featured in the marketplace.

---

**Built with** ❤️ **by GDI Latam**

*"El secreto del cambio es enfocar toda tu energía, no en luchar contra lo viejo, sino en construir lo nuevo." - Sócrates*

---

## 📚 Additional Documentation

- [CONTRIBUTING.md](./CONTRIBUTING.md) - How to add new solutions
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Railway deployment guide
- [AGENTS.md](./AGENTS.md) - Maintenance protocols
- [STRUCTURE.md](./STRUCTURE.md) - Technical architecture
- [LICENSE](./LICENSE) - Copyright and licensing
