header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/TodoList.php';
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

$user_id = $decoded['id'];
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $todoList = new TodoList($db);
        $todoList->user_id = $user_id;
        
        if(isset($_GET['id'])) {
            // Get specific todo with items
            $todoList->id = $_GET['id'];
            $todo = $todoList->read();
            
            if($todo) {
                $todoItem = new TodoItem($db);
                $todoItem->todo_list_id = $_GET['id'];
                $items = $todoItem->readByList();
                
                $todo['items'] = $items;
                echo json_encode($todo);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Todo not found']);
            }
        } else {
            // Get all todos for user
            $todos = $todoList->readByUser();
            echo json_encode($todos);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        $todoList = new TodoList($db);
        $todoList->user_id = $user_id;
        $todoList->title = $data->title;
        $todoList->description = $data->description ?? '';
        $todoList->priority = $data->priority ?? 'medium';
        $todoList->due_date = $data->due_date ?? null;
        
        if($todoList->create()) {
            // Create todo items if provided
            if(isset($data->items) && is_array($data->items)) {
                $todoItem = new TodoItem($db);
                foreach($data->items as $index => $item) {
                    $todoItem->todo_list_id = $todoList->id;
                    $todoItem->content = $item->content;
                    $todoItem->category_id = $item->category_id ?? null;
                    $todoItem->position = $index;
                    $todoItem->create();
                }
            }
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Todo created successfully',
                'id' => $todoList->id
            ]);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to create todo']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        $todoList = new TodoList($db);
        $todoList->id = $data->id;
        $todoList->user_id = $user_id;
        $todoList->title = $data->title;
        $todoList->description = $data->description;
        $todoList->priority = $data->priority;
        $todoList->status = $data->status;
        $todoList->due_date = $data->due_date;
        
        if($todoList->update()) {
            echo json_encode(['message' => 'Todo updated successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to update todo']);
        }
        break;
        
    case 'DELETE':
        $todoList = new TodoList($db);
        $todoList->id = $_GET['id'];
        $todoList->user_id = $user_id;
        
        if($todoList->delete()) {
            echo json_encode(['message' => 'Todo deleted successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to delete todo']);
        }
        break;
}