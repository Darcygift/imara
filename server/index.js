import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import authRoutes from "./routes/auth.js";
import propertyRoutes from "./routes/properties.js";
import paymentRoutes from "./routes/payments.js";

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3001;
const NODE_ENV = process.env.NODE_ENV || "development";

// Middleware
app.use(cors({
  origin: process.env.FRONTEND_URL || "http://localhost:3000",
  credentials: true,
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Request logging middleware (development only)
if (NODE_ENV === "development") {
  app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
    next();
  });
}

// Health check
app.get("/health", (req, res) => {
  res.json({
    status: "ok",
    message: "Smart Rent API is running",
    environment: NODE_ENV,
    timestamp: new Date().toISOString(),
  });
});

// API Info
app.get("/", (req, res) => {
  res.json({
    name: "Smart Rent API",
    version: "1.0.0",
    description: "Professional Property Management System Backend",
    documentation: "/api-docs",
  });
});

// API Routes
app.use("/api/auth", authRoutes);
app.use("/api/properties", propertyRoutes);
app.use("/api/payments", paymentRoutes);

// TODO: Implement tenants routes
app.use("/api/tenants", (req, res) => {
  res.json({
    message: "Tenants routes - TODO: Implement with database integration",
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    message: "Route not found",
    path: req.path,
  });
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error("[ERROR]", err.stack);
  res.status(err.status || 500).json({
    success: false,
    message: "Internal server error",
    error: NODE_ENV === "development" ? err.message : undefined,
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`
╔═══════════════════════════════════════╗
║  Smart Rent API Server               ║
║  Environment: ${NODE_ENV.padEnd(22)}║
║  Port: ${PORT.toString().padEnd(28)}║
║  Time: ${new Date().toLocaleString().padEnd(22)}║
╚═══════════════════════════════════════╝
`);
  
  if (process.env.DATABASE_URL) {
    console.log(`✓ Database configured`);
  } else {
    console.log(`⚠ Database URL not configured`);
  }
  
  console.log(`\nAPI Health: http://localhost:${PORT}/health`);
  console.log(`API Base URL: http://localhost:${PORT}/api\n`);
});
