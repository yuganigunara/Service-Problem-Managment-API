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

// Check if required fields are set
if (isset($data['serviceProblemId']) && isset($data['reason'])) {
    $stmt = $pdo->prepare("INSERT INTO problem_unacknowledgement (serviceProblemId, reason, date) VALUES (?, ?, NOW())");
    $stmt->execute([$data['serviceProblemId'], $data['reason']]);
    echo json_encode(['message' => 'Unacknowledgement saved']);
} else {
    echo json_encode(['error' => 'Invalid input data']);
}
?>
