class Note {
    private $conn;
    private $table = "notes";

    public $id;
    public $user_id;
    public $title;
    public $content;
    public $category_id;
    public $is_pinned;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, title=:title, content=:content, 
                      category_id=:category_id, is_pinned=:is_pinned";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":is_pinned", $this->is_pinned);

        return $stmt->execute();
    }

    public function readByUser() {
        $query = "SELECT n.*, c.name as category_name, c.color as category_color 
                  FROM " . $this->table . " n
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE n.user_id = :user_id
                  ORDER BY n.is_pinned DESC, n.updated_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, content = :content, 
                      category_id = :category_id, is_pinned = :is_pinned 
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':is_pinned', $this->is_pinned);
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