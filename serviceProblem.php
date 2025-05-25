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
    die("DB connection failed: " . $e->getMessage());
}

// API logic
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM service_problem");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO service_problem (name, description, status, priority, creationDate) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$data['name'], $data['description'], $data['status'], $data['priority']]);
        echo json_encode(['message' => 'Service Problem Created']);
        break;

    case 'PATCH':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("UPDATE service_problem SET name=?, description=?, status=?, priority=? WHERE id=?");
        $stmt->execute([$data['name'], $data['description'], $data['status'], $data['priority'], $data['id']]);
        echo json_encode(['message' => 'Service Problem Updated']);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("DELETE FROM service_problem WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['message' => 'Service Problem Deleted']);
        break;
}
?>
