// admin.js - Admin panel logic

const admin = {
  token: null,
  solutions: [],
  editingId: null,

  // Initialize admin panel
  init() {
    // Check if already logged in
    this.token = localStorage.getItem('adminToken');

    if (this.token) {
      this.showDashboard();
    } else {
      this.showLogin();
    }

    this.setupEventListeners();
  },

  // Setup all event listeners
  setupEventListeners() {
    // Login form
    document.getElementById('login-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleLogin();
    });

    // Logout
    document.getElementById('logout-btn')?.addEventListener('click', () => {
      this.handleLogout();
    });

    // Add new solution
    document.getElementById('add-new-btn')?.addEventListener('click', () => {
      this.showSolutionForm();
    });

    // Solution form
    document.getElementById('solution-form')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleSolutionSubmit();
    });

    // Cancel form
    document.getElementById('cancel-btn')?.addEventListener('click', () => {
      this.closeSolutionForm();
    });

    // Close form modal
    document.getElementById('form-modal-close')?.addEventListener('click', () => {
      this.closeSolutionForm();
    });

    document.getElementById('form-modal-overlay')?.addEventListener('click', () => {
      this.closeSolutionForm();
    });

    // Add LinkedIn input
    document.getElementById('add-linkedin-btn')?.addEventListener('click', () => {
      const container = document.getElementById('linkedin-inputs');
      const currentInputs = container.querySelectorAll('.linkedin-input');

      if (currentInputs.length < 5) {
        const input = document.createElement('input');
        input.type = 'url';
        input.className = 'linkedin-input';
        input.placeholder = `https://linkedin.com/in/founder${currentInputs.length + 1}`;
        container.appendChild(input);
      } else {
        utils.notify('Máximo 5 LinkedIn founders', 'info');
      }
    });
  },

  // Show login screen
  showLogin() {
    document.getElementById('login-screen').style.display = 'flex';
    document.getElementById('admin-dashboard').style.display = 'none';
  },

  // Show dashboard
  async showDashboard() {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('admin-dashboard').style.display = 'block';

    await this.loadSolutions();
    this.renderSolutionsTable();
  },

  // Handle login
  async handleLogin() {
    const password = document.getElementById('password').value;

    try {
      const response = await fetch('/api/admin/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password })
      });

      if (!response.ok) {
        utils.notify('Password incorrecta', 'error');
        return;
      }

      const data = await response.json();
      this.token = data.token;
      localStorage.setItem('adminToken', this.token);

      this.showDashboard();
    } catch (error) {
      console.error('Login error:', error);
      utils.notify('Error al iniciar sesión', 'error');
    }
  },

  // Handle logout
  handleLogout() {
    this.token = null;
    localStorage.removeItem('adminToken');
    this.showLogin();
    document.getElementById('password').value = '';
  },

  // Load solutions
  async loadSolutions() {
    try {
      const response = await fetch('/api/solutions');
      if (!response.ok) throw new Error('Failed to fetch');

      this.solutions = await response.json();
    } catch (error) {
      console.error('Error loading solutions:', error);
      utils.notify('Error al cargar soluciones', 'error');
    }
  },

  // Render solutions table
  renderSolutionsTable() {
    const tbody = document.getElementById('solutions-tbody');
    const empty = document.getElementById('table-empty');
    const totalCount = document.getElementById('total-count');

    totalCount.textContent = this.solutions.length;

    if (this.solutions.length === 0) {
      tbody.innerHTML = '';
      empty.style.display = 'block';
      return;
    }

    empty.style.display = 'none';

    tbody.innerHTML = this.solutions.map(solution => {
      const categoryEmoji = i18n.getCategoryEmoji(solution.category);
      const categoryName = i18n.getCategoryName(solution.category);

      return `
        <tr>
          <td><strong>${solution.name}</strong></td>
          <td>${categoryEmoji} ${categoryName}</td>
          <td><a href="${solution.website}" target="_blank">${solution.website}</a></td>
          <td>${solution.images ? solution.images.length : 0}</td>
          <td class="actions">
            <button class="btn-edit" data-id="${solution.id}">Editar</button>
            <button class="btn-danger" data-id="${solution.id}">Eliminar</button>
          </td>
        </tr>
      `;
    }).join('');

    // Add event listeners to edit/delete buttons
    tbody.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        this.editSolution(id);
      });
    });

    tbody.querySelectorAll('.btn-danger').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        this.deleteSolution(id);
      });
    });
  },

  // Show solution form (create or edit)
  showSolutionForm(solution = null) {
    this.editingId = solution ? solution.id : null;

    const modal = document.getElementById('form-modal');
    const title = document.getElementById('form-title');
    const form = document.getElementById('solution-form');

    title.textContent = solution ? 'Editar Solución' : 'Nueva Solución';

    // Reset form
    form.reset();
    document.getElementById('linkedin-inputs').innerHTML = `
      <input type="url" class="linkedin-input" placeholder="https://linkedin.com/in/founder1">
    `;
    document.getElementById('current-images').innerHTML = '';

    // Fill form if editing
    if (solution) {
      document.getElementById('solution-id').value = solution.id;
      document.getElementById('name').value = solution.name;
      document.getElementById('category').value = solution.category;
      document.getElementById('description-es').value = solution.description.es || '';
      document.getElementById('description-en').value = solution.description.en || '';
      document.getElementById('website').value = solution.website;
      document.getElementById('email').value = solution.email || '';
      document.getElementById('phone').value = solution.phone || '';

      // LinkedIn founders
      if (solution.linkedinFounders && solution.linkedinFounders.length > 0) {
        const container = document.getElementById('linkedin-inputs');
        container.innerHTML = '';

        solution.linkedinFounders.forEach((url, index) => {
          const input = document.createElement('input');
          input.type = 'url';
          input.className = 'linkedin-input';
          input.value = url;
          input.placeholder = `https://linkedin.com/in/founder${index + 1}`;
          container.appendChild(input);
        });
      }

      // Current images
      if (solution.images && solution.images.length > 0) {
        const imagesContainer = document.getElementById('current-images');
        imagesContainer.innerHTML = solution.images.map(img => `
          <img src="${img}" alt="Current image">
        `).join('');
      }
    }

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  },

  // Close solution form
  closeSolutionForm() {
    const modal = document.getElementById('form-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    this.editingId = null;
  },

  // Handle solution submit
  async handleSolutionSubmit() {
    const formData = {
      name: document.getElementById('name').value,
      category: document.getElementById('category').value,
      description: {
        es: document.getElementById('description-es').value,
        en: document.getElementById('description-en').value
      },
      website: document.getElementById('website').value,
      email: document.getElementById('email').value || null,
      phone: document.getElementById('phone').value || null,
      linkedinFounders: [],
      images: []
    };

    // Get LinkedIn URLs
    const linkedinInputs = document.querySelectorAll('.linkedin-input');
    linkedinInputs.forEach(input => {
      if (input.value.trim()) {
        formData.linkedinFounders.push(input.value.trim());
      }
    });

    // Upload images first
    const imagesInput = document.getElementById('images');
    if (imagesInput.files.length > 0) {
      const uploadedImages = await this.uploadImages(imagesInput.files);
      if (uploadedImages) {
        formData.images = uploadedImages;
      }
    } else if (this.editingId) {
      // Keep existing images if editing
      const currentSolution = this.solutions.find(s => s.id === this.editingId);
      if (currentSolution && currentSolution.images) {
        formData.images = currentSolution.images;
      }
    }

    // Create or update
    try {
      const url = this.editingId
        ? `/api/admin/solutions/${this.editingId}`
        : '/api/admin/solutions';

      const method = this.editingId ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.token}`
        },
        body: JSON.stringify(formData)
      });

      if (!response.ok) {
        throw new Error('Failed to save solution');
      }

      utils.notify(
        this.editingId ? 'Solución actualizada' : 'Solución creada',
        'success'
      );

      this.closeSolutionForm();
      await this.loadSolutions();
      this.renderSolutionsTable();
    } catch (error) {
      console.error('Error saving solution:', error);
      utils.notify('Error al guardar solución', 'error');
    }
  },

  // Upload images
  async uploadImages(files) {
    const formData = new FormData();

    for (let i = 0; i < Math.min(files.length, 4); i++) {
      formData.append('images', files[i]);
    }

    try {
      const response = await fetch('/api/admin/upload', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`
        },
        body: formData
      });

      if (!response.ok) throw new Error('Upload failed');

      const data = await response.json();
      return data.images;
    } catch (error) {
      console.error('Error uploading images:', error);
      utils.notify('Error al subir imágenes', 'error');
      return null;
    }
  },

  // Edit solution
  editSolution(id) {
    const solution = this.solutions.find(s => s.id === id);
    if (solution) {
      this.showSolutionForm(solution);
    }
  },

  // Delete solution
  async deleteSolution(id) {
    const solution = this.solutions.find(s => s.id === id);
    if (!solution) return;

    const confirmed = confirm(`¿Eliminar "${solution.name}"?`);
    if (!confirmed) return;

    try {
      const response = await fetch(`/api/admin/solutions/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${this.token}`
        }
      });

      if (!response.ok) throw new Error('Delete failed');

      utils.notify('Solución eliminada', 'success');

      await this.loadSolutions();
      this.renderSolutionsTable();
    } catch (error) {
      console.error('Error deleting solution:', error);
      utils.notify('Error al eliminar solución', 'error');
    }
  }
};

// Initialize admin when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => admin.init());
} else {
  admin.init();
}
