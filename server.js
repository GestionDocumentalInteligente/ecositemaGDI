const express = require('express');
const fs = require('fs').promises;
const path = require('path');
const multer = require('multer');
const { v4: uuidv4 } = require('uuid');

const app = express();
const PORT = process.env.PORT || 3000;
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'admin123'; // Change in production!

// Middleware
app.use(express.json());
app.use(express.static('public'));

// File paths
const DATA_FILE = path.join(__dirname, 'data', 'solutions.json');
const IMAGES_DIR = path.join(__dirname, 'public', 'images', 'solutions');

// Ensure directories exist
async function ensureDirectories() {
  await fs.mkdir(path.join(__dirname, 'data'), { recursive: true });
  await fs.mkdir(IMAGES_DIR, { recursive: true });

  // Create empty solutions.json if it doesn't exist
  try {
    await fs.access(DATA_FILE);
  } catch {
    await fs.writeFile(DATA_FILE, JSON.stringify([], null, 2));
  }
}

// Configure multer for image uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, IMAGES_DIR);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, `${uuidv4()}${ext}`);
  }
});

const upload = multer({
  storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB max
  fileFilter: (req, file, cb) => {
    const allowedTypes = /jpeg|jpg|png|gif|webp/;
    const ext = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimeType = allowedTypes.test(file.mimetype);

    if (ext && mimeType) {
      cb(null, true);
    } else {
      cb(new Error('Only image files are allowed'));
    }
  }
});

// Auth middleware for admin routes
function requireAuth(req, res, next) {
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  const token = authHeader.substring(7);

  if (token !== ADMIN_PASSWORD) {
    return res.status(401).json({ error: 'Invalid credentials' });
  }

  next();
}

// Helper: Read solutions from file
async function readSolutions() {
  try {
    const data = await fs.readFile(DATA_FILE, 'utf-8');
    return JSON.parse(data);
  } catch (error) {
    console.error('Error reading solutions:', error);
    return [];
  }
}

// Helper: Write solutions to file
async function writeSolutions(solutions) {
  await fs.writeFile(DATA_FILE, JSON.stringify(solutions, null, 2));
}

// ============================================================
// PUBLIC ROUTES
// ============================================================

// Get all solutions
app.get('/api/solutions', async (req, res) => {
  try {
    const solutions = await readSolutions();
    res.json(solutions);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch solutions' });
  }
});

// Get single solution
app.get('/api/solutions/:id', async (req, res) => {
  try {
    const solutions = await readSolutions();
    const solution = solutions.find(s => s.id === req.params.id);

    if (!solution) {
      return res.status(404).json({ error: 'Solution not found' });
    }

    res.json(solution);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch solution' });
  }
});

// ============================================================
// ADMIN ROUTES (Protected)
// ============================================================

// Admin login
app.post('/api/admin/login', (req, res) => {
  const { password } = req.body;

  if (password === ADMIN_PASSWORD) {
    res.json({ token: ADMIN_PASSWORD });
  } else {
    res.status(401).json({ error: 'Invalid password' });
  }
});

// Create solution
app.post('/api/admin/solutions', requireAuth, async (req, res) => {
  try {
    const solutions = await readSolutions();

    const newSolution = {
      id: uuidv4(),
      ...req.body,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };

    solutions.push(newSolution);
    await writeSolutions(solutions);

    res.status(201).json(newSolution);
  } catch (error) {
    res.status(500).json({ error: 'Failed to create solution' });
  }
});

// Update solution
app.put('/api/admin/solutions/:id', requireAuth, async (req, res) => {
  try {
    const solutions = await readSolutions();
    const index = solutions.findIndex(s => s.id === req.params.id);

    if (index === -1) {
      return res.status(404).json({ error: 'Solution not found' });
    }

    solutions[index] = {
      ...solutions[index],
      ...req.body,
      id: req.params.id, // Ensure ID doesn't change
      updatedAt: new Date().toISOString()
    };

    await writeSolutions(solutions);
    res.json(solutions[index]);
  } catch (error) {
    res.status(500).json({ error: 'Failed to update solution' });
  }
});

// Delete solution
app.delete('/api/admin/solutions/:id', requireAuth, async (req, res) => {
  try {
    const solutions = await readSolutions();
    const solution = solutions.find(s => s.id === req.params.id);

    if (!solution) {
      return res.status(404).json({ error: 'Solution not found' });
    }

    // Delete associated images
    if (solution.images && solution.images.length > 0) {
      for (const imagePath of solution.images) {
        const fullPath = path.join(__dirname, 'public', imagePath);
        try {
          await fs.unlink(fullPath);
        } catch (error) {
          console.error('Failed to delete image:', fullPath, error);
        }
      }
    }

    const filtered = solutions.filter(s => s.id !== req.params.id);
    await writeSolutions(filtered);

    res.json({ message: 'Solution deleted successfully' });
  } catch (error) {
    res.status(500).json({ error: 'Failed to delete solution' });
  }
});

// Upload images
app.post('/api/admin/upload', requireAuth, upload.array('images', 4), (req, res) => {
  try {
    const filePaths = req.files.map(file => `/images/solutions/${file.filename}`);
    res.json({ images: filePaths });
  } catch (error) {
    res.status(500).json({ error: 'Failed to upload images' });
  }
});

// ============================================================
// START SERVER
// ============================================================

ensureDirectories().then(() => {
  app.listen(PORT, () => {
    console.log(`🚀 Cero1 Marketplace running on port ${PORT}`);
    console.log(`📁 Data file: ${DATA_FILE}`);
    console.log(`🖼️  Images directory: ${IMAGES_DIR}`);
  });
}).catch(error => {
  console.error('Failed to start server:', error);
  process.exit(1);
});
