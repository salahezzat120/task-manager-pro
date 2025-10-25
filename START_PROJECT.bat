@echo off
echo 🚀 Starting Task Management Project...
echo.

echo 📋 Instructions:
echo 1. This will start the BACKEND server
echo 2. You need to start the FRONTEND separately
echo.

echo 🔧 Starting Backend Server...
cd /d "%~dp0backend"

echo 📍 Current directory: %cd%
echo 📂 Checking if public directory exists...
if not exist "public" (
    echo ❌ ERROR: public directory not found!
    echo    Make sure you're running this from the project root
    pause
    exit /b 1
)

echo ✅ public directory found
echo 📄 Checking if index.php exists...
if not exist "public\index.php" (
    echo ❌ ERROR: public\index.php not found!
    pause
    exit /b 1
)

echo ✅ index.php found
echo.
echo 🚀 Starting PHP Development Server...
echo 🌐 Backend will be available at: http://localhost:8000/api/
echo.
echo ⚠️  IMPORTANT: Keep this window open while using the app!
echo ⚠️  To start frontend, open NEW terminal and run: npm run dev
echo.

php -S localhost:8000 -t public
