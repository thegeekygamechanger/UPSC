class Community {
    private $conn;
    private $table = "community_messages";

    public $id;
    public $user_id;
    public $message;
    public $parent_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, message=:message, parent_id=:parent_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":parent_id", $this->parent_id);

        return $stmt->execute();
    }

    public function getRecent($limit = 50) {
        $query = "SELECT cm.*, u.name as user_name 
                  FROM " . $this->table . " cm
                  JOIN users u ON cm.user_id = u.id
                  WHERE cm.parent_id IS NULL
                  ORDER BY cm.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReplies($parent_id) {
        $query = "SELECT cm.*, u.name as user_name 
                  FROM " . $this->table . " cm
                  JOIN users u ON cm.user_id = u.id
                  WHERE cm.parent_id = :parent_id
                  ORDER BY cm.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
                        