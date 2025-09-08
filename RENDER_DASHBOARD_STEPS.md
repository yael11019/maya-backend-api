# 🚨 URGENT: Render Dashboard Instructions

## THE CRITICAL ISSUE:
You are **NOT** creating a new service. You're using the existing service that has Node.js cached configuration.

## STEP-BY-STEP SOLUTION:

### 1. Go to Render Dashboard: https://dashboard.render.com

### 2. Find your current service (probably named "maya" or similar)

### 3. **CRITICAL**: Click on the service name, then:
   - Click "Settings" tab
   - Scroll to bottom
   - Click "Delete Service" 
   - Type the service name to confirm
   - **WAIT** for complete deletion (this may take 2-3 minutes)

### 4. **ONLY AFTER** complete deletion, create new service:
   - Click "New +" button
   - Select "Web Service"
   - Connect GitHub repo: `yael11019/maya`
   - **IMPORTANT**: When it asks for configuration:

```
Service Name: maya-php-backend
Branch: main
Root Directory: maya_backend
```

### 5. **CRITICAL**: Look for "Runtime" or "Environment" selection:
   - **DO NOT** let it auto-detect
   - **MANUALLY SELECT**: "PHP" 
   - **Version**: 8.1 or 8.2

### 6. Commands:
```
Build Command: ./build.sh
Start Command: php artisan serve --host=0.0.0.0 --port=$PORT
```

### 7. Environment Variables (add BEFORE deploying):
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:z6mInzSvlq5W4Yh/P+NTr/F5dAflLugVNZu64drJgBk=
APP_URL=https://[service-name].onrender.com
DB_CONNECTION=sqlite
DB_DATABASE=/opt/render/project/src/database/database.sqlite
JWT_SECRET=BaV8yGmrSZZgTjMdBKEj8QOKyBD2FxtjzR6C9L3k
FACEBOOK_APP_ID=1117448569880953
```

## 🎯 Expected SUCCESS log:
```
Using PHP buildpack
🚀 Starting Laravel build process...
📋 PHP Version: 8.2.x
📦 Installing Composer dependencies...
✅ Build completed successfully!
```

## ❌ If you still see "Using Node.js version", you didn't delete the old service completely.

## 📧 ALTERNATIVE: Contact Render Support
If auto-detection persists, email Render support:
"Please manually configure my service to use PHP buildpack instead of Node.js auto-detection for repository yael11019/maya, directory maya_backend"
