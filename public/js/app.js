// app.js - Main application logic

const app = {
  solutions: [],
  currentFilter: 'all',

  // Initialize app
  async init() {
    await this.loadSolutions();
    this.setupFilters();
    this.setupModal();
    this.renderSolutions();

    // Re-render when language changes
    document.addEventListener('languageChanged', () => {
      this.renderSolutions();
    });
  },

  // Load solutions from API
  async loadSolutions() {
    const loading = document.getElementById('loading');
    const grid = document.getElementById('solutions-grid');

    utils.show(loading);
    utils.hide(grid);

    try {
      const response = await fetch('/api/solutions');
      if (!response.ok) throw new Error('Failed to fetch solutions');

      this.solutions = await response.json();
      utils.hide(loading);
      utils.show(grid);
    } catch (error) {
      console.error('Error loading solutions:', error);
      utils.hide(loading);
      utils.notify('Error al cargar las soluciones', 'error');
    }
  },

  // Setup filter buttons
  setupFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Update active state
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Set current filter
        this.currentFilter = btn.dataset.category;

        // Re-render solutions
        this.renderSolutions();
      });
    });
  },

  // Setup modal
  setupModal() {
    const modal = document.getElementById('modal');
    const modalClose = document.getElementById('modal-close');
    const modalOverlay = document.getElementById('modal-overlay');

    const closeModal = () => {
      utils.hide(modal);
      document.body.style.overflow = '';
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalOverlay) modalOverlay.addEventListener('click', closeModal);

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.style.display !== 'none') {
        closeModal();
      }
    });
  },

  // Render solutions grid
  renderSolutions() {
    const grid = document.getElementById('solutions-grid');
    const emptyState = document.getElementById('empty-state');
    const viewMoreContainer = document.getElementById('view-more-container');

    // Filter solutions
    const filtered = this.currentFilter === 'all'
      ? this.solutions
      : this.solutions.filter(s => s.category === this.currentFilter);

    // Show empty state if no solutions
    if (filtered.length === 0) {
      utils.hide(grid);
      utils.show(emptyState);
      if (viewMoreContainer) utils.hide(viewMoreContainer);
      return;
    }

    utils.show(grid);
    utils.hide(emptyState);

    // On home page, show only 3 random solutions
    const isHomePage = window.location.pathname === '/' || window.location.pathname === '/index.html';
    let toDisplay = filtered;

    if (isHomePage) {
      // Shuffle and take 3
      const shuffled = [...filtered].sort(() => Math.random() - 0.5);
      toDisplay = shuffled.slice(0, 3);

      // Show "Ver más" button if there are more solutions
      if (viewMoreContainer) {
        if (filtered.length > 3) {
          utils.show(viewMoreContainer);
        } else {
          utils.hide(viewMoreContainer);
        }
      }
    } else {
      // On "all solutions" page, hide the button
      if (viewMoreContainer) utils.hide(viewMoreContainer);
    }

    // Render cards
    grid.innerHTML = toDisplay.map(solution => this.createSolutionCard(solution)).join('');

    // Add click handlers
    grid.querySelectorAll('.solution-card').forEach((card, index) => {
      card.addEventListener('click', () => {
        this.showSolutionDetails(toDisplay[index]);
      });
    });
  },

  // Create solution card HTML
  createSolutionCard(solution) {
    const lang = i18n.currentLang;
    const description = solution.description[lang] || solution.description.es || '';
    const imageUrl = solution.images && solution.images[0]
      ? solution.images[0]
      : '/images/placeholder.png';

    const categoryName = i18n.getCategoryName(solution.category);
    const categoryEmoji = i18n.getCategoryEmoji(solution.category);

    return `
      <article class="solution-card" data-id="${solution.id}">
        <img
          src="${imageUrl}"
          alt="${solution.name}"
          class="solution-image"
          loading="lazy"
          onerror="this.src='/images/placeholder.png'"
        >
        <div class="solution-content">
          <p class="solution-category">${categoryEmoji} ${categoryName}</p>
          <h3 class="solution-name">${solution.name}</h3>
          <p class="solution-description">${utils.truncate(description, 120)}</p>
          <span class="solution-cta">${i18n.get('card.viewMore')}</span>
        </div>
      </article>
    `;
  },

  // Show solution details in modal
  showSolutionDetails(solution) {
    const lang = i18n.currentLang;
    const description = solution.description[lang] || solution.description.es || '';

    // Update modal content
    document.getElementById('modal-name').textContent = solution.name;

    const categoryName = i18n.getCategoryName(solution.category);
    const categoryEmoji = i18n.getCategoryEmoji(solution.category);
    document.getElementById('modal-category').textContent = `${categoryEmoji} ${categoryName}`;

    document.getElementById('modal-description').textContent = description;

    // Gallery
    const gallery = document.getElementById('modal-gallery');
    if (solution.images && solution.images.length > 0) {
      gallery.innerHTML = solution.images.map(img => `
        <img src="${img}" alt="${solution.name}" onerror="this.style.display='none'">
      `).join('');
      utils.show(gallery);
    } else {
      utils.hide(gallery);
    }

    // Website
    const websiteLink = document.getElementById('modal-website');
    if (solution.website) {
      websiteLink.href = solution.website;
      utils.show(websiteLink);
    } else {
      utils.hide(websiteLink);
    }

    // Email
    const emailLink = document.getElementById('modal-email');
    if (solution.email) {
      emailLink.href = `mailto:${solution.email}`;
      emailLink.querySelector('span').textContent = solution.email;
      utils.show(emailLink);
    } else {
      utils.hide(emailLink);
    }

    // Phone
    const phoneLink = document.getElementById('modal-phone');
    if (solution.phone) {
      phoneLink.href = `tel:${solution.phone}`;
      phoneLink.querySelector('span').textContent = solution.phone;
      utils.show(phoneLink);
    } else {
      utils.hide(phoneLink);
    }

    // LinkedIn Founders
    const linkedinContainer = document.getElementById('modal-linkedin');
    const linkedinList = document.getElementById('modal-linkedin-list');
    if (solution.linkedinFounders && solution.linkedinFounders.length > 0) {
      linkedinList.innerHTML = solution.linkedinFounders.map((url, index) => `
        <a href="${url}" target="_blank" rel="noopener noreferrer" class="modal-link">
          🔗 Founder ${index + 1}
        </a>
      `).join('');
      utils.show(linkedinContainer);
    } else {
      utils.hide(linkedinContainer);
    }

    // Show modal
    const modal = document.getElementById('modal');
    utils.show(modal);
    document.body.style.overflow = 'hidden'; // Prevent scrolling
  }
};

// Initialize app when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => app.init());
} else {
  app.init();
}
