# Imara.rw — Professional Rwanda Property Platform

A modern, enterprise-grade property marketplace designed specifically for Rwanda. Tenants can search verified properties using precise GPS map overlays, schedule viewing appointments protected by MTN MoMo escrow deposits, and chat directly with landlords/agents.

---

## Key Features

- 🗺️ **Interactive Geolocation Map**: Locate properties dynamically using price-pin indicators on an OpenStreetMap map via Leaflet.js.
- 💳 **MTN MoMo Escrow Protection**: Viewing requests commit a 10% holding deposit. Funds are held safely in escrow and automatically refunded on cancellations.
- 💬 **Direct Live Chat**: Communicate instantly in-app between tenants and landlords/agents with threads and unread markers.
- 🛡️ **UPI land registry verification**: Validate property details using official UPI Land Numbers.
- 📊 **Unified Role-Based Dashboard**: Custom management grids for tenants (track bookings and chats) and landlords (create listings, approve booking requests, track views).

---

## Tech Stack

- **Frontend**: Next.js 16 (App Router) + React 19 + TypeScript
- **Styling**: Tailwind CSS 4 (with glassmorphism and modern dark mode typography)
- **Database**: MySQL 8.0
- **ORM**: Drizzle ORM (type-safe query client)
- **Authentication**: JWT-based session security with bcryptjs password hashing

---

## Getting Started

### Prerequisites

- Node.js 18+
- Docker (for spinning up the local MySQL container)

### Step 1: Install Dependencies
```bash
npm install
```

### Step 2: Spin Up MySQL Database
We provide a configured `docker-compose.yml` to launch a MySQL container:
```bash
docker-compose up -d
```

### Step 3: Configure Environment
Create a `.env.local` file in the root directory:
```env
# Database URI
DATABASE_URL=mysql://imara_user:secure_password_change_me@localhost:3306/imara_db

# Security Secrets
JWT_SECRET=imara_rw_secret_session_key_2026_change_in_production
```

### Step 4: Run Development Server
```bash
npm run dev
# The platform runs on http://localhost:3000
```
- On first load, the database tables are **automatically created** and populated with **Rwanda property listings** (Kiyovu, Nyarutarama, Rubavu, etc.)!
- **Demo Landlord credentials**: `demo@imara.rw` / `Demo1234!`

---

## Project Structure

```
imara-main/
├── app/                          # Next.js App Router Pages & APIs
│   ├── api/                      # Backend endpoints (auth, listings, bookings, messages)
│   ├── auth/                     # Login & Sign-up pages
│   ├── dashboard/                # Unified landlord & tenant workspace
│   ├── listings/                 # Search explorer & detail visualizer
│   ├── map/                      # Interactive GPS Leaflet page
│   ├── layout.tsx                # Root layout
│   └── globals.css              # Custom Tailwind utility configurations
├── components/                   # Reusable UI widgets
│   ├── Navbar.tsx                # Client header with GPS indicators
│   ├── Footer.tsx                # Platform footer with certifications
│   └── MapComponent.tsx          # Dynamic Leaflet component
├── lib/                          # Utility functions
│   ├── db/
│   │   ├── client.ts             # MySQL pool client connection
│   │   ├── schema.ts             # Drizzle database structures
│   │   └── init.ts               # Auto-migration & demo data seeder
│   ├── api.ts                    # Intercepted Axios handler
│   └── auth.ts                   # Token helpers
├── docker-compose.yml            # MySQL service container profile
└── package.json                  # Script runners and dependencies
```
