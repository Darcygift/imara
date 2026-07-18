import express from "express";
import { Router } from "express";
import { hashPassword, comparePassword, validatePasswordStrength } from "../utils/password.js";
import { successResponse, errorResponse, conflictResponse, validationErrorResponse } from "../utils/response.js";
import { generateToken, verifyToken } from "../middleware/auth.js";
import { emailExists, getUserByEmail, getUserById } from "../utils/database.js";

const router = Router();

/**
 * POST /api/auth/register
 * Register a new landlord
 */
router.post("/register", async (req, res) => {
  try {
    const { name, email, phone, password, company } = req.body;

    // Validate input
    if (!name || !email || !password) {
      return validationErrorResponse(res, ["Name, email, and password are required"]);
    }

    // Validate password strength
    const passwordValidation = validatePasswordStrength(password);
    if (!passwordValidation.valid) {
      return validationErrorResponse(res, passwordValidation.errors);
    }

    // Check if email already exists
    if (emailExists(email)) {
      return conflictResponse(res, "Email already registered");
    }

    // Hash password
    const hashedPassword = await hashPassword(password);

    // TODO: Create landlord in database with:
    // - id, name, email, phone, company, hashedPassword, createdAt
    const landlord = {
      id: Math.floor(Math.random() * 1000000),
      name,
      email,
      phone,
      company,
      createdAt: new Date().toISOString(),
    };

    // Generate JWT token
    const token = generateToken(landlord.id, email);

    return successResponse(
      res,
      {
        token,
        landlord,
      },
      "Registration successful",
      201
    );
  } catch (error) {
    console.error("[REGISTER ERROR]", error.message);
    return errorResponse(res, "Registration failed", 500);
  }
});

/**
 * POST /api/auth/login
 * Login with email and password
 */
router.post("/login", async (req, res) => {
  try {
    const { email, password } = req.body;

    // Validate input
    if (!email || !password) {
      return validationErrorResponse(res, ["Email and password are required"]);
    }

    // TODO: Find user in database by email
    const landlord = getUserByEmail(email);
    if (!landlord) {
      return errorResponse(res, "Invalid email or password", 401);
    }

    // TODO: Compare provided password with stored hash
    const passwordMatch = await comparePassword(password, landlord.passwordHash);
    if (!passwordMatch) {
      return errorResponse(res, "Invalid email or password", 401);
    }

    // Generate JWT token
    const token = generateToken(landlord.id, email);

    // Remove sensitive data
    const { passwordHash, ...safeData } = landlord;

    return successResponse(res, {
      token,
      landlord: safeData,
    });
  } catch (error) {
    console.error("[LOGIN ERROR]", error.message);
    return errorResponse(res, "Login failed", 500);
  }
});
  } catch (error) {
    res.status(401).json({
      success: false,
      message: "Invalid credentials",
      error: error.message,
    });
  }
});

/**
 * GET /api/auth/me
 * Get current user profile (requires authentication)
 */
router.get("/me", async (req, res) => {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith("Bearer ")) {
      return errorResponse(res, "Missing authorization header", 401);
    }

    const token = authHeader.substring(7);
    const decoded = verifyToken(token);

    if (!decoded) {
      return errorResponse(res, "Invalid or expired token", 401);
    }

    // TODO: Get user from database by ID
    const landlord = getUserById(decoded.userId);

    if (!landlord) {
      return errorResponse(res, "User not found", 404);
    }

    const { passwordHash, ...safeData } = landlord;

    return successResponse(res, safeData);
  } catch (error) {
    console.error("[GET ME ERROR]", error.message);
    return errorResponse(res, "Unauthorized", 401);
  }
});

/**
 * PUT /api/auth/profile
 * Update user profile
 */
router.put("/profile", async (req, res) => {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith("Bearer ")) {
      return errorResponse(res, "Missing authorization header", 401);
    }

    const token = authHeader.substring(7);
    const decoded = verifyToken(token);

    if (!decoded) {
      return errorResponse(res, "Invalid or expired token", 401);
    }

    const { name, phone, address, city } = req.body;

    // Validate input
    if (!name && !phone && !address && !city) {
      return validationErrorResponse(res, ["At least one field must be provided"]);
    }

    // TODO: Update user in database with:
    // - name, phone, address, city (if provided)
    // - updatedAt timestamp

    const updatedLandlord = {
      id: decoded.userId,
      email: decoded.email,
      ...(name && { name }),
      ...(phone && { phone }),
      ...(address && { address }),
      ...(city && { city }),
      updatedAt: new Date().toISOString(),
    };

    return successResponse(
      res,
      updatedLandlord,
      "Profile updated successfully"
    );
  } catch (error) {
    console.error("[UPDATE PROFILE ERROR]", error.message);
    res.json({
      success: true,
      message: "Profile updated",
      data: {
        id: 1,
        name,
        phone,
        address,
        city,
      },
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: "Profile update failed",
      error: error.message,
    });
  }
});

export default router;
