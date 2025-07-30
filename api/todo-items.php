header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/TodoItem.php';
include_once '../utils/JWT.php';

$database = new Database();
$db = $database->getConnection();

// Verify JWT token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $token);

$decoded = JWT::decode($token);
if(!$decoded) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$todoItem = new TodoItem($db);

switch($method) {
    case 'POST':
        if($_GET['action'] == 'toggle') {
            $todoItem->id = $_GET['id'];
            if($todoItem->toggleComplete()) {
                echo json_encode(['message' => 'Item toggled successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['message' => 'Unable to toggle item']);
            }
        } else {
            $data = json_decode(file_get_contents("php://input"));
            $todoItem->todo_list_id = $data->todo_list_id;
            $todoItem->content = $data->content;
            $todoItem->category_id = $data->category_id ?? null;
            $todoItem->position = $data->position ?? 0;
            
            if($todoItem->create()) {
                http_response_code(201);
                echo json_encode(['message' => 'Item created successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['message' => 'Unable to create item']);
            }
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $todoItem->id = $data->id;
        $todoItem->content = $data->content;
        $todoItem->category_id = $data->category_id;
        
        if($todoItem->update()) {
            echo json_encode(['message' => 'Item updated successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to update item']);
        }
        break;
        
    case 'DELETE':
        $todoItem->id = $_GET['id'];
        
        if($todoItem->delete()) {
            echo json_encode(['message' => 'Item deleted successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to delete item']);
        }
        break;
}