class Notification {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function sendEmail($user_id, $to, $subject, $content) {
        // Email sending logic (using PHPMailer or similar)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: UPSC Todo App <noreply@upsctodo.com>' . "\r\n";
        
        $success = mail($to, $subject, $content, $headers);
        
        // Log the notification
        $this->logNotification($user_id, 'email', $to, $subject, $content, $success ? 'sent' : 'failed');
        
        return $success;
    }

    public function sendSMS($user_id, $phone, $message) {
        // SMS sending logic (using Twilio, TextLocal, or similar)
        // This is a placeholder - implement with your SMS provider
        $success = true; // Simulate success
        
        $this->logNotification($user_id, 'sms', $phone, 'Todo Reminder', $message, $success ? 'sent' : 'failed');
        
        return $success;
    }

    private function logNotification($user_id, $type, $recipient, $subject, $content, $status) {
        $query = "INSERT INTO notification_logs 
                  SET user_id=:user_id, type=:type, recipient=:recipient, 
                      subject=:subject, content=:content, status=:status, 
                      sent_at=NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':recipient', $recipient);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':status', $status);
        
        $stmt->execute();
    }

    public function sendReminders() {
        $query = "SELECT tl.*, u.name, u.email, u.phone_number 
                  FROM todo_lists tl
                  JOIN users u ON tl.user_id = u.id
                  WHERE tl.reminder_sent = 0 
                  AND tl.status != 'completed'
                  AND tl.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 90 MINUTE)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($reminders as $reminder) {
            $message = "Hi {$reminder['name']}, reminder: '{$reminder['title']}' is due at " . 
                      date('h:i A', strtotime($reminder['due_date']));
            
            // Send email if available
            if($reminder['email']) {
                $this->sendEmail($reminder['user_id'], $reminder['email'], 
                               'Todo Reminder: ' . $reminder['title'], $message);
            }
            
            // Send SMS
            if($reminder['phone_number']) {
                $this->sendSMS($reminder['user_id'], $reminder['phone_number'], $message);
            }
            
            // Mark as sent
            $updateQuery = "UPDATE todo_lists SET reminder_sent = 1 WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $reminder['id']);
            $updateStmt->execute();
        }
    }
}
