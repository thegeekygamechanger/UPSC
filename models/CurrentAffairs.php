class CurrentAffairs {
    private $conn;
    private $table = "current_affairs";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getToday() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE date = CURDATE() 
                  ORDER BY importance DESC, created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE date BETWEEN :start_date AND :end_date 
                  ORDER BY date DESC, importance DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}