# Production Setup Guide

This guide covers the essential configuration needed to deploy Challenge Picker to production.

## 🔐 Environment Variables

### Required Variables

Add these to your `backend/.env` file:

```env
# Application
APP_ENV=prod
APP_SECRET=your-secret-key-here-min-32-chars

# Database
DATABASE_URL=mysql://user:password@host:3306/challenge_picker_db?serverVersion=8.0

# Frontend URL (for email links, OAuth callbacks, etc.)
FRONTEND_URL=https://yourdomain.com

# Email Configuration (for password reset, email verification, notifications)
# AWS SES (Recommended - no SMTP server needed)
MAILER_DSN=ses://ACCESS_KEY:SECRET_KEY@default?region=us-east-1
# Or SMTP:
# MAILER_DSN=smtp://user:password@smtp.example.com:587
# Or SendGrid:
# MAILER_DSN=sendgrid://KEY@default
# Or Mailgun:
# MAILER_DSN=mailgun://KEY:DOMAIN@default

# JWT Authentication
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your-passphrase-here

# OAuth (Optional but recommended)
DISCORD_CLIENT_ID=your-discord-client-id
DISCORD_CLIENT_SECRET=your-discord-client-secret
TWITCH_CLIENT_ID=your-twitch-client-id
TWITCH_CLIENT_SECRET=your-twitch-client-secret

# Stripe (for shop functionality)
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...
```

### Generating Secrets

```bash
# Generate APP_SECRET (32+ characters)
php -r "echo bin2hex(random_bytes(32));"

# Generate JWT keys (if not already generated)
docker-compose exec php php bin/console lexik:jwt:generate-keypair
```

## 🚀 Deployment Steps

### 1. Backend Setup

```bash
# Clone repository
git clone <your-repo>
cd challenge-picker

# Create .env file
cp backend/.env.dist backend/.env
# Edit backend/.env with production values

# Generate JWT keys
make jwt

# Start services
make start

# Run migrations
make migrate

# Load fixtures (optional, for initial data)
make fixtures
```

### 2. Frontend Setup

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Start production server
npm run start
# Or use PM2:
# pm2 start npm --name "challenge-picker" -- start
```

### 3. Nginx Configuration

Update `docker/nginx/default.conf` for production:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers (already configured)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # CORS Headers (update with your frontend domain)
    add_header Access-Control-Allow-Origin "https://yourdomain.com" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With" always;
    
    # ... rest of configuration
}
```

### 4. SSL Certificates

Use Let's Encrypt with Certbot:

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal (add to crontab)
0 0 * * * certbot renew --quiet
```

## 🔒 Security Checklist

- [ ] Change all default passwords
- [ ] Use strong `APP_SECRET` (32+ characters)
- [ ] Set secure JWT passphrase
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure CORS properly (not `*` in production)
- [ ] Set up rate limiting (already configured)
- [ ] Configure email service (for password reset)
- [ ] Set up database backups
- [ ] Enable firewall (only allow 80, 443, SSH)
- [ ] Keep dependencies updated
- [ ] Set `APP_ENV=prod` and `APP_DEBUG=0`

## 📧 Email Configuration

### Option 1: AWS SES (Recommended - No SMTP Server Needed)

**Already installed!** Just configure your AWS credentials:

1. Create AWS SES account: https://aws.amazon.com/ses/
2. Verify your sending domain or email address
3. Create IAM user with SES permissions
4. Get Access Key ID and Secret Access Key

```env
MAILER_DSN=ses://ACCESS_KEY_ID:SECRET_ACCESS_KEY@default?region=us-east-1
```

**Note:** Replace `us-east-1` with your AWS region (e.g., `eu-west-1`, `ap-southeast-1`)

**AWS SES Benefits:**
- No SMTP server needed
- Highly reliable (99.9% uptime SLA)
- Cost-effective ($0.10 per 1,000 emails)
- Built-in bounce/complaint handling
- Scales automatically

### Option 2: SMTP (Gmail, Outlook, etc.)

```env
MAILER_DSN=smtp://username:password@smtp.gmail.com:587
```

### Option 3: SendGrid

```bash
composer require symfony/sendgrid-mailer
```

```env
MAILER_DSN=sendgrid://API_KEY@default
```

### Option 4: Mailgun

```bash
composer require symfony/mailgun-mailer
```

```env
MAILER_DSN=mailgun://API_KEY:DOMAIN@default
```

## 🗄️ Database Backups

Set up automated backups:

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump -u root -prootpassword challenge_picker_db > /backups/challenge_picker_$DATE.sql

# Keep last 30 days
find /backups -name "challenge_picker_*.sql" -mtime +30 -delete
```

## 📊 Monitoring

### Recommended Tools

- **Uptime Monitoring**: UptimeRobot, Pingdom
- **Error Tracking**: Sentry, Rollbar
- **Logs**: Papertrail, Loggly
- **Performance**: New Relic, Datadog

### Application Logs

```bash
# View Symfony logs
docker-compose exec php tail -f var/log/prod.log

# View Nginx logs
docker-compose exec nginx tail -f /var/log/nginx/error.log
```

## 🚨 Rate Limiting

Rate limiting is already configured:
- **Login**: 5 attempts per 15 minutes
- **Password Reset**: 3 attempts per hour
- **API**: 100 requests per minute

Adjust in `backend/config/packages/rate_limiter.yaml` if needed.

## ✅ Pre-Launch Checklist

- [ ] All environment variables configured
- [ ] SSL certificate installed and working
- [ ] Email service configured and tested
- [ ] Password reset flow tested
- [ ] Database backups configured
- [ ] Rate limiting tested
- [ ] Security headers verified
- [ ] CORS configured correctly
- [ ] Error pages working (404, 500, etc.)
- [ ] Monitoring set up
- [ ] Load testing completed
- [ ] Documentation updated

## 🆘 Troubleshooting

### Email Not Sending

1. Check `MAILER_DSN` format
2. Verify SMTP credentials
3. Check firewall/port 587/465
4. Test with `php bin/console mailer:test`

### Rate Limiting Too Strict

Edit `backend/config/packages/rate_limiter.yaml` and adjust limits.

### CORS Errors

Update CORS headers in `docker/nginx/default.conf` with your frontend domain.

### Database Connection Issues

1. Verify `DATABASE_URL` format
2. Check MySQL is accessible
3. Verify user permissions
4. Check firewall rules

## 📚 Additional Resources

- [Symfony Production Best Practices](https://symfony.com/doc/current/deployment.html)
- [Nuxt.js Deployment](https://nuxt.com/docs/getting-started/deployment)
- [Docker Production Guide](https://docs.docker.com/config/containers/logging/)

