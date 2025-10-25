<?php

// Task Management API
// Handles authentication and task CRUD operations
// Structured for WhatsApp integration via webhooks

// Include WhatsApp integration (for future use)
// require_once 'whatsapp-integration.php';

// Security Headers
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS Configuration
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security Functions
function sanitizeInput($input) {
    if (is_string($input)) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    return strlen($password) >= 6 && strlen($password) <= 128;
}

function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] ERROR: {$message}";
    if (!empty($context)) {
        $logEntry .= " Context: " . json_encode($context);
    }
    error_log($logEntry);
}

function logInfo($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] INFO: {$message}";
    if (!empty($context)) {
        $logEntry .= " Context: " . json_encode($context);
    }
    error_log($logEntry);
}

// Database connection
$host = '127.0.0.1';
$dbname = 'task_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($request_uri, PHP_URL_PATH);

// Authentication Functions
function generateSecureToken($userId, $email) {
    $timestamp = time();
    $randomBytes = bin2hex(random_bytes(16));
    $payload = json_encode([
        'user_id' => $userId,
        'email' => $email,
        'timestamp' => $timestamp,
        'random' => $randomBytes
    ]);
    return base64_encode($payload);
}

function getCurrentUser($pdo) {
    try {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }
        
        $token = substr($authHeader, 7);
        $decoded = base64_decode($token);
        
        if (!$decoded) {
            logError('Invalid token format');
            return null;
        }
        
        $payload = json_decode($decoded, true);
        
        if (!$payload || !isset($payload['email']) || !isset($payload['timestamp'])) {
            logError('Invalid token payload');
            return null;
        }
        
        // Token expiry check (24 hours)
        if (time() - $payload['timestamp'] > 86400) {
            logError('Token expired', ['email' => $payload['email']]);
            return null;
        }
        
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ? AND id = ?");
        $stmt->execute([$payload['email'], $payload['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            logInfo('User authenticated successfully', ['user_id' => $user['id']]);
        }
        
        return $user;
        
    } catch (Exception $e) {
        logError('Authentication error: ' . $e->getMessage());
        return null;
    }
}

function validateTaskInput($input) {
    $errors = [];
    
    if (empty($input['title']) || strlen(trim($input['title'])) === 0) {
        $errors[] = 'Task title is required';
    } elseif (strlen($input['title']) > 255) {
        $errors[] = 'Task title must be less than 255 characters';
    }
    
    if (isset($input['description']) && strlen($input['description']) > 1000) {
        $errors[] = 'Task description must be less than 1000 characters';
    }
    
    if (empty($input['due_date'])) {
        $errors[] = 'Due date is required';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['due_date'])) {
        $errors[] = 'Due date must be in YYYY-MM-DD format';
    }
    
    if (isset($input['priority']) && !in_array($input['priority'], ['low', 'medium', 'high'])) {
        $errors[] = 'Priority must be low, medium, or high';
    }
    
    return $errors;
}

// API Routes
if (strpos($path, '/api/') === 0) {
    $path = substr($path, 4); // Remove /api
    
    switch ($path) {
        case '/':
            echo json_encode([
                'message' => 'Task Management API',
                'version' => '1.0.0',
                'status' => 'running'
            ]);
            break;
            
        case '/auth/signup':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['email']) || !isset($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
                    break;
                }
                
                $email = sanitizeInput(trim($input['email']));
                $password = $input['password'];
                
                // Enhanced validation
                if (!validateEmail($email)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
                    logError('Invalid email format during signup', ['email' => $email]);
                    break;
                }
                
                if (!validatePassword($password)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Password must be between 6 and 128 characters']);
                    break;
                }
                
                // Check if user already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingUser) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'error' => 'An account with this email already exists. Please sign in instead.']);
                    logError('Signup attempt with existing email', ['email' => $email]);
                    break;
                }
                
                // Enhanced password hashing with Argon2ID
                $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3
                ]);
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$email, $hashedPassword]);
                    
                    $userId = $pdo->lastInsertId();
                    $token = generateSecureToken($userId, $email);
                    
                    logInfo('User registered successfully', ['user_id' => $userId, 'email' => $email]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'User registered successfully',
                        'data' => [
                            'user' => ['id' => $userId, 'email' => $email],
                            'token' => $token
                        ]
                    ]);
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()]);
                }
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/auth/login':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['email']) || !isset($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
                    break;
                }
                
                $email = trim($input['email']);
                $password = $input['password'];
                
                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
                    break;
                }
                
                $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $token = generateSecureToken($user['id'], $user['email']);
                    
                    logInfo('User logged in successfully', ['user_id' => $user['id'], 'email' => $user['email']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'data' => [
                            'user' => ['id' => (int)$user['id'], 'email' => $user['email']],
                            'token' => $token
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
                }
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/auth/me':
            if ($method === 'GET') {
                // Get current authenticated user
                $currentUser = getCurrentUser($pdo);
                if (!$currentUser) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    break;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$currentUser['id'],
                        'email' => $currentUser['email']
                    ]
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/tasks':
            if ($method === 'GET') {
                // Get tasks assigned to authenticated user only
                $currentUser = getCurrentUser($pdo);
                if (!$currentUser) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    break;
                }
                
                $stmt = $pdo->prepare("
                    SELECT t.*, u1.email as creator_email, u2.email as assignee_email 
                    FROM tasks t 
                    LEFT JOIN users u1 ON t.creator_id = u1.id 
                    LEFT JOIN users u2 ON t.assignee_id = u2.id 
                    WHERE t.assignee_id = ?
                    ORDER BY t.due_date ASC
                ");
                $stmt->execute([$currentUser['id']]);
                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $tasks
                ]);
            } elseif ($method === 'POST') {
                // Create new task
                $currentUser = getCurrentUser($pdo);
                if (!$currentUser) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    break;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['title']) || !isset($input['assignee_email'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Title and assignee email are required']);
                    break;
                }
                
                if (!isset($input['due_date'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Due date is required']);
                    break;
                }
                
                // Find assignee
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$input['assignee_email']]);
                $assignee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$assignee) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Assignee email not found. User must have an account in the system.']);
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO tasks (creator_id, assignee_id, title, description, due_date, priority) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['id'], // Use authenticated user as creator
                    $assignee['id'],
                    $input['title'],
                    $input['description'] ?? '',
                    $input['due_date'],
                    $input['priority'] ?? 'medium'
                ]);
                
                $taskId = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'data' => ['id' => $taskId]
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/users':
            if ($method === 'GET') {
                // Get all users for assignment dropdown
                $stmt = $pdo->prepare("SELECT id, email FROM users ORDER BY email ASC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $users
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        default:
            // Handle dynamic routes like /tasks/{id}
            if (preg_match('/^\/tasks\/(\d+)$/', $path, $matches)) {
                $taskId = $matches[1];
                
                if ($method === 'PUT') {
                    // Update task - only assignee can edit
                    $currentUser = getCurrentUser($pdo);
                    if (!$currentUser) {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'error' => 'Authentication required']);
                        break;
                    }
                    
                    // Check if user is assignee
                    $stmt = $pdo->prepare("SELECT assignee_id FROM tasks WHERE id = ?");
                    $stmt->execute([$taskId]);
                    $task = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$task) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Task not found']);
                        break;
                    }
                    
                    if ($task['assignee_id'] != $currentUser['id']) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'You can only edit tasks assigned to you']);
                        break;
                    }
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    $stmt = $pdo->prepare("
                        UPDATE tasks 
                        SET title = ?, description = ?, due_date = ?, priority = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $input['title'],
                        $input['description'] ?? '',
                        $input['due_date'],
                        $input['priority'] ?? 'medium',
                        $taskId
                    ]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task updated successfully',
                        'data' => ['id' => $taskId]
                    ]);
                } elseif ($method === 'DELETE') {
                    // Delete task - assignee or creator can delete
                    $currentUser = getCurrentUser($pdo);
                    if (!$currentUser) {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'error' => 'Authentication required']);
                        break;
                    }
                    
                    // Check if user is assignee or creator
                    $stmt = $pdo->prepare("SELECT assignee_id, creator_id FROM tasks WHERE id = ?");
                    $stmt->execute([$taskId]);
                    $task = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$task) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Task not found']);
                        break;
                    }
                    
                    if ($task['assignee_id'] != $currentUser['id'] && $task['creator_id'] != $currentUser['id']) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'You can only delete tasks assigned to you or created by you']);
                        break;
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
                    $stmt->execute([$taskId]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task deleted successfully'
                    ]);
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
                }
            } elseif (preg_match('/^\/tasks\/(\d+)\/toggle-complete$/', $path, $matches)) {
                $taskId = $matches[1];
                
                if ($method === 'POST') {
                    // Toggle task completion - only assignee can toggle
                    $currentUser = getCurrentUser($pdo);
                    if (!$currentUser) {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'error' => 'Authentication required']);
                        break;
                    }
                    
                    // Check if user is assignee
                    $stmt = $pdo->prepare("SELECT assignee_id FROM tasks WHERE id = ?");
                    $stmt->execute([$taskId]);
                    $task = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$task) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Task not found']);
                        break;
                    }
                    
                    if ($task['assignee_id'] != $currentUser['id']) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'You can only complete tasks assigned to you']);
                        break;
                    }
                    
                    $input = json_decode(file_get_contents('php://input'), true);
                    $completed = $input['is_completed'] ?? false;
                    
                    $stmt = $pdo->prepare("
                        UPDATE tasks 
                        SET is_completed = ?, completed_at = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $completed ? 1 : 0,
                        $completed ? date('Y-m-d H:i:s') : null,
                        $taskId
                    ]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task completion updated',
                        'data' => ['id' => $taskId, 'is_completed' => $completed]
                    ]);
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case '/webhooks/whatsapp':
            if ($method === 'POST') {
                // WhatsApp webhook endpoint for future integration
                $input = json_decode(file_get_contents('php://input'), true);
                
                // Log webhook for now (implement actual handling later)
                logInfo('WhatsApp webhook received', ['data' => $input]);
                
                // Future: Process with WhatsAppNotificationService
                // $whatsappService = new WhatsAppNotificationService();
                // $result = $whatsappService->handleWebhook($input);
                
                echo json_encode(['status' => 'received']);
            } elseif ($method === 'GET') {
                // Webhook verification for WhatsApp
                $verify_token = 'your_verify_token_here';
                $hub_verify_token = $_GET['hub_verify_token'] ?? '';
                $hub_challenge = $_GET['hub_challenge'] ?? '';
                
                if ($hub_verify_token === $verify_token) {
                    echo $hub_challenge;
                } else {
                    http_response_code(403);
                    echo 'Forbidden';
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
    }
} else {
    // Serve static files or show API info
    echo json_encode([
        'message' => 'Task Management API',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/auth/signup',
            'POST /api/auth/login',
            'GET /api/auth/me',
            'GET /api/tasks',
            'POST /api/tasks',
            'PUT /api/tasks/{id}',
            'DELETE /api/tasks/{id}',
            'POST /api/tasks/{id}/toggle-complete',
            'GET /api/users',
            'POST /api/webhooks/whatsapp'
        ]
    ]);
}
?>

