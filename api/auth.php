header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/User.php';
include_once '../utils/JWT.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));
$action = $_GET['action'] ?? '';

if($action == 'signup') {
    if(!empty($data->phone_number)) {
        $user->phone_number = $data->phone_number;
        $user->name = $data->name ?? null;
        $user->email = $data->email ?? null;
        $user->password_hash = password_hash($data->password ?? $data->phone_number, PASSWORD_BCRYPT);
        
        if($user->create()) {
            $token = JWT::encode([
                'id' => $db->lastInsertId(),
                'phone_number' => $user->phone_number,
                'exp' => time() + (86400 * 7) // 7 days
            ]);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'User created successfully',
                'token' => $token,
                'user' => [
                    'id' => $db->lastInsertId(),
                    'phone_number' => $user->phone_number,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to create user']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Phone number is required']);
    }
}

if($action == 'login') {
    if(!empty($data->phone_number) && !empty($data->password)) {
        $user->phone_number = $data->phone_number;
        $user->password_hash = $data->password;
        
        if($user->login()) {
            $token = JWT::encode([
                'id' => $user->id,
                'phone_number' => $user->phone_number,
                'exp' => time() + (86400 * 7)
            ]);
            
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'phone_number' => $user->phone_number,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Phone number and password are required']);
    }
}