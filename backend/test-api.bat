@echo off
echo 🧪 Testing Task Management API...

echo.
echo 1. Testing API status...
curl -s http://localhost:8000
if %errorlevel% neq 0 (
    echo ❌ API is not running. Please start with: php artisan serve
    pause
    exit /b 1
)

echo.
echo 2. Testing user registration...
curl -X POST http://localhost:8000/api/auth/signup ^
  -H "Content-Type: application/json" ^
  -d "{\"email\": \"test@example.com\", \"password\": \"password123\"}"

echo.
echo.
echo 3. Testing user login...
curl -X POST http://localhost:8000/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\": \"test@example.com\", \"password\": \"password123\"}"

echo.
echo.
echo ✅ API tests completed!
echo.
echo To test with your React frontend:
echo 1. Update your frontend API base URL to: http://localhost:8000/api
echo 2. Make sure CORS is configured for your frontend URL
echo.
pause


