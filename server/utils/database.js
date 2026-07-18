/**
 * Database utility functions
 * Note: For production, implement actual database connection
 */

// Mock database store (in production, use PostgreSQL)
const mockDatabase = {
  users: [],
  properties: [],
  tenants: [],
  payments: [],
  units: [],
};

/**
 * Initialize database connection
 * For production: Use pg, drizzle-orm, or similar
 */
export async function initializeDatabase() {
  console.log('[DB] Initializing database connection...');

  try {
    // In production, establish connection:
    // const pool = new Pool({ connectionString: process.env.DATABASE_URL });
    // const connection = await pool.connect();
    // console.log('[DB] Connected successfully');

    console.log('[DB] Using mock database for development');
    return mockDatabase;
  } catch (error) {
    console.error('[DB ERROR]', error.message);
    throw error;
  }
}

/**
 * Get database connection
 */
export function getDatabase() {
  return mockDatabase;
}

/**
 * Generate unique ID
 */
export function generateId() {
  return Math.floor(Math.random() * 1000000);
}

/**
 * Get paginated results
 */
export function paginate(items, page = 1, limit = 10) {
  const start = (page - 1) * limit;
  const end = start + limit;

  return {
    data: items.slice(start, end),
    total: items.length,
    page,
    limit,
  };
}

/**
 * Filter items by criteria
 */
export function filterItems(items, criteria) {
  return items.filter((item) => {
    for (const [key, value] of Object.entries(criteria)) {
      if (item[key] !== value) return false;
    }
    return true;
  });
}

/**
 * Sort items
 */
export function sortItems(items, field, order = 'asc') {
  const sorted = [...items];

  sorted.sort((a, b) => {
    if (a[field] < b[field]) return order === 'asc' ? -1 : 1;
    if (a[field] > b[field]) return order === 'asc' ? 1 : -1;
    return 0;
  });

  return sorted;
}

/**
 * Deep clone object
 */
export function deepClone(obj) {
  return JSON.parse(JSON.stringify(obj));
}

/**
 * Update object fields
 */
export function updateFields(target, updates) {
  const result = { ...target };

  for (const [key, value] of Object.entries(updates)) {
    if (value !== undefined && value !== null) {
      result[key] = value;
    }
  }

  return result;
}

/**
 * Check if email already exists
 */
export function emailExists(email) {
  return mockDatabase.users.some((user) => user.email === email);
}

/**
 * Get user by email
 */
export function getUserByEmail(email) {
  return mockDatabase.users.find((user) => user.email === email);
}

/**
 * Get user by ID
 */
export function getUserById(id) {
  return mockDatabase.users.find((user) => user.id === id);
}
