/**
 * Standard API response formatter
 */

export function successResponse(res, data, message = 'Success', status = 200) {
  return res.status(status).json({
    success: true,
    message,
    data,
    timestamp: new Date().toISOString(),
  });
}

export function errorResponse(
  res,
  message = 'Error',
  status = 400,
  errors = null
) {
  return res.status(status).json({
    success: false,
    message,
    ...(errors && { errors }),
    timestamp: new Date().toISOString(),
  });
}

export function notFoundResponse(res, resource = 'Resource') {
  return errorResponse(res, `${resource} not found`, 404);
}

export function unauthorizedResponse(res, message = 'Unauthorized') {
  return errorResponse(res, message, 401);
}

export function forbiddenResponse(res, message = 'Forbidden') {
  return errorResponse(res, message, 403);
}

export function validationErrorResponse(res, errors) {
  return errorResponse(res, 'Validation failed', 422, errors);
}

export function conflictResponse(res, message = 'Resource already exists') {
  return errorResponse(res, message, 409);
}

/**
 * Paginated response formatter
 */
export function paginatedResponse(
  res,
  data,
  total,
  page = 1,
  limit = 10,
  message = 'Success'
) {
  const totalPages = Math.ceil(total / limit);

  return res.status(200).json({
    success: true,
    message,
    data,
    pagination: {
      total,
      page,
      limit,
      totalPages,
      hasNextPage: page < totalPages,
      hasPrevPage: page > 1,
    },
    timestamp: new Date().toISOString(),
  });
}
