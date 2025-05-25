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

// Check if required fields are present
if (isset($data['parentId']) && isset($data['childId'])) {
    $stmt = $pdo->prepare("DELETE FROM problem_group WHERE parentId = ? AND childId = ?");
    $stmt->execute([$data['parentId'], $data['childId']]);
    echo json_encode(['message' => 'Problem ungrouped']);
} else {
    echo json_encode(['error' => 'Invalid input data']);
}
?>
