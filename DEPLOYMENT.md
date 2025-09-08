# Maya Backend - CRITICAL Deployment Fix for Render

## 🚨 URGENT: Render Keep Using Node.js Auto-Detection

The issue is that **Render is ignoring our configuration** and auto-detecting Node.js.

## ✅ SOLUTION: Manual Configuration Required

### Step 1: Delete Current Service
1. Go to: https://dashboard.render.com
2. **DELETE** the current service completely
3. Wait for it to be fully removed

### Step 2: Create NEW Service - MANUAL CONFIGURATION
1. Click "New +" → "Web Service"
2. Connect GitHub: `yael11019/maya`
3. **CRITICAL SETTINGS**:

```
Service Name: maya-backend-php
Branch: main
Root Directory: maya_backend        ← ESSENTIAL
Runtime: PHP                        ← SELECT MANUALLY, DON'T AUTO-DETECT
PHP Version: 8.1 or 8.2
Build Command: ./build.sh
Start Command: php artisan serve --host=0.0.0.0 --port=$PORT
```

### Step 3: Environment Variables
Add these **before** first deploy:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:z6mInzSvlq5W4Yh/P+NTr/F5dAflLugVNZu64drJgBk=
APP_URL=https://[YOUR-SERVICE-NAME].onrender.com
DB_CONNECTION=sqlite
DB_DATABASE=/opt/render/project/src/database/database.sqlite
JWT_SECRET=BaV8yGmrSZZgTjMdBKEj8QOKyBD2FxtjzR6C9L3k
FACEBOOK_APP_ID=1117448569880953
FACEBOOK_APP_SECRET=your_secret_here
```

### Step 4: Advanced Settings
- **Auto-Deploy**: Yes
- **Health Check Path**: `/api/health`
- **Root Directory**: `maya_backend` ← DOUBLE CHECK THIS

## 🔧 Files Added to Force PHP Detection:
- ✅ `.render-buildpacks.rc` - Forces PHP buildpack
- ✅ `Procfile` - Specifies start command
- ✅ Enhanced `build.sh` - Better error handling
- ✅ `index.php` - PHP detection file

## 🎯 Expected Success Log:
```
🚀 Starting Laravel build process...
📋 PHP Version: 8.2.x
📦 Installing Composer dependencies...
✅ Build completed successfully!
🎯 Ready to start Laravel server!
```

## ⚠️ If Still Fails:
Contact Render support and specify: "Please use PHP runtime, not Node.js auto-detection"
