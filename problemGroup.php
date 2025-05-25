<?php
// Database connection
$host = 'localhost';
$db = 'tmf656';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if required keys are present
if (isset($data['parentId']) && isset($data['childId'])) {
    $stmt = $pdo->prepare("INSERT INTO problem_group (parentId, childId) VALUES (?, ?)");
    $stmt->execute([$data['parentId'], $data['childId']]);
    echo json_encode(['message' => 'Problem grouped']);
} else {
    echo json_encode(['error' => 'Invalid input data']);
}
?>
