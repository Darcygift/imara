# Smart Rent - Deployment Guide

This guide covers deploying Smart Rent to production environments.

## Architecture Overview

Smart Rent consists of two main services:
- **Frontend**: Next.js app (React 19, Tailwind CSS)
- **Backend**: Express.js API with PostgreSQL

Both can be deployed separately or together depending on your infrastructure.

## Prerequisites

- Node.js 18+
- PostgreSQL 12+
- Git
- Package manager (npm, yarn, pnpm, or bun)

## Environment Variables

### Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api
```

### Backend (.env.local)
```env
NODE_ENV=production
PORT=3001
DATABASE_URL=postgresql://user:password@host:5432/smart_rent_db
JWT_SECRET=your_secure_random_string_here
MTN_MOMO_API_KEY=your_api_key
SMS_API_KEY=your_sms_provider_key
FRONTEND_URL=https://yourdomain.com
```

## Option 1: Deploy to Vercel (Recommended for Frontend)

### Frontend Deployment

1. **Push code to GitHub**
```bash
git add .
git commit -m "Deploy to Vercel"
git push origin main
```

2. **Connect to Vercel**
   - Go to https://vercel.com
   - Click "New Project"
   - Import your GitHub repository
   - Set environment variables in project settings

3. **Configure**
   - Root directory: `/`
   - Build command: `npm run build`
   - Output directory: `.next`

4. **Add environment variables in Vercel Dashboard**
   - Project Settings → Environment Variables
   - Add `NEXT_PUBLIC_API_URL` pointing to your backend

Frontend is now live at `yourdomain.vercel.app`

## Option 2: Deploy Backend to Railway

Railway is excellent for Node.js + PostgreSQL deployments.

### Setup

1. **Create Railway Account**
   - Go to https://railway.app
   - Sign up with GitHub

2. **Create New Project**
   - Click "New Project"
   - Select "GitHub Repo"
   - Choose your Smart Rent repository

3. **Add PostgreSQL Plugin**
   - Click "Add" → "Provision PostgreSQL"
   - Railway automatically creates database

4. **Add Environment Variables**
   - Railway Console → Variables
   - Add all variables from `.env.local`

5. **Configure Start Command**
   - Railway Settings → Run Command
   - Set to: `npm run backend:dev` or `node server/index.js`

### Deploy

```bash
# Push to main branch
git push origin main

# Railway auto-deploys from GitHub
# Check deployment in Railway dashboard
```

Backend is now live at your Railway URL.

## Option 3: Deploy to AWS EC2

### Prerequisites
- AWS account
- EC2 instance (t3.micro eligible for free tier)
- Ubuntu 22.04 LTS

### Setup

1. **Connect to EC2 Instance**
```bash
ssh -i your-key.pem ubuntu@your-instance-ip
```

2. **Install Node.js & PostgreSQL**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

3. **Setup Database**
```bash
sudo -u postgres createdb smart_rent_db
sudo -u postgres createuser smartrent_user
sudo -u postgres psql -c "ALTER USER smartrent_user WITH PASSWORD 'secure_password';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE smart_rent_db TO smartrent_user;"
```

4. **Clone Repository**
```bash
cd /var/www
git clone https://github.com/yourusername/imara.git
cd imara
npm install
```

5. **Configure Environment**
```bash
cp .env.example .env.local
nano .env.local  # Edit with your values
```

6. **Build Application**
```bash
npm run build
```

7. **Install PM2 (Process Manager)**
```bash
sudo npm install -g pm2
pm2 start server/index.js --name "smart-rent-api"
pm2 save
pm2 startup
```

8. **Setup Nginx Reverse Proxy**
```bash
sudo apt install -y nginx

# Create Nginx config
sudo nano /etc/nginx/sites-available/smart-rent
```

Add configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Enable and test:
```bash
sudo ln -s /etc/nginx/sites-available/smart-rent /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

9. **Setup SSL with Let's Encrypt**
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

## Option 4: Deploy to Heroku

### Setup

1. **Install Heroku CLI**
```bash
curl https://cli.heroku.com/install.sh | sh
```

2. **Login to Heroku**
```bash
heroku login
```

3. **Create Heroku App**
```bash
heroku create your-app-name
```

4. **Add PostgreSQL Plugin**
```bash
heroku addons:create heroku-postgresql:mini
```

5. **Set Environment Variables**
```bash
heroku config:set JWT_SECRET=your_secret
heroku config:set MTN_MOMO_API_KEY=your_key
heroku config:set SMS_API_KEY=your_key
```

6. **Deploy**
```bash
git push heroku main
```

7. **Check Logs**
```bash
heroku logs --tail
```

## Option 5: Docker Deployment

### Create Dockerfile

```dockerfile
FROM node:18-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install --production

# Copy application
COPY . .

# Build Next.js
RUN npm run build

# Expose port
EXPOSE 3000 3001

# Start both services
CMD ["sh", "-c", "npm run dev & npm run backend:dev"]
```

### Build and Run

```bash
# Build image
docker build -t smart-rent .

# Run container
docker run -p 3000:3000 -p 3001:3001 --env-file .env.local smart-rent

# Push to Docker Hub
docker tag smart-rent yourusername/smart-rent
docker push yourusername/smart-rent
```

## Database Migrations

### Create Migration Script

```bash
# Create migrations directory
mkdir -p db/migrations

# Run migrations
node db/migrate.js
```

### Backup Database

```bash
# Backup
pg_dump -U smartrent_user smart_rent_db > backup.sql

# Restore
psql -U smartrent_user smart_rent_db < backup.sql
```

## Monitoring & Logging

### PM2 Monitoring
```bash
pm2 monit
pm2 logs smart-rent-api
```

### Nginx Logs
```bash
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

### Application Monitoring
- Set up monitoring with tools like:
  - New Relic
  - Datadog
  - Sentry (error tracking)
  - LogRocket (frontend monitoring)

## SSL/TLS Certificate

### Let's Encrypt (Free)
```bash
sudo certbot renew --quiet
```

Add to crontab:
```bash
sudo crontab -e
# Add line: 0 12 * * * /usr/bin/certbot renew --quiet
```

## Performance Optimization

1. **Enable Gzip Compression**
```nginx
gzip on;
gzip_types text/plain text/css text/javascript application/json;
```

2. **Setup CDN**
   - CloudFlare: https://cloudflare.com
   - AWS CloudFront: https://aws.amazon.com/cloudfront

3. **Database Indexing**
   - Add indexes to frequently queried columns
   - Monitor query performance

4. **Caching**
   - Implement Redis for session storage
   - Cache API responses

## Troubleshooting

### API Connection Issues
```bash
# Check if backend is running
curl http://localhost:3001/health

# Check logs
pm2 logs smart-rent-api
```

### Database Connection Issues
```bash
# Test database connection
psql -h localhost -U smartrent_user -d smart_rent_db -c "SELECT 1;"

# Check PostgreSQL status
sudo systemctl status postgresql
```

### Deployment Failures
1. Check environment variables are set correctly
2. Verify database is accessible
3. Check logs in deployment dashboard
4. Ensure all dependencies are installed

## Scaling

### Horizontal Scaling
- Use load balancer (AWS ELB, Nginx)
- Deploy multiple backend instances
- Share database connection pool

### Vertical Scaling
- Upgrade server resources (CPU, RAM)
- Optimize database queries
- Implement caching layer

## Maintenance

### Regular Tasks
- Review logs weekly
- Monitor database size
- Update dependencies monthly
- Backup database weekly

### Security Updates
```bash
# Update all packages
npm update

# Check for vulnerabilities
npm audit

# Fix vulnerabilities
npm audit fix
```

## Support

For deployment issues:
- Check logs in your deployment platform
- Review environment variables
- Verify database connectivity
- Check firewall/security group rules

---

Need help? Open an issue on GitHub or contact support@smartrent.app
