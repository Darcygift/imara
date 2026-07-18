# Smart Rent - Development Guide

Complete guide for developers contributing to Smart Rent.

## Project Setup

### Initial Setup

```bash
# 1. Clone repository
git clone https://github.com/Darcygift/imara.git
cd imara

# 2. Install dependencies
npm install

# 3. Setup environment variables
cp .env.example .env.local
# Edit .env.local with your configuration

# 4. Setup database (optional for local development)
# Create a PostgreSQL database or use Docker
createdb smart_rent_db

# 5. Start development servers
# Terminal 1: Frontend
npm run dev

# Terminal 2: Backend
npm run backend:dev
```

Frontend runs on `http://localhost:3000`
Backend runs on `http://localhost:3001`

## Development Workflow

### Frontend Development

#### Structure
```
app/                     # Next.js App Router
├── page.tsx            # Main dashboard
├── login/              # Login page
├── register/           # Registration page
├── layout.tsx          # Root layout
└── globals.css         # Global styles

components/            # Reusable React components
├── DashboardHeader.tsx
├── StatsCard.tsx
├── PaymentChart.tsx
└── RecentPayments.tsx

lib/                    # Utilities and services
├── api.ts             # Axios client
├── types.ts           # TypeScript interfaces
├── validators.ts      # Zod schemas
├── auth.ts            # Auth utilities
└── services/          # API service layer
    ├── auth.ts
    ├── properties.ts
    └── payments.ts
```

#### Creating Components

1. **Create component file in `/components`**
```typescript
// components/MyComponent.tsx
"use client"; // If using client-side features

interface MyComponentProps {
  title: string;
  onClick?: () => void;
}

export default function MyComponent({ title, onClick }: MyComponentProps) {
  return (
    <div className="card">
      <h2 className="text-xl font-bold">{title}</h2>
      {onClick && <button onClick={onClick} className="btn-primary">Click</button>}
    </div>
  );
}
```

2. **Use in pages**
```typescript
// app/page.tsx
import MyComponent from "@/components/MyComponent";

export default function Page() {
  return <MyComponent title="Hello" />;
}
```

#### Styling with Tailwind

- Use Tailwind utility classes: `flex`, `gap-4`, `p-6`
- Use CSS design tokens defined in `globals.css`
- Classes for common patterns: `btn-primary`, `card`, `input-field`, `badge`

```typescript
// Good
<div className="flex items-center gap-4 p-6 rounded-lg bg-background">
  <h1 className="text-2xl font-bold">Title</h1>
</div>

// Avoid
<div style={{ display: 'flex', gap: '1rem', padding: '1.5rem' }}>
  <h1 style={{ fontSize: '2rem', fontWeight: 'bold' }}>Title</h1>
</div>
```

#### Connecting to Backend API

```typescript
// components/PropertyList.tsx
"use client";

import { useEffect, useState } from "react";
import { propertyService } from "@/lib/services/properties";
import { Property } from "@/lib/types";

export default function PropertyList() {
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const loadProperties = async () => {
      try {
        const data = await propertyService.getAll();
        setProperties(data);
      } catch (err) {
        setError("Failed to load properties");
      } finally {
        setLoading(false);
      }
    };

    loadProperties();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="text-red-600">{error}</div>;

  return (
    <div className="space-y-4">
      {properties.map((prop) => (
        <div key={prop.id} className="card">
          <h3 className="font-bold">{prop.name}</h3>
          <p className="text-foreground/60">{prop.address}</p>
        </div>
      ))}
    </div>
  );
}
```

#### Forms and Validation

```typescript
// components/PropertyForm.tsx
"use client";

import { useState } from "react";
import { propertySchema, type PropertyInput } from "@/lib/validators";
import { propertyService } from "@/lib/services/properties";

export default function PropertyForm() {
  const [formData, setFormData] = useState<PropertyInput>({
    name: "",
    address: "",
    city: "",
    propertyType: "apartment",
    numberOfUnits: 1,
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});
    setLoading(true);

    try {
      // Validate with Zod
      const validated = propertySchema.parse(formData);
      
      // Submit to API
      await propertyService.create(validated);
      
      // Reset form
      setFormData({
        name: "",
        address: "",
        city: "",
        propertyType: "apartment",
        numberOfUnits: 1,
      });
    } catch (err: any) {
      if (err.errors) {
        // Zod validation errors
        const newErrors: Record<string, string> = {};
        err.errors.forEach((error: any) => {
          newErrors[error.path[0]] = error.message;
        });
        setErrors(newErrors);
      } else {
        setErrors({ submit: "Failed to create property" });
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {errors.submit && <div className="text-red-600">{errors.submit}</div>}
      
      <div>
        <label>Property Name</label>
        <input
          type="text"
          value={formData.name}
          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          className="input-field"
        />
        {errors.name && <p className="text-red-600 text-sm">{errors.name}</p>}
      </div>

      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Creating..." : "Create Property"}
      </button>
    </form>
  );
}
```

### Backend Development

#### Structure
```
server/
├── index.js              # Main server file
├── middleware/           # Express middleware
│   ├── auth.js          # JWT verification
│   └── validation.js    # Request validation
├── routes/              # API routes
│   ├── auth.js
│   ├── properties.js
│   ├── payments.js
│   └── tenants.js
├── controllers/         # Business logic
└── utils/              # Utilities
    └── database.js     # Database helpers
```

#### Creating API Routes

```javascript
// server/routes/myroute.js
import express from "express";
import { verifyAuth } from "../middleware/auth.js";

const router = express.Router();

/**
 * GET /api/myroute
 * Description of endpoint
 */
router.get("/", verifyAuth, async (req, res) => {
  try {
    const userId = req.user.id;
    
    // Business logic here
    
    res.json({
      success: true,
      data: {
        /* response data */
      },
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: "Error message",
      error: error.message,
    });
  }
});

export default router;
```

#### Authentication Middleware

```javascript
// server/middleware/auth.js
import { verifyToken } from "../lib/auth.js";

export function verifyAuth(req, res, next) {
  const authHeader = req.headers.authorization;
  
  if (!authHeader) {
    return res.status(401).json({
      success: false,
      message: "No authorization token",
    });
  }

  const token = authHeader.split(" ")[1];
  const payload = verifyToken(token);

  if (!payload) {
    return res.status(401).json({
      success: false,
      message: "Invalid token",
    });
  }

  req.user = payload;
  next();
}
```

#### Database Operations with Drizzle

```typescript
// Example: Get properties for user
import { db } from "@/lib/db/client";
import { properties } from "@/lib/db/schema";
import { eq } from "drizzle-orm";

export async function getUserProperties(landlordId: number) {
  return await db
    .select()
    .from(properties)
    .where(eq(properties.landlordId, landlordId));
}
```

#### Error Handling

```javascript
// Consistent error format
class AppError extends Error {
  constructor(message, statusCode) {
    super(message);
    this.statusCode = statusCode;
  }
}

// Usage
if (!user) {
  throw new AppError("User not found", 404);
}

// Error handler middleware catches it
app.use((err, req, res, next) => {
  const statusCode = err.statusCode || 500;
  res.status(statusCode).json({
    success: false,
    message: err.message,
  });
});
```

## Testing

### Running Tests

```bash
# Frontend tests
npm test

# Backend tests
npm run test:backend
```

### Writing Tests

```typescript
// components/__tests__/StatsCard.test.tsx
import { render, screen } from "@testing-library/react";
import StatsCard from "@/components/StatsCard";

describe("StatsCard", () => {
  it("renders with correct label and value", () => {
    render(
      <StatsCard
        label="Total Properties"
        value={12}
        icon="🏠"
        bgColor="bg-blue-100"
        textColor="text-blue-600"
      />
    );

    expect(screen.getByText("Total Properties")).toBeInTheDocument();
    expect(screen.getByText("12")).toBeInTheDocument();
  });
});
```

## Debugging

### Frontend Debugging

```typescript
// Add debug logs
console.log("[v0] Component mounted:", props);

// Use React DevTools Chrome extension
// Use Next.js debugging in VS Code
```

#### VS Code Debugging Setup

```json
// .vscode/launch.json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Next.js",
      "type": "node",
      "request": "launch",
      "program": "${workspaceFolder}/node_modules/.bin/next",
      "args": ["dev"],
      "cwd": "${workspaceFolder}"
    }
  ]
}
```

### Backend Debugging

```javascript
// Add debug logs
console.log("[DEBUG] Processing request:", req.body);

// Use debugger
debugger; // Execution pauses here

// Run with debugger
node --inspect server/index.js
```

## Code Style & Conventions

### TypeScript
- Use strict mode: `"strict": true` in tsconfig.json
- Define interfaces for component props
- Use union types instead of string literals

```typescript
// Good
type Status = "pending" | "completed" | "failed";

interface Payment {
  id: number;
  status: Status;
}

// Avoid
interface Payment {
  id: number;
  status: string; // Too generic
}
```

### React Components
- Use functional components with hooks
- Keep components small and focused
- Use "use client" only when needed
- Export default at end of file

```typescript
// Good
interface HeaderProps {
  title: string;
}

export default function Header({ title }: HeaderProps) {
  return <h1>{title}</h1>;
}

// Avoid
export default function Header(props: any) {
  return <h1>{props.title}</h1>;
}
```

### File Naming
- Components: PascalCase (`Button.tsx`)
- Utilities: camelCase (`helpers.ts`)
- Pages: lowercase with hyphens (`login.tsx`)
- Services: camelCase (`authService.ts`)

### Database Schema
- Table names: plural, lowercase (`users`, `properties`)
- Column names: snake_case (`first_name`, `created_at`)
- Timestamps: always include `created_at` and `updated_at`

## Performance Optimization

### Frontend

```typescript
// Lazy load components
const PropertyList = dynamic(
  () => import("@/components/PropertyList"),
  { loading: () => <div>Loading...</div> }
);

// Use Image component for optimization
import Image from "next/image";

// Memoize expensive computations
const memoizedValue = useMemo(() => {
  return complexCalculation(data);
}, [data]);
```

### Backend

```javascript
// Add database indexes
CREATE INDEX idx_properties_landlord ON properties(landlord_id);
CREATE INDEX idx_payments_tenant ON payments(tenant_id);

// Use pagination
router.get("/properties", async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const limit = 20;
  const offset = (page - 1) * limit;

  const properties = await db
    .select()
    .from(properties)
    .limit(limit)
    .offset(offset);

  res.json({ data: properties, page, limit });
});
```

## Common Tasks

### Adding a New Page

```typescript
// 1. Create page component
// app/tenants/page.tsx
import TenantList from "@/components/TenantList";

export default function TenantsPage() {
  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">Tenants</h1>
      <TenantList />
    </div>
  );
}

// 2. Add navigation link
// Update sidebar in app/page.tsx
```

### Adding Database Table

```typescript
// 1. Update schema
// lib/db/schema.ts
export const newTable = pgTable("new_table", {
  id: serial("id").primaryKey(),
  // ... columns
});

// 2. Create migration
// db/migrations/001_create_new_table.sql
CREATE TABLE new_table (
  id SERIAL PRIMARY KEY,
  -- ... columns
);

// 3. Update types
// lib/types.ts
export interface NewTable {
  id: number;
  // ... properties
}

// 4. Create service
// lib/services/newService.ts
export const newService = {
  getAll: async () => { /* ... */ },
  // ... methods
};
```

### Adding API Endpoint

```javascript
// 1. Create route
// server/routes/newroute.js
router.post("/", verifyAuth, async (req, res) => {
  // Implementation
});

// 2. Register in server
// server/index.js
import newRoutes from "./routes/newroute.js";
app.use("/api/newroute", newRoutes);

// 3. Create service
// lib/services/newService.ts
export const newService = {
  create: async (data) => {
    return await apiClient.post("/newroute", data);
  },
};

// 4. Use in component
import { newService } from "@/lib/services/newService";
```

## Git Workflow

```bash
# Create feature branch
git checkout -b feature/my-feature

# Make changes and commit
git add .
git commit -m "feat: Add new feature"

# Push to remote
git push origin feature/my-feature

# Create Pull Request on GitHub
# After review and approval, merge to main
```

### Commit Message Convention

```
feat: Add payment tracking feature
fix: Correct validation error message
refactor: Simplify database query
docs: Update deployment guide
test: Add unit tests for auth service
chore: Update dependencies
```

## Useful Resources

- [Next.js Documentation](https://nextjs.org/docs)
- [TypeScript Handbook](https://www.typescriptlang.org/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Drizzle ORM](https://orm.drizzle.team/)
- [Express.js Guide](https://expressjs.com/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

## Getting Help

- Check existing issues on GitHub
- Read code comments and documentation
- Ask in project discussions
- Submit issue with clear description

---

Happy coding! For questions, open an issue or contact the team.
