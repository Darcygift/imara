/**
 * Validation schemas for common request patterns
 */

export const authSchemas = {
  register: {
    type: 'object',
    properties: {
      email: { type: 'string', format: 'email' },
      password: { type: 'string', minLength: 6 },
      name: { type: 'string', minLength: 2 },
      phone: { type: 'string' },
      company: { type: 'string' },
    },
    required: ['email', 'password', 'name'],
  },

  login: {
    type: 'object',
    properties: {
      email: { type: 'string', format: 'email' },
      password: { type: 'string' },
    },
    required: ['email', 'password'],
  },
};

export const propertySchemas = {
  create: {
    type: 'object',
    properties: {
      name: { type: 'string', minLength: 3 },
      address: { type: 'string', minLength: 5 },
      city: { type: 'string' },
      unitCount: { type: 'number', minimum: 1 },
      rentAmount: { type: 'number', minimum: 0 },
      description: { type: 'string' },
    },
    required: ['name', 'address', 'unitCount', 'rentAmount'],
  },

  update: {
    type: 'object',
    properties: {
      name: { type: 'string', minLength: 3 },
      address: { type: 'string', minLength: 5 },
      city: { type: 'string' },
      rentAmount: { type: 'number', minimum: 0 },
      description: { type: 'string' },
    },
  },
};

export const tenantSchemas = {
  create: {
    type: 'object',
    properties: {
      name: { type: 'string', minLength: 2 },
      email: { type: 'string', format: 'email' },
      phone: { type: 'string' },
      idNumber: { type: 'string' },
      propertyId: { type: 'number' },
      unitId: { type: 'number' },
      leaseStart: { type: 'string', format: 'date' },
      leaseEnd: { type: 'string', format: 'date' },
    },
    required: ['name', 'phone', 'propertyId', 'unitId'],
  },
};

export const paymentSchemas = {
  create: {
    type: 'object',
    properties: {
      tenantId: { type: 'number' },
      amount: { type: 'number', minimum: 0 },
      dueDate: { type: 'string', format: 'date' },
      month: { type: 'string' },
    },
    required: ['tenantId', 'amount', 'dueDate'],
  },

  update: {
    type: 'object',
    properties: {
      status: { type: 'string', enum: ['pending', 'completed', 'overdue'] },
      paidAmount: { type: 'number', minimum: 0 },
      paymentMethod: { type: 'string' },
      paymentDate: { type: 'string', format: 'date' },
    },
  },

  record: {
    type: 'object',
    properties: {
      paymentId: { type: 'number' },
      amount: { type: 'number', minimum: 0 },
      paymentMethod: { type: 'string' },
      reference: { type: 'string' },
    },
    required: ['paymentId', 'amount', 'paymentMethod'],
  },
};

/**
 * Simple JSON Schema validator
 * For production, use ajv or joi library
 */
export function validateSchema(data, schema) {
  const errors = [];

  // Check required fields
  if (schema.required) {
    for (const field of schema.required) {
      if (!data[field]) {
        errors.push(`Missing required field: ${field}`);
      }
    }
  }

  // Check types and constraints
  for (const [key, rules] of Object.entries(schema.properties || {})) {
    if (data[key] === undefined) continue;

    const value = data[key];

    if (rules.type === 'string' && typeof value !== 'string') {
      errors.push(`Field '${key}' must be a string`);
    }

    if (rules.type === 'number' && typeof value !== 'number') {
      errors.push(`Field '${key}' must be a number`);
    }

    if (rules.minLength && value.length < rules.minLength) {
      errors.push(
        `Field '${key}' must be at least ${rules.minLength} characters`
      );
    }

    if (rules.minimum && value < rules.minimum) {
      errors.push(`Field '${key}' must be at least ${rules.minimum}`);
    }

    if (rules.enum && !rules.enum.includes(value)) {
      errors.push(
        `Field '${key}' must be one of: ${rules.enum.join(', ')}`
      );
    }
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}
