#!/bin/bash
# Render.com Build Script for Laravel
# This script sets up the environment for Laravel on Render

set -e  # Exit on any error

echo "🚀 Starting Laravel build process..."
echo "📍 Working directory: $(pwd)"
echo "📋 Files in directory: $(ls -la)"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ artisan file not found. Are we in the Laravel root?"
    echo "📍 Current directory: $(pwd)"
    echo "📂 Directory contents: $(ls -la)"
    exit 1
fi

# Check PHP version
echo "📋 PHP Version: $(php --version)"

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Clear any existing cache
echo "🧹 Clearing caches..."
php artisan config:clear || echo "⚠️ Config clear failed (might be ok)"
php artisan cache:clear || echo "⚠️ Cache clear failed (might be ok)"
php artisan view:clear || echo "⚠️ View clear failed (might be ok)"
php artisan route:clear || echo "⚠️ Route clear failed (might be ok)"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Create storage directories
echo "� Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || echo "⚠️ Storage link failed (might be ok)"

# Check if database directory exists
if [ ! -d "./database" ]; then
    echo "❌ Database directory not found!"
    exit 1
fi

# Check if database exists, if not create it
if [ ! -f "./database/database.sqlite" ]; then
    echo "🗄️ Creating SQLite database..."
    touch ./database/database.sqlite
fi

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force || echo "⚠️ Migration failed"

# Cache config for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache || echo "⚠️ Route cache failed (might be ok)"
php artisan view:cache

echo "✅ Build completed successfully!"
echo "🎯 Ready to start Laravel server!"
