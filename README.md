# Smart Rent - Professional Property Management System

A comprehensive, enterprise-grade property management platform designed for landlords to efficiently manage properties, tenants, payments, and automate rent collection with integrated payment processing (MTN MoMo, SMS notifications).

## Features

### Core Features
- **Dashboard Analytics**: Real-time overview of properties, tenants, payments, and occupancy rates
- **Property Management**: Add, edit, and manage multiple properties with unit details
- **Tenant Management**: Complete tenant profiles with lease information and contact details
- **Payment Tracking**: Monitor rent payments with status tracking (pending, completed, overdue)
- **Financial Reports**: Collection analytics and outstanding amount summaries
- **SMS Notifications**: Automated payment reminders via SMS integration
- **Mobile Payment Integration**: MTN MoMo and other mobile money integrations

### Technical Features
- **Professional UI**: Modern, responsive dashboard with Tailwind CSS
- **Full-Stack TypeScript**: Type-safe frontend and backend
- **RESTful API**: Clean, documented API endpoints
- **Database**: PostgreSQL with Drizzle ORM for type-safe queries
- **Authentication**: JWT-based auth with secure password hashing
- **Real-time Notifications**: SMS and email alert system
- **Scalable Architecture**: Ready for enterprise deployment

## Tech Stack

### Frontend
- **Framework**: Next.js 16 (React 19)
- **Styling**: Tailwind CSS 4 with custom design tokens
- **HTTP Client**: Axios with interceptors
- **Type Safety**: TypeScript
- **Deployment**: Vercel

### Backend
- **Runtime**: Node.js
- **Framework**: Express.js
- **Database**: PostgreSQL
- **ORM**: Drizzle ORM
- **Authentication**: JWT + bcryptjs
- **API Documentation**: RESTful API with request validation

### External Services
- **SMS Provider**: Configurable (African Mobile Operators)
- **Payment Gateway**: MTN MoMo API
- **Database Hosting**: Cloud-based PostgreSQL (Neon, Railway, or similar)

## Project Structure

```
smart-rent/
├── app/                          # Next.js App Router
│   ├── page.tsx                 # Main dashboard
│   ├── layout.tsx               # Root layout
│   └── globals.css              # Global styles
├── components/                   # Reusable React components
│   ├── DashboardHeader.tsx
│   ├── StatsCard.tsx
│   ├── PaymentChart.tsx
│   └── RecentPayments.tsx
├── lib/
│   ├── api.ts                   # Axios client with interceptors
│   ├── types.ts                 # TypeScript interfaces
│   ├── db/
│   │   └── schema.ts            # Drizzle ORM schema
│   └── services/                # API service layer
│       ├── auth.ts
│       ├── properties.ts
│       ├── payments.ts
│       └── tenants.ts
├── server/                       # Express.js backend
│   └── index.js                 # Main server file
├── public/                       # Static assets
├── .env.local                   # Environment variables
└── tailwind.config.ts           # Tailwind configuration
```

## Getting Started

### Prerequisites
- Node.js 18+ and npm/yarn/pnpm
- PostgreSQL 12+
- Git

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/Darcygift/imara.git
cd imara
npm install
```

2. **Set up environment variables**
Create a `.env.local` file:
```env
# API Configuration
NEXT_PUBLIC_API_URL=http://localhost:3001/api

# Database
DATABASE_URL=postgresql://user:password@localhost:5432/smart_rent_db

# Authentication
JWT_SECRET=your_jwt_secret_key_here_change_in_production

# External Services
MTN_MOMO_API_KEY=your_mtn_momo_key_here
SMS_API_KEY=your_sms_api_key_here

# Environment
NODE_ENV=development
```

3. **Set up the database**
```bash
# Create PostgreSQL database
createdb smart_rent_db

# Run migrations (when implemented)
npm run db:migrate
```

4. **Start development servers**

Frontend:
```bash
npm run dev
# Frontend runs on http://localhost:3000
```

Backend (in another terminal):
```bash
npm run backend:dev
# Backend runs on http://localhost:3001
```

## Development Workflow

### Frontend Development
- Components are modular and located in `/components`
- Use TypeScript for type safety
- Tailwind CSS for styling with design tokens
- API calls through `/lib/services` layer

### Backend Development
- Express routes in `/server` directory
- Database queries using Drizzle ORM
- Type safety with TypeScript types from `/lib/types`
- RESTful API conventions

### Adding New Features
1. Create types in `/lib/types.ts`
2. Create API service in `/lib/services/`
3. Build frontend components in `/components/`
4. Implement backend routes in `/server/`
5. Connect frontend to backend via API service

## API Endpoints

### Authentication
```
POST   /api/auth/register      - Register new landlord
POST   /api/auth/login         - Login
GET    /api/auth/me            - Get current user
PUT    /api/auth/profile       - Update profile
```

### Properties
```
GET    /api/properties         - List all properties
POST   /api/properties         - Create property
GET    /api/properties/:id     - Get property details
PUT    /api/properties/:id     - Update property
DELETE /api/properties/:id     - Delete property
GET    /api/properties/:id/units - Get units for property
POST   /api/properties/:id/units - Create unit
```

### Tenants
```
GET    /api/tenants            - List all tenants
POST   /api/tenants            - Create tenant
GET    /api/tenants/:id        - Get tenant details
PUT    /api/tenants/:id        - Update tenant
DELETE /api/tenants/:id        - Delete tenant
```

### Payments
```
GET    /api/payments           - List all payments
POST   /api/payments           - Create payment
GET    /api/payments/:id       - Get payment details
PATCH  /api/payments/:id       - Update payment status
POST   /api/payments/:id/record - Record payment
POST   /api/payments/pending   - Get pending payments
POST   /api/payments/:id/send-reminder - Send SMS reminder
```

## Database Schema

### Tables
- **landlords**: User accounts for landlords
- **properties**: Properties managed by landlords
- **units**: Individual rental units within properties
- **tenants**: Tenant information and lease details
- **payments**: Payment records and transaction history
- **sms_logs**: SMS delivery logs for audit trail

See `/lib/db/schema.ts` for detailed schema definition.

## Deployment

### Deploy Frontend to Vercel
```bash
npm run build
# Push to GitHub, Vercel auto-deploys
```

### Deploy Backend
Options:
- **Railway**: `railway up` after configuring Railway config
- **Render**: Connect GitHub repo and auto-deploy
- **Heroku**: `git push heroku main`
- **AWS EC2**: Deploy Node.js app with PM2

## Security Considerations

- ✅ JWT tokens stored securely
- ✅ Passwords hashed with bcryptjs
- ✅ Environment variables for sensitive data
- ✅ CORS properly configured
- ✅ SQL injection prevention via Drizzle ORM
- ✅ XSS protection with React sanitization
- 🔒 TODO: Add rate limiting
- 🔒 TODO: Implement request validation with Zod
- 🔒 TODO: Add audit logging

## Contributing

1. Create feature branch: `git checkout -b feature/your-feature`
2. Make changes and commit: `git commit -am 'Add your feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Open Pull Request

## Roadmap

### Phase 1: MVP (Current)
- ✅ Dashboard UI
- ✅ Database schema
- 🔄 API routes implementation
- 🔄 Authentication system

### Phase 2: Core Features
- [ ] Property management CRUD
- [ ] Tenant management
- [ ] Payment tracking
- [ ] Email notifications

### Phase 3: Integrations
- [ ] MTN MoMo payment integration
- [ ] SMS notifications
- [ ] Payment gateway integration
- [ ] Reporting & exports

### Phase 4: Enterprise
- [ ] Advanced analytics
- [ ] Multi-user support
- [ ] Mobile app (React Native)
- [ ] AI-powered insights

## Troubleshooting

### Database Connection Error
```
Solution: Check DATABASE_URL is correct and PostgreSQL is running
psql postgres -h localhost -U postgres
```

### API Connection Error
```
Solution: Ensure backend is running on port 3001
npm run backend:dev
```

### Styling Issues
```
Solution: Rebuild Tailwind CSS
npm run dev
```

## Support & Contact

For issues, questions, or feature requests:
- Open an issue on GitHub
- Contact: support@smartrent.app
- Documentation: https://docs.smartrent.app

## License

MIT License - See LICENSE file for details

## Acknowledgments

Built with modern web technologies for professional property management at scale.

---

**Smart Rent** - Making Property Management Simple, Secure, and Scalable
