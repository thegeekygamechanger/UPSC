include_once '../config/database.php';
include_once '../utils/Notification.php';

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);
$notification->sendReminders();

echo "Reminders sent successfully\n";