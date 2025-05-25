<?php
// DB connection settings
$host = 'localhost';
$db = 'tmf656';
$user = 'root';
$pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, return error and exit
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

// Set response content type to JSON
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed, please use POST']);
    exit;
}

// Get JSON input from request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['serviceProblemId']) || !isset($data['acknowledgedBy'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: serviceProblemId and acknowledgedBy']);
    exit;
}

try {
    // Prepare and execute insert statement
    $stmt = $pdo->prepare("INSERT INTO problem_acknowledgement (serviceProblemId, acknowledgedBy, date) VALUES (?, ?, NOW())");
    $stmt->execute([$data['serviceProblemId'], $data['acknowledgedBy']]);

    // Send success response
    echo json_encode(['message' => 'Acknowledgement saved']);
} catch (PDOException $e) {
    // On DB error, return error message
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
