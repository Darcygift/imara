import jwt from 'jsonwebtoken';

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';

/**
 * Verify JWT token and extract user information
 */
export function verifyToken(token) {
  try {
    return jwt.verify(token, JWT_SECRET);
  } catch (error) {
    return null;
  }
}

/**
 * Generate JWT token for user
 */
export function generateToken(userId, email) {
  return jwt.sign(
    { userId, email, iat: Date.now() },
    JWT_SECRET,
    { expiresIn: '7d' }
  );
}

/**
 * Middleware to check if user is authenticated
 */
export function authMiddleware(req, res, next) {
  try {
    const authHeader = req.headers.authorization;
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Missing or invalid authorization header',
      });
    }

    const token = authHeader.substring(7);
    const decoded = verifyToken(token);

    if (!decoded) {
      return res.status(401).json({
        success: false,
        message: 'Invalid or expired token',
      });
    }

    // Attach user info to request
    req.user = decoded;
    next();
  } catch (error) {
    console.error('[AUTH ERROR]', error.message);
    res.status(500).json({
      success: false,
      message: 'Authentication error',
    });
  }
}

/**
 * Middleware to validate request body
 */
export function validateRequest(schema) {
  return (req, res, next) => {
    try {
      const validation = schema.safeParse(req.body);
      
      if (!validation.success) {
        return res.status(400).json({
          success: false,
          message: 'Validation failed',
          errors: validation.error.errors,
        });
      }

      req.validatedData = validation.data;
      next();
    } catch (error) {
      console.error('[VALIDATION ERROR]', error.message);
      res.status(500).json({
        success: false,
        message: 'Validation error',
      });
    }
  };
}

/**
 * Middleware for error handling
 */
export function errorHandler(err, req, res, next) {
  console.error('[ERROR]', err);

  const status = err.status || 500;
  const message = err.message || 'Internal server error';

  res.status(status).json({
    success: false,
    message,
    ...(process.env.NODE_ENV === 'development' && { error: err.stack }),
  });
}
