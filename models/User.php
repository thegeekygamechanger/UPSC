class User {
    private $conn;
    private $table = "users";

    public $id;
    public $phone_number;
    public $name;                       
    public $email;
    public $password_hash;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {      
        $query = "INSERT INTO " . $this->table . " 
                  SET phone_number=:phone_number, name=:name, email=:email, password_hash=:password_hash";

        $stmt = $this->conn->prepare($query);

        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, name, email, password_hash FROM " . $this->table . " 
                  WHERE phone_number = :phone_number LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password_hash, $row['password_hash'])) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, email = :email, phone_number = :phone_number 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone_number', $this->phone_number);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}