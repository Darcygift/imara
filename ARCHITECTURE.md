# Smart Rent - Architecture Overview

Complete architectural documentation for Smart Rent property management system.

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Smart Rent System                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────┐  HTTP/HTTPS  ┌──────────────────────┐
│   Frontend Layer    │◄────────────►│   Backend Layer      │
│  (Next.js 16)       │   REST API   │  (Express.js)        │
├─────────────────────┤              ├──────────────────────┤
│ React 19            │              │ Node.js 18+          │
│ Tailwind CSS        │              │ PostgreSQL Driver    │
│ TypeScript          │              │ JWT Auth             │
│ Axios HTTP Client   │              │ Drizzle ORM          │
└─────────────────────┘              └──────────────────────┘
        │                                    │
        │                                    │
        ▼                                    ▼
┌─────────────────────┐              ┌──────────────────────┐
│  User Experience    │              │  Data Persistence   │
├─────────────────────┤              ├──────────────────────┤
│ Dashboard           │              │ PostgreSQL 12+       │
│ Property Management │              │ Tables:              │
│ Tenant Tracking     │              │  - landlords         │
│ Payment Monitoring  │              │  - properties        │
│ Analytics           │              │  - units             │
└─────────────────────┘              │  - tenants           │
                                     │  - payments          │
                                     │  - sms_logs          │
                                     └──────────────────────┘

External Services
┌──────────────────┐   ┌──────────────────┐   ┌──────────────────┐
│   MTN MoMo       │   │   SMS Provider   │   │   Email Service  │
│   Payment API    │   │  (Configurable)  │   │  (Configurable)  │
└──────────────────┘   └──────────────────┘   └──────────────────┘
```

## Frontend Architecture

### Directory Structure
```
app/
├── layout.tsx              # Root layout with metadata
├── page.tsx                # Dashboard (main page)
├── globals.css             # Global styles & design tokens
├── login/
│   └── page.tsx           # Login page
├── register/
│   └── page.tsx           # Registration page
└── (future pages)

components/
├── DashboardHeader.tsx     # Top navigation & search
├── StatsCard.tsx           # Statistics card component
├── PaymentChart.tsx        # Payment trend chart
└── RecentPayments.tsx      # Recent transactions table

lib/
├── api.ts                  # Axios instance with interceptors
├── types.ts                # TypeScript interfaces
├── validators.ts           # Zod validation schemas
├── auth.ts                 # Auth utilities (client-side)
├── db/
│   ├── schema.ts          # Drizzle ORM schema
│   └── client.ts          # Database client
└── services/              # API service layer
    ├── auth.ts            # Authentication API calls
    ├── properties.ts      # Properties API calls
    ├── payments.ts        # Payments API calls
    └── tenants.ts         # Tenants API calls
```

### Component Hierarchy
```
Layout (app/layout.tsx)
└── Dashboard (app/page.tsx)
    ├── Sidebar Navigation
    │   └── NavItem (reusable)
    ├── Main Content
    │   ├── DashboardHeader
    │   ├── Stats Grid
    │   │   └── StatsCard (×3)
    │   ├── Financial Overview
    │   │   ├── PaymentChart
    │   │   └── Summary Cards
    │   └── RecentPayments Table
    └── Quick Actions
        └── Buttons (Add Property, Add Tenant)

Login Page (app/login/page.tsx)
└── Form
    ├── Input Fields
    └── Submit Button

Register Page (app/register/page.tsx)
└── Form
    ├── Input Fields
    └── Submit Button
```

## Backend Architecture

### Directory Structure
```
server/
├── index.js                # Main server entry point
├── middleware/
│   ├── auth.js            # JWT verification middleware
│   ├── validation.js      # Request validation middleware
│   └── errorHandler.js    # Global error handler
├── routes/                # API routes
│   ├── auth.js            # Authentication endpoints
│   ├── properties.js      # Properties endpoints
│   ├── payments.js        # Payments endpoints
│   └── tenants.js         # Tenants endpoints
├── controllers/           # Business logic (to be implemented)
│   ├── authController.js
│   ├── propertiesController.js
│   ├── paymentsController.js
│   └── tenantsController.js
└── utils/                 # Utility functions
    ├── database.js        # Database helpers
    └── validators.js      # Backend validation
```

### Request Flow
```
HTTP Request
    ↓
CORS Middleware
    ↓
Body Parser
    ↓
Route Matching
    ↓
Authentication (verifyAuth middleware)
    ↓
Validation (validateRequest middleware)
    ↓
Route Handler (Controller)
    ↓
Database Operation (Drizzle ORM)
    ↓
Response Formatting
    ↓
Error Handling (if any)
    ↓
HTTP Response (JSON)
```

## Data Flow

### Authentication Flow
```
1. User submits credentials (login/register)
   ↓
2. Frontend validates with Zod schema
   ↓
3. POST /api/auth/login or /api/auth/register
   ↓
4. Backend validates input
   ↓
5. Backend checks/creates user in database
   ↓
6. Backend hashes password (bcryptjs)
   ↓
7. Backend generates JWT token
   ↓
8. Frontend stores token in localStorage
   ↓
9. Frontend redirects to dashboard
   ↓
10. Subsequent requests include: Authorization: Bearer <token>
```

### Payment Flow
```
1. Landlord views dashboard
   ↓
2. Frontend calls propertyService.getAll()
   ↓
3. Axios adds JWT token to Authorization header
   ↓
4. GET /api/payments (or /api/payments/pending)
   ↓
5. verifyAuth middleware checks token
   ↓
6. Route handler queries database:
   SELECT * FROM payments WHERE tenant_id IN (...)
   ↓
7. Include related data (tenant, property info)
   ↓
8. Format response as JSON
   ↓
9. Frontend receives and displays in RecentPayments component
   ↓
10. User can update payment status
   ↓
11. PATCH /api/payments/:id { status: "completed" }
   ↓
12. Database updates payment record
   ↓
13. SMS reminder sent (future feature)
   ↓
14. Frontend updates UI
```

## Database Schema

### Relationships
```
Landlord (1) ──→ (many) Property
   ↓                     ↓
   │              Unit (many)
   │                     ↓
   │              Tenant (many)
   │                     ↓
   └──────────→ Payment (many)

Tenant (1) ──→ (many) Payment
Unit (1) ──→ (many) Tenant
Unit (1) ──→ (many) Payment
```

### Table Details

#### landlords
- id (PK)
- name, email (unique), phone
- address, city
- password_hash
- created_at, updated_at

#### properties
- id (PK)
- landlord_id (FK)
- name, address, city
- description
- property_type (apartment|house|commercial|land)
- number_of_units
- created_at, updated_at

#### units
- id (PK)
- property_id (FK)
- unit_number
- rental_amount
- description
- is_occupied (boolean)
- created_at, updated_at

#### tenants
- id (PK)
- unit_id (FK)
- first_name, last_name
- email, phone
- id_number
- lease_start_date, lease_end_date
- emergency_contact
- created_at, updated_at

#### payments
- id (PK)
- tenant_id (FK)
- unit_id (FK)
- amount
- due_date
- paid_date (nullable)
- status (pending|completed|failed|overdue)
- payment_method (mtn_momo|airtel_money|bank_transfer|cash)
- transaction_reference (nullable)
- notes (nullable)
- created_at, updated_at

#### sms_logs
- id (PK)
- tenant_id (FK, nullable)
- phone_number
- message
- status
- external_id (nullable)
- created_at

## API Design

### Request Format
```json
{
  "method": "POST",
  "path": "/api/auth/login",
  "headers": {
    "Content-Type": "application/json",
    "Authorization": "Bearer <token>" // For protected routes
  },
  "body": {
    "email": "landlord@example.com",
    "password": "secure_password"
  }
}
```

### Response Format
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error (development only)"
}
```

### HTTP Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Authentication & Security

### JWT Token Structure
```
Header:
{
  "alg": "HS256",
  "typ": "JWT"
}

Payload:
{
  "id": 1,
  "email": "landlord@example.com",
  "iat": 1234567890,
  "exp": 1234654290  // 7 days
}

Signature:
HMACSHA256(
  base64UrlEncode(header) + "." +
  base64UrlEncode(payload),
  secret
)
```

### Security Measures
- ✅ Passwords hashed with bcryptjs (10 rounds)
- ✅ JWT tokens for stateless authentication
- ✅ CORS configured for frontend domain
- ✅ Environment variables for secrets
- ✅ SQL injection prevention (Drizzle ORM)
- ✅ XSS protection (React sanitization)
- 🔒 TODO: Rate limiting
- 🔒 TODO: Request validation
- 🔒 TODO: Audit logging

## State Management

### Frontend State
- **Authentication**: JWT token in localStorage
- **User Data**: Loaded from `/api/auth/me`
- **Component State**: React hooks (useState, useEffect)
- **Navigation**: Next.js routing

### Backend State
- **Sessions**: Stateless JWT
- **Database**: PostgreSQL as source of truth
- **Cache**: (To be implemented) Redis

## Error Handling

### Frontend
```typescript
try {
  const data = await propertyService.getAll();
  setProperties(data);
} catch (error) {
  // Handle API errors
  setError(error.message);
}
```

### Backend
```javascript
try {
  // Route logic
} catch (error) {
  console.error(error);
  res.status(500).json({
    success: false,
    message: "Internal server error",
    error: process.env.NODE_ENV === "development" ? error.message : undefined
  });
}
```

## Scaling Considerations

### Horizontal Scaling
- Stateless backend (can run multiple instances)
- Load balancer distributes traffic
- Shared PostgreSQL database
- Optional: Redis for session management

### Vertical Scaling
- Upgrade server resources
- Database indexing optimization
- Query caching
- Connection pooling

### Performance Optimization
- Lazy load components
- Database query optimization
- Response caching
- Gzip compression
- CDN for static assets

## Deployment Architecture

### Development
```
Local Machine
├── Frontend (http://localhost:3000)
├── Backend (http://localhost:3001)
└── Database (PostgreSQL local)
```

### Production (Example)
```
CDN (CloudFlare)
    ↓
Frontend (Vercel)
├── Static assets cached
├── API calls to backend
└── Environment variables

Backend (Railway/AWS)
├── Multiple instances
├── Load balancer
├── Auto-scaling
└── Health monitoring

Database (AWS RDS/Neon)
├── Managed PostgreSQL
├── Automated backups
├── High availability
└── Read replicas
```

## Integration Points

### MTN MoMo Integration
```
Payment Request → MTN API → Payment Processing → Callback → Database Update
```

### SMS Service Integration
```
Payment Due → SMS Service → Provider API → Delivery → SMS Log
```

### Email Service (Future)
```
User Action → Email Service → Provider → Delivery
```

## Technology Choices

| Layer | Technology | Why |
|-------|-----------|-----|
| Frontend | Next.js 16 | Server/Client components, fast builds, Vercel hosting |
| State | React Hooks | Built-in, simple, performant |
| Styling | Tailwind CSS | Utility-first, responsive, design tokens |
| Type Safety | TypeScript | Catches errors early, better DX |
| HTTP Client | Axios | Great interceptors, error handling |
| Validation | Zod | Runtime validation, TypeScript inference |
| Backend | Express.js | Simple, lightweight, great ecosystem |
| Database | PostgreSQL | Reliable, ACID compliant, JSON support |
| ORM | Drizzle | Type-safe, lightweight, migrations |
| Auth | JWT + bcryptjs | Stateless, scalable, secure |
| Deployment | Vercel + Railway | Easy deploys, good free tiers, great DX |

## Future Enhancements

1. **Real-time Updates**: WebSocket for live payment notifications
2. **Analytics**: Advanced dashboards with Recharts
3. **Mobile App**: React Native version
4. **AI Features**: Predictive analytics for late payments
5. **Multi-tenant**: Support multiple organizations
6. **Advanced Auth**: OAuth2, 2FA, SSO
7. **API Documentation**: Swagger/OpenAPI
8. **Testing**: Unit tests, E2E tests, load testing
9. **Monitoring**: Sentry, DataDog, LogRocket
10. **Rate Limiting**: Implement Redis-based rate limiting

---

This architecture provides a solid foundation for scaling Smart Rent to thousands of landlords while maintaining code quality and performance.
