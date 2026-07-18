/**
 * Request/Response logging middleware
 */

export function requestLogger(req, res, next) {
  const start = Date.now();
  const method = req.method;
  const path = req.path;

  // Log request
  console.log(`[${new Date().toISOString()}] ${method} ${path}`);

  if (req.body && Object.keys(req.body).length > 0) {
    console.log('[REQUEST BODY]', sanitizeData(req.body));
  }

  // Intercept response
  const originalJson = res.json.bind(res);
  res.json = function (data) {
    const duration = Date.now() - start;
    const status = res.statusCode;

    console.log(
      `[${new Date().toISOString()}] ${method} ${path} ${status} ${duration}ms`
    );

    if (status >= 400) {
      console.log('[RESPONSE ERROR]', sanitizeData(data));
    }

    return originalJson(data);
  };

  next();
}

/**
 * Remove sensitive data from logs
 */
function sanitizeData(data) {
  if (!data || typeof data !== 'object') return data;

  const sanitized = { ...data };
  const sensitiveFields = ['password', 'token', 'secret', 'apiKey'];

  for (const field of sensitiveFields) {
    if (sanitized[field]) {
      sanitized[field] = '[REDACTED]';
    }
  }

  return sanitized;
}

/**
 * Performance monitoring
 */
export function performanceMonitor(req, res, next) {
  const start = process.hrtime.bigint();

  res.on('finish', () => {
    const end = process.hrtime.bigint();
    const duration = Number(end - start) / 1000000; // Convert to milliseconds

    if (duration > 1000) {
      console.warn(
        `[SLOW REQUEST] ${req.method} ${req.path} took ${duration.toFixed(2)}ms`
      );
    }
  });

  next();
}
