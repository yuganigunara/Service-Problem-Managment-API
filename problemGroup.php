<?php
// Database connection settings
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

// Set JSON response header
header('Content-Type: application/json');

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Read input JSON body if applicable
$data = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'POST':
        // CREATE a new record
        if (isset($data['parentId']) && isset($data['childId'])) {
            $stmt = $pdo->prepare("INSERT INTO problem_group (parentId, childId, dateCreated, dateUpdated) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$data['parentId'], $data['childId']]);
            echo json_encode(['message' => 'Problem grouped successfully', 'id' => $pdo->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parentId or childId']);
        }
        break;

    case 'GET':
        // READ all or one record by id
        if (isset($_GET['id'])) {
            // Get one record by id
            $stmt = $pdo->prepare("SELECT * FROM problem_group WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
            }
        } else {
            // Get all records
            $stmt = $pdo->query("SELECT * FROM problem_group");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        }
        break;

    case 'PUT':
        // UPDATE a record by id
        if (isset($data['id']) && (isset($data['parentId']) || isset($data['childId']))) {
            // Build dynamic query depending on which fields provided
            $fields = [];
            $params = [];

            if (isset($data['parentId'])) {
                $fields[] = "parentId = ?";
                $params[] = $data['parentId'];
            }
            if (isset($data['childId'])) {
                $fields[] = "childId = ?";
                $params[] = $data['childId'];
            }
            $fields[] = "dateUpdated = NOW()";

            $params[] = $data['id'];

            $sql = "UPDATE problem_group SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount()) {
                echo json_encode(['message' => 'Record updated successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found or no changes made']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id or fields to update']);
        }
        break;

    case 'DELETE':
        // DELETE a record by id
        // id can be sent as query param ?id= or in JSON body
        $id = $_GET['id'] ?? ($data['id'] ?? null);

        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM problem_group WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount()) {
                echo json_encode(['message' => 'Record deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id to delete']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
?>
