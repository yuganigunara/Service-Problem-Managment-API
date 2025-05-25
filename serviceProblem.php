<?php
// Database connection settings
$host = 'localhost';
$db = 'tmf656';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "DB connection failed: " . $e->getMessage()]);
    exit;
}

// Set JSON response header
header("Content-Type: application/json");

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Handle request based on HTTP method
switch ($method) {
    case 'GET':
        // Fetch all service problems
        $stmt = $pdo->query("SELECT * FROM service_problem");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
        break;

    case 'POST':
        // Create new service problem
        $data = json_decode(file_get_contents("php://input"), true);

        // Basic validation
        if (!isset($data['name'], $data['description'], $data['status'], $data['priority'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO service_problem (name, description, status, priority, creationDate) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$data['name'], $data['description'], $data['status'], $data['priority']]);
        echo json_encode(['message' => 'Service Problem Created', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PATCH':
        // Update existing service problem
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['name'], $data['description'], $data['status'], $data['priority'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE service_problem SET name = ?, description = ?, status = ?, priority = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['status'], $data['priority'], $data['id']]);
        echo json_encode(['message' => 'Service Problem Updated']);
        break;

    case 'DELETE':
        // Delete a service problem
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing service problem ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM service_problem WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['message' => 'Service Problem Deleted']);
        break;

    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
