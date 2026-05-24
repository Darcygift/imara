# Imara.rw — Rwanda Property Platform
### Vue.js + Node.js/Express + MongoDB

---

## 🚀 Quick Start (Local)

### 1. Prerequisites
- Node.js 18+ → https://nodejs.org
- MongoDB Atlas account (free) → https://mongodb.com/atlas

### 2. Clone & Install
```bash
git clone https://github.com/yourname/imara-rw.git
cd imara-rw
npm install
```

### 3. Configure
```bash
cp .env.example .env
# Edit .env and set your MONGODB_URI from Atlas
```

### 4. Run
```bash
npm start
# → http://localhost:3000
```

Demo login: `demo@imara.rw` / `Demo1234!`

---

## 🌐 Deploy to Render (Free, Recommended)

1. Push to GitHub
2. Go to https://render.com → New Web Service
3. Connect your repo
4. Set:
   - Build command: `npm install`
   - Start command: `node server.js`
5. Add environment variables:
   - `MONGODB_URI` = your Atlas connection string
   - `JWT_SECRET`  = any long random string
6. Deploy → your site is live in ~2 minutes

## ☁️ Deploy to Railway
```bash
npm install -g railway
railway login
railway init
railway up
# Set MONGODB_URI and JWT_SECRET in dashboard
```

## ▲ Deploy to Vercel
Vercel is best for Next.js; for this Express app use Render or Railway instead.

---

## 📁 File Structure
```
imara-rw/
  server.js          ← Express API + static file server
  models.js          ← MongoDB/Mongoose models
  public/
    index.html       ← Vue.js SPA (all pages in one file)
  .env               ← your secrets (never commit!)
  .env.example       ← template
  package.json
  README.md
```

---

## 🔌 API Reference

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/register | Create account |
| POST | /api/auth/login | Sign in → returns JWT |
| GET  | /api/auth/me | Get current user (auth required) |

### Listings
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | /api/listings | All listings (filter by type, district, price, beds) |
| GET  | /api/listings/:id | Single listing (increments view count) |
| POST | /api/listings | Create listing (auth) |
| PUT  | /api/listings/:id | Update listing (auth, owner only) |
| DELETE | /api/listings/:id | Delete listing (auth, owner only) |
| GET  | /api/my/listings | My listings (auth) |

### Bookings
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/bookings | Create booking (auth) |
| GET  | /api/my/bookings | My bookings as tenant (auth) |
| GET  | /api/landlord/bookings | Booking requests as landlord (auth) |
| PATCH | /api/bookings/:id/status | Update booking status (auth) |

### Messages
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | /api/messages | All conversations (auth) |
| GET  | /api/messages/:userId | Chat with a user (auth) |
| POST | /api/messages | Send a message (auth) |

---

## 🏗️ Tech Stack
- **Frontend**: Vue.js 3 (CDN, no build step required), Leaflet.js GPS maps
- **Backend**: Node.js + Express.js
- **Database**: MongoDB via Mongoose
- **Auth**: JWT tokens (7-day expiry)
- **Payments**: MTN MoMo escrow (webhook at /api/momo/callback)
- **Maps**: OpenStreetMap + Leaflet (free, no API key)
- **Hosting**: Any Node.js host (Render, Railway, DigitalOcean, VPS)

---

## 🗺️ Features
- ✅ GPS-based property map (Leaflet + real geolocation)
- ✅ Full auth system (register, login, JWT sessions)
- ✅ Role-based access (Tenant / Landlord / Agent / Admin)
- ✅ Property listings with MongoDB (CRUD)
- ✅ Advanced search & filters
- ✅ Booking system with MTN MoMo escrow
- ✅ Real-time messaging between users
- ✅ Landlord dashboard with booking management
- ✅ UPI land registry validation fields
- ✅ Demo data auto-seeded on first run
- ✅ Mobile responsive

---

## 🔐 Security Notes (Production)
1. Set a strong `JWT_SECRET` (32+ random chars)
2. Use MongoDB Atlas with IP allowlist (not 0.0.0.0/0)
3. Enable HTTPS on your host (Render/Railway do this automatically)
4. Add rate limiting to auth routes (install express-rate-limit)
5. Validate all inputs server-side (already done in models)
