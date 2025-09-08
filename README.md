# Maya Pets Backend API

Laravel-based REST API for the Maya Pets application.

## 🚀 Features

- **Authentication**: JWT-based auth with Facebook OAuth integration
- **Pet Management**: CRUD operations for pet profiles
- **Social Network**: Posts, comments, followers system
- **Veterinary Services**: Appointments, vaccinations tracking
- **File Upload**: Image handling for pet avatars and posts

## 🛠 Tech Stack

- **Framework**: Laravel 12
- **Database**: SQLite (production) / MySQL (local)
- **Authentication**: JWT + Laravel Socialite (Facebook)
- **Storage**: Laravel Storage (local/cloud)

## 📋 Requirements

- PHP 8.2+
- Composer
- SQLite extension

## 🔧 Local Development

1. Clone the repository:
```bash
git clone https://github.com/yael11019/maya-backend-api.git
cd maya-backend-api
```

2. Install dependencies:
```bash
composer install
```

3. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start development server:
```bash
php artisan serve --port=8003
```

## 🌐 API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/facebook/login` - Facebook OAuth login
- `POST /api/auth/logout` - Logout (requires auth)

### Pets
- `GET /api/pets` - List user's pets
- `POST /api/pets` - Create new pet
- `PUT /api/pets/{id}` - Update pet
- `DELETE /api/pets/{id}` - Delete pet

### Social Network
- `GET /api/social/posts` - Get timeline posts
- `POST /api/social/posts` - Create new post
- `POST /api/social/posts/{id}/like` - Like/unlike post
- `GET /api/social/search` - Search pets/users

### Health Check
- `GET /api/health` - Service health status

## 🚀 Deployment (Render)

This repository is configured for deployment on Render.com:

1. Connect this repository to Render
2. Configure as **PHP** web service  
3. Set environment variables (see .env.example)
4. Deploy!

Build command: `./build.sh`
Start command: `php artisan serve --host=0.0.0.0 --port=$PORT`

## 🔐 Environment Variables

Required environment variables for production:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=your_app_key_here
APP_URL=https://your-domain.onrender.com
DB_CONNECTION=sqlite
JWT_SECRET=your_jwt_secret
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_secret
```

## 📝 License

This project is private and proprietary.
