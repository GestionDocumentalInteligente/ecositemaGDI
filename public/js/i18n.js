// i18n.js - Internationalization
const i18n = {
  currentLang: 'es',

  translations: {
    es: {
      hero: {
        title: 'Marketplace de Soluciones para Ciudades',
        subtitle: 'Descubrí las mejores soluciones tecnológicas para transformar tu ciudad'
      },
      filters: {
        all: 'Todas'
      },
      categories: {
        mobility: 'Movilidad',
        publicSpace: 'Espacio Público',
        fintech: 'Fintech',
        legalAI: 'LegalIA',
        data: 'Datos'
      },
      loading: 'Cargando soluciones...',
      emptyState: 'No se encontraron soluciones',
      modal: {
        website: 'Visitar sitio web',
        email: 'Enviar email',
        phone: 'Llamar',
        founders: 'Founders'
      },
      footer: {
        rights: 'Todos los derechos reservados.',
        admin: 'Admin'
      },
      card: {
        viewMore: 'Ver más'
      }
    },
    en: {
      hero: {
        title: 'Marketplace of Solutions for Cities',
        subtitle: 'Discover the best tech solutions to transform your city'
      },
      filters: {
        all: 'All'
      },
      categories: {
        mobility: 'Mobility',
        publicSpace: 'Public Space',
        fintech: 'Fintech',
        legalAI: 'LegalAI',
        data: 'Data'
      },
      loading: 'Loading solutions...',
      emptyState: 'No solutions found',
      modal: {
        website: 'Visit website',
        email: 'Send email',
        phone: 'Call',
        founders: 'Founders'
      },
      footer: {
        rights: 'All rights reserved.',
        admin: 'Admin'
      },
      card: {
        viewMore: 'View more'
      }
    }
  },

  // Initialize i18n
  init() {
    // Load language from localStorage or default to 'es'
    const savedLang = localStorage.getItem('lang') || 'es';
    this.setLanguage(savedLang);

    // Setup language toggle
    const langToggle = document.getElementById('lang-toggle');
    if (langToggle) {
      langToggle.addEventListener('click', () => {
        const newLang = this.currentLang === 'es' ? 'en' : 'es';
        this.setLanguage(newLang);
      });
    }
  },

  // Set language
  setLanguage(lang) {
    this.currentLang = lang;
    localStorage.setItem('lang', lang);

    // Update current lang display
    const currentLangEl = document.getElementById('current-lang');
    if (currentLangEl) {
      currentLangEl.textContent = lang.toUpperCase();
    }

    // Update all i18n elements
    this.updateElements();

    // Dispatch event for other components to react
    document.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang } }));
  },

  // Update all elements with data-i18n attribute
  updateElements() {
    const elements = document.querySelectorAll('[data-i18n]');

    elements.forEach(el => {
      const key = el.getAttribute('data-i18n');
      const translation = this.get(key);

      if (translation) {
        el.textContent = translation;
      }
    });
  },

  // Get translation by key (supports nested keys like 'hero.title')
  get(key) {
    const keys = key.split('.');
    let value = this.translations[this.currentLang];

    for (const k of keys) {
      if (value && typeof value === 'object') {
        value = value[k];
      } else {
        return key; // Return key if not found
      }
    }

    return value || key;
  },

  // Get category name
  getCategoryName(category) {
    const categoryMap = {
      'mobility': 'categories.mobility',
      'public-space': 'categories.publicSpace',
      'fintech': 'categories.fintech',
      'legal-ai': 'categories.legalAI',
      'data': 'categories.data'
    };

    return this.get(categoryMap[category] || category);
  },

  // Get category emoji
  getCategoryEmoji(category) {
    const emojiMap = {
      'mobility': '🚗',
      'public-space': '🏛️',
      'fintech': '💰',
      'legal-ai': '⚖️',
      'data': '📊'
    };

    return emojiMap[category] || '';
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => i18n.init());
} else {
  i18n.init();
}
