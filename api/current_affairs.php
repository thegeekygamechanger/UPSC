<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Include database and object files
include_once '../config/database.php';
include_once '../models/CurrentAffairs.php';

// Instantiate database and current affairs object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$currentAffairs = new CurrentAffairs($db);

// Check for date parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$result = [];

// Fetch data based on provided parameters
if ($start_date && $end_date) {
    $result = $currentAffairs->getByDateRange($start_date, $end_date);
} else {
    $result = $currentAffairs->getToday();
}

// Check if any records were found
if (!empty($result)) {
    // Set response code - 200 OK
    http_response_code(200);

    // Output data in JSON format
    echo json_encode($result);
} else {
    // Set response code - 404 Not Found
    http_response_code(404);

    // Inform the user that no current affairs were found
    echo json_encode(
        array("message" => "No current affairs found.")
    );
}

?>