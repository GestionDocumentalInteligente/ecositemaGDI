// utils.js - Utility functions

const utils = {
  // Truncate text to specified length
  truncate(text, maxLength = 120) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
  },

  // Format date to locale string
  formatDate(dateString) {
    const date = new Date(dateString);
    const lang = i18n.currentLang || 'es';
    return date.toLocaleDateString(lang === 'es' ? 'es-AR' : 'en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  },

  // Show/hide element
  show(element) {
    if (element) element.style.display = '';
  },

  hide(element) {
    if (element) element.style.display = 'none';
  },

  toggle(element) {
    if (element) {
      element.style.display = element.style.display === 'none' ? '' : 'none';
    }
  },

  // Debounce function
  debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  // Show notification/toast
  notify(message, type = 'info') {
    // Simple console notification for now
    // Can be enhanced with a toast library later
    console.log(`[${type.toUpperCase()}]`, message);

    // Could add a simple toast notification here
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#0A2463'};
      color: white;
      padding: 1rem 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      z-index: 9999;
      animation: slideIn 0.3s ease-out;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'slideOut 0.3s ease-out';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
};

// Add CSS animations for toast
if (!document.getElementById('toast-animations')) {
  const style = document.createElement('style');
  style.id = 'toast-animations';
  style.textContent = `
    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
}
