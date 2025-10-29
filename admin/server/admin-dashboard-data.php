<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require __DIR__ . "/../../assets/config/dbconfig.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_uplug";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Ensure Manila timezone
mysqli_query($conn, "SET time_zone = '+08:00'");

$response = [
    "debug" => [],
    "posts" => [],
    "users" => []
];

$response["debug"][] = "Connected to database";

// --- Fetch posts today ---
$start = date('Y-m-d 00:00:00');
$end   = date('Y-m-d 23:59:59');

// Get posts first
$postSql = "
    SELECT 
        post_id,
        author_id,
        post_type,
        title,
        content,
        create_date,
        edited_at,
        author_department AS department
    FROM posts
    WHERE create_date BETWEEN ? AND ?
    ORDER BY create_date DESC
";

$stmt = $conn->prepare($postSql);
if ($stmt) {
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {

        // Determine author full name
        $authorId = $row['author_id'];
        $fullName = null;

        if (strpos($authorId, 'STU-') === 0) {
            $stuStmt = $conn->prepare("SELECT full_name FROM student_users WHERE student_id = ?");
            if ($stuStmt) {
                $stuStmt->bind_param("s", $authorId);
                $stuStmt->execute();
                $stuResult = $stuStmt->get_result();
                if ($stuRow = $stuResult->fetch_assoc()) {
                    $fullName = $stuRow['full_name'];
                }
                $stuStmt->close();
            }
        } elseif (strpos($authorId, 'FAC-') === 0) {
            $facStmt = $conn->prepare("SELECT full_name FROM faculty_users WHERE faculty_id = ?");
            if ($facStmt) {
                $facStmt->bind_param("s", $authorId);
                $facStmt->execute();
                $facResult = $facStmt->get_result();
                if ($facRow = $facResult->fetch_assoc()) {
                    $fullName = $facRow['full_name'];
                }
                $facStmt->close();
            }
        }

        $row['author_name'] = $fullName; // Add full name to row
        $response["posts"][] = $row;
    }
    $stmt->close();
    $response["debug"][] = "Posts fetched: " . count($response["posts"]);
} else {
    $response["debug"][] = "Post query failed: " . $conn->error;
}

// --- Fetch new users registered in last 24 hours ---
// Time 24 hours ago
$yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

// --- Students ---
$stuSql = "SELECT student_id AS id, full_name, email, create_date, department FROM student_users WHERE create_date >= ?";
$stuStmt = $conn->prepare($stuSql);
if ($stuStmt) {
    $stuStmt->bind_param("s", $yesterday);
    $stuStmt->execute();
    $stuResult = $stuStmt->get_result();
    while ($row = $stuResult->fetch_assoc()) {
        $row['type'] = 'student';
        $row['department'] = $row['department'] ?? '';
        $row['name'] = $row['full_name']; // always add 'name'
        unset($row['full_name']);         // remove 'full_name' to avoid confusion
        $response["users"][] = $row;
    }
    $stuStmt->close();
}

// --- Faculty ---
$facSql = "SELECT faculty_id AS id, full_name, email, create_date, department FROM faculty_users WHERE create_date >= ?";
$facStmt = $conn->prepare($facSql);
if ($facStmt) {
    $facStmt->bind_param("s", $yesterday);
    $facStmt->execute();
    $facResult = $facStmt->get_result();
    while ($row = $facResult->fetch_assoc()) {
        $row['type'] = 'faculty';
        $row['department'] = $row['department'] ?? '';
        $row['name'] = $row['full_name']; // always add 'name'
        unset($row['full_name']);
        $response["users"][] = $row;
    }
    $facStmt->close();
}


$response["debug"][] = "New users fetched: " . count($response["users"]);

mysqli_close($conn);

echo json_encode($response);
?>
