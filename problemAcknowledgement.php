<?php
// DB connection settings
$host = 'localhost';
$db = 'tmf656';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Create new acknowledgement
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['serviceProblemId']) || !isset($data['acknowledgedBy'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: serviceProblemId and acknowledgedBy']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO problem_acknowledgement (serviceProblemId, acknowledgedBy, date) VALUES (?, ?, NOW())");
            $stmt->execute([$data['serviceProblemId'], $data['acknowledgedBy']]);
            echo json_encode(['message' => 'Acknowledgement saved', 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // Read acknowledgement(s)
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM problem_acknowledgement WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
            }
        } else {
            // Return all records
            $stmt = $pdo->query("SELECT * FROM problem_acknowledgement");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        }
        break;

    case 'PUT':
        // Update acknowledgement by id
        parse_str(file_get_contents("php://input"), $put_vars);
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter']);
            exit;
        }
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['serviceProblemId']) || !isset($data['acknowledgedBy'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: serviceProblemId and acknowledgedBy']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE problem_acknowledgement SET serviceProblemId = ?, acknowledgedBy = ? WHERE id = ?");
            $stmt->execute([$data['serviceProblemId'], $data['acknowledgedBy'], $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Acknowledgement updated']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found or no change']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Delete acknowledgement by id
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter']);
            exit;
        }
        $id = intval($_GET['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM problem_acknowledgement WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Acknowledgement deleted']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>

