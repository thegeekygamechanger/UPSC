    class TodoList {
    private $conn;
    private $table = "todo_lists";

    public $id;
    public $user_id;
    public $title;
    public $description;
    public $priority;
    public $status;
    public $due_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, title=:title, description=:description, 
                      priority=:priority, due_date=:due_date";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":due_date", $this->due_date);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readByUser() {
        $query = "SELECT tl.*, COUNT(ti.id) as total_items, 
                         SUM(CASE WHEN ti.is_completed = 1 THEN 1 ELSE 0 END) as completed_items
                  FROM " . $this->table . " tl
                  LEFT JOIN todo_items ti ON tl.id = ti.todo_list_id
                  WHERE tl.user_id = :user_id
                  GROUP BY tl.id
                  ORDER BY tl.priority DESC, tl.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, description = :description, 
                      priority = :priority, status = :status, due_date = :due_date 
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':due_date', $this->due_date);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        return $stmt->execute();
    }
}