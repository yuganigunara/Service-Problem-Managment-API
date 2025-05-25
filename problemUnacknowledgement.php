<?php
// Set JSON response header
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'tmf656';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
}

// Detect HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Read and decode input data
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    // For PUT and DELETE if JSON is not passed properly
    parse_str(file_get_contents("php://input"), $data);
}

switch ($method) {
    case 'POST': // Create
        if (isset($data['serviceProblemId']) && isset($data['reason'])) {
            $stmt = $pdo->prepare("INSERT INTO problem_unacknowledgement (serviceProblemId, reason, date) VALUES (?, ?, NOW())");
            $stmt->execute([$data['serviceProblemId'], $data['reason']]);
            echo json_encode(['message' => 'Unacknowledgement saved']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input data']);
        }
        break;

    case 'GET': // Read
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM problem_unacknowledgement WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $result ? json_encode($result) : json_encode(['error' => 'Record not found']);
        } else {
            $stmt = $pdo->query("SELECT * FROM problem_unacknowledgement");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'PUT': // Update
        if (isset($data['id']) && isset($data['serviceProblemId']) && isset($data['reason'])) {
            $stmt = $pdo->prepare("UPDATE problem_unacknowledgement SET serviceProblemId = ?, reason = ? WHERE id = ?");
            $stmt->execute([$data['serviceProblemId'], $data['reason'], $data['id']]);
            if ($stmt->rowCount()) {
                echo json_encode(['message' => 'Record updated']);
            } else {
                echo json_encode(['message' => 'No changes made or record not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input for update']);
        }
        break;

    case 'DELETE': // Delete
        if (isset($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM problem_unacknowledgement WHERE id = ?");
            $stmt->execute([$data['id']]);
            if ($stmt->rowCount()) {
                echo json_encode(['message' => 'Record deleted']);
            } else {
                echo json_encode(['error' => 'Record not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID for deletion']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Unsupported method']);
}
?>
