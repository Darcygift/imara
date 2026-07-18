# Smart Rent - Quick Start Guide

Get Smart Rent up and running in 5 minutes.

## ⚡ 30-Second Setup

```bash
# 1. Clone and install
git clone https://github.com/Darcygift/imara.git
cd imara
npm install

# 2. Start development
npm run dev              # Frontend: http://localhost:3000
npm run backend:dev     # Backend: http://localhost:3001
```

**Done!** Visit http://localhost:3000

## 📋 What You Get

### ✅ Frontend (React 19 + Next.js 16)
- Professional dashboard with real-time analytics
- Property management interface
- Tenant tracking system
- Payment monitoring dashboard
- Login & registration pages
- Responsive design with Tailwind CSS

### ✅ Backend (Express.js + PostgreSQL)
- RESTful API with authentication
- Database schema for properties, tenants, payments
- JWT-based security
- Request validation with Zod
- API route stubs for integration

### ✅ Database
- PostgreSQL schema with Drizzle ORM
- Tables for: landlords, properties, units, tenants, payments, SMS logs
- Type-safe queries with TypeScript

## 🗂️ Project Structure

```
imara/
├── app/                 # Next.js pages
├── components/          # React components
├── lib/
│   ├── services/       # API clients
│   ├── db/             # Database config
│   └── types.ts        # TypeScript types
├── server/
│   └── routes/         # Express API routes
├── DEVELOPMENT.md      # Development guide
├── DEPLOYMENT.md       # Deployment guide
└── README.md          # Full documentation
```

## 🚀 Next Steps

### 1. Customize Configuration

Edit `.env.local`:
```env
NEXT_PUBLIC_API_URL=http://localhost:3001/api
DATABASE_URL=postgresql://user:password@localhost:5432/smart_rent_db
JWT_SECRET=your_secret_key_here
```

### 2. Setup Database (Optional)

```bash
# Create PostgreSQL database
createdb smart_rent_db

# Connect to database in your app
# Run migrations (when implemented)
```

### 3. Implement Backend Routes

Each route in `/server/routes` has TODO comments:
1. Add authentication verification
2. Add database queries
3. Add business logic
4. Return proper responses

Example:
```javascript
// server/routes/properties.js
router.post("/", verifyAuth, async (req, res) => {
  // TODO: Validate input with Zod
  // TODO: Create property in database
  // TODO: Return created property
});
```

### 4. Connect API Services

Frontend services in `/lib/services` are ready to use:
```typescript
import { propertyService } from "@/lib/services/properties";

const properties = await propertyService.getAll();
```

### 5. Deploy

Follow [DEPLOYMENT.md](./DEPLOYMENT.md) for:
- Vercel (frontend)
- Railway (backend)
- AWS, Heroku, Docker options

## 📚 Key Features Implemented

- ✅ Professional dashboard UI with charts
- ✅ Navigation system
- ✅ Authentication pages (login/register)
- ✅ Type-safe TypeScript setup
- ✅ Database schema designed
- ✅ API route stubs with comments
- ✅ Tailwind CSS styling system
- ✅ Development environment configured

## 🛠️ Development Commands

```bash
# Frontend
npm run dev              # Start dev server
npm run build           # Production build
npm start               # Run production build

# Backend
npm run backend:dev     # Start backend
node server/index.js    # Run backend directly

# Database
npm run db:migrate      # (To be implemented)
npm run db:seed         # (To be implemented)
```

## 🔍 API Endpoints

### Authentication
```
POST   /api/auth/register      - Register new landlord
POST   /api/auth/login         - Login
GET    /api/auth/me            - Get current user
PUT    /api/auth/profile       - Update profile
```

### Properties
```
GET    /api/properties         - List properties
POST   /api/properties         - Create property
GET    /api/properties/:id     - Get property
PUT    /api/properties/:id     - Update property
DELETE /api/properties/:id     - Delete property
```

### Payments
```
GET    /api/payments           - List payments
POST   /api/payments           - Create payment
PATCH  /api/payments/:id       - Update payment status
POST   /api/payments/:id/record - Record payment
POST   /api/payments/pending   - Get pending payments
```

See [README.md](./README.md) for full API documentation.

## 🎨 Design System

Pre-built CSS classes for consistency:

```html
<!-- Cards -->
<div class="card">Content</div>

<!-- Buttons -->
<button class="btn-primary">Primary</button>
<button class="btn-secondary">Secondary</button>

<!-- Inputs -->
<input class="input-field" type="text" />

<!-- Badges -->
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-pending">Pending</span>
```

## 📖 Documentation

- [README.md](./README.md) - Full project documentation
- [DEVELOPMENT.md](./DEVELOPMENT.md) - Developer guide
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment instructions
- [Next.js Docs](https://nextjs.org/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Drizzle ORM](https://orm.drizzle.team/)

## 🐛 Troubleshooting

### Dev server won't start
```bash
# Clear cache and reinstall
rm -rf .next node_modules package-lock.json
npm install
npm run dev
```

### Port already in use
```bash
# Use different port
npm run dev -- -p 3002      # Frontend on 3002
node server/index.js PORT=3002  # Backend on 3002
```

### TypeScript errors
```bash
# Rebuild TypeScript
npm run build

# Check for type errors
npx tsc --noEmit
```

## 📞 Getting Help

1. Check [DEVELOPMENT.md](./DEVELOPMENT.md) for development patterns
2. Review existing components in `/components`
3. Check API service examples in `/lib/services`
4. Open an issue on GitHub
5. Read code comments - they have helpful TODOs!

## 🎯 Common Tasks

### Add a new page
1. Create file in `/app/mypage/page.tsx`
2. Import components
3. Export default component

### Add API endpoint
1. Create route in `/server/routes`
2. Register in `server/index.js`
3. Create service in `/lib/services`

### Style a component
1. Use Tailwind classes
2. Use design tokens from `/lib/db/schema.ts`
3. Reference `/app/globals.css` for custom classes

### Fetch data
1. Use service from `/lib/services`
2. Use `useEffect` or Server Components
3. Handle loading and errors

## 💡 Tips & Tricks

- Use `"use client"` only when needed (state, events, hooks)
- Keep components small and focused
- Use TypeScript interfaces for props
- Follow Tailwind class ordering: layout → spacing → sizing → colors → effects
- Test API endpoints with curl or Postman
- Check browser console for client-side errors
- Check terminal for server-side errors

## 🚢 Ready to Deploy?

1. **Frontend**: Push to GitHub → Vercel auto-deploys
2. **Backend**: Deploy to Railway, Heroku, or AWS
3. **Database**: Use managed PostgreSQL (Neon, Railway, Supabase)

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed instructions.

## 📋 Checklist for Production

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] API endpoints implemented
- [ ] Authentication working
- [ ] Error handling tested
- [ ] Security headers added
- [ ] Rate limiting implemented
- [ ] Logging configured
- [ ] Backups scheduled
- [ ] Monitoring setup

## 🎉 You're Ready!

Start building Smart Rent's core features:
1. Complete backend API routes
2. Add database operations
3. Implement SMS notifications
4. Add MTN MoMo integration
5. Build advanced analytics

Happy coding! 🚀

---

**Smart Rent** - Making Property Management Simple, Secure, and Scalable
