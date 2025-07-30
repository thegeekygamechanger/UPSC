    class TodoItem {
    private $conn;
    private $table = "todo_items";

    public $id;
    public $todo_list_id;
    public $category_id;
    public $content;
    public $is_completed;
    public $position;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET todo_list_id=:todo_list_id, category_id=:category_id, 
                      content=:content, position=:position";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":todo_list_id", $this->todo_list_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":position", $this->position);

        return $stmt->execute();
    }

    public function readByList() {
        $query = "SELECT ti.*, c.name as category_name, c.color as category_color 
                  FROM " . $this->table . " ti
                  LEFT JOIN categories c ON ti.category_id = c.id
                  WHERE ti.todo_list_id = :todo_list_id
                  ORDER BY ti.position ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":todo_list_id", $this->todo_list_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleComplete() {
        $query = "UPDATE " . $this->table . " 
                  SET is_completed = NOT is_completed, 
                      completed_at = CASE WHEN is_completed = 0 THEN NOW() ELSE NULL END
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET content = :content, category_id = :category_id 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':id', $this->id);
                    
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}                          