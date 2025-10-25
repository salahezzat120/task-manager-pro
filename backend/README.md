# Task Management API - Laravel Backend

A Laravel-based RESTful API for the Task Management System with MySQL database, authentication, and comprehensive task management features.

## Features

- **User Authentication**: Secure signup/login with Laravel Sanctum
- **Task Management**: Full CRUD operations for tasks
- **Task Assignment**: Assign tasks to users by email
- **Task Filtering**: Filter by status, priority, and search
- **CORS Support**: Configured for frontend integration
- **Database Security**: PDO with prepared statements
- **RESTful APIs**: Clean, consistent API endpoints

## API Endpoints

### Authentication
- `POST /api/auth/signup` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### Tasks
- `GET /api/tasks` - Get all tasks for authenticated user
- `POST /api/tasks` - Create new task
- `GET /api/tasks/{id}` - Get specific task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `POST /api/tasks/{id}/toggle-complete` - Toggle task completion
- `GET /api/tasks-stats` - Get task statistics

## Installation & Setup

### Option 1: Using Composer (Recommended)

1. **Install Composer** (if not already installed):
   ```bash
   # Download and install Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

2. **Install Dependencies**:
   ```bash
   cd backend
   composer install
   ```

3. **Environment Setup**:
   ```bash
   cp env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**:
   - Create MySQL database: `task_management`
   - Update `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_management
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

6. **Start Development Server**:
   ```bash
   php artisan serve
   ```

### Option 2: Using Docker

1. **Create Docker Compose File**:
   ```yaml
   version: '3.8'
   services:
     app:
       build:
         context: .
         dockerfile: Dockerfile
       ports:
         - "8000:8000"
       volumes:
         - .:/var/www/html
       environment:
         - APP_ENV=local
         - APP_DEBUG=true
       depends_on:
         - db
     
     db:
       image: mysql:8.0
       environment:
         MYSQL_ROOT_PASSWORD: root
         MYSQL_DATABASE: task_management
       ports:
         - "3306:3306"
       volumes:
         - mysql_data:/var/lib/mysql
   
   volumes:
     mysql_data:
   ```

2. **Create Dockerfile**:
   ```dockerfile
   FROM php:8.1-fpm
   
   RUN apt-get update && apt-get install -y \
       git \
       curl \
       libpng-dev \
       libonig-dev \
       libxml2-dev \
       zip \
       unzip \
       mysql-client
   
   RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
   
   COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
   
   WORKDIR /var/www/html
   
   COPY . .
   
   RUN composer install --no-dev --optimize-autoloader
   
   EXPOSE 8000
   
   CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
   ```

3. **Run with Docker**:
   ```bash
   docker-compose up -d
   ```

### Option 3: Using XAMPP

1. **Install XAMPP** and start Apache + MySQL
2. **Create Database**: Create `task_management` database in phpMyAdmin
3. **Copy Project**: Place backend folder in `htdocs`
4. **Install Dependencies**: Run `composer install` in project directory
5. **Configure Environment**: Update `.env` file with XAMPP database settings
6. **Run Migrations**: Execute `php artisan migrate`
7. **Access**: Visit `http://localhost/backend/public`

## Environment Configuration

Update your `.env` file with the following settings:

```env
APP_NAME="Task Management API"
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=your_password

# CORS Configuration
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With
```

## API Usage Examples

### Authentication

**Signup**:
```bash
curl -X POST http://localhost:8000/api/auth/signup \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

**Login**:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

### Task Management

**Create Task**:
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token" \
  -d '{
    "title": "Complete project",
    "description": "Finish the task management system",
    "due_date": "2024-12-31",
    "priority": "high",
    "assignee_email": "assignee@example.com"
  }'
```

**Get Tasks**:
```bash
curl -X GET http://localhost:8000/api/tasks \
  -H "Authorization: Bearer your_token"
```

## Database Schema

### Users Table
- `id` (Primary Key)
- `email` (Unique)
- `password` (Hashed)
- `created_at`, `updated_at`

### Tasks Table
- `id` (Primary Key)
- `creator_id` (Foreign Key → users.id)
- `assignee_id` (Foreign Key → users.id)
- `title`
- `description`
- `due_date`
- `priority` (low/medium/high)
- `is_completed` (boolean)
- `completed_at`
- `created_at`, `updated_at`

## Security Features

- **Laravel Sanctum** for API authentication
- **PDO with prepared statements** for database security
- **CORS middleware** for cross-origin requests
- **Input validation** on all endpoints
- **Foreign key constraints** for data integrity
- **Password hashing** with bcrypt

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Seeding
```bash
php artisan db:seed
```

## Production Deployment

1. **Set Environment**: Change `APP_ENV=production` in `.env`
2. **Optimize**: Run `php artisan config:cache` and `php artisan route:cache`
3. **Web Server**: Configure Apache/Nginx to serve Laravel
4. **Database**: Use production MySQL/PostgreSQL database
5. **SSL**: Enable HTTPS for secure API communication

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials in `.env`
   - Ensure MySQL service is running
   - Verify database exists

2. **CORS Issues**:
   - Update `CORS_ALLOWED_ORIGINS` in `.env`
   - Check frontend URL matches allowed origins

3. **Authentication Issues**:
   - Verify token is included in Authorization header
   - Check token format: `Bearer your_token`

4. **Migration Errors**:
   - Run `php artisan migrate:fresh` to reset database
   - Check database permissions

## Support

For issues and questions, please check the Laravel documentation or create an issue in the project repository.


