<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_dashboard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $response = [];

    // Fetch student test data
    $sql = "SELECT * FROM main_table WHERE student_id='$student_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $test_id = $row['test_id'];
            $rankSql = "SELECT * FROM ranks_table WHERE test_id='$test_id'";
            $rankResult = $conn->query($rankSql);
            $rankData = $rankResult->fetch_assoc();

            $response[] = [
                'test_id' => $test_id,
                'score' => $row['score'],
                'college_rank' => $rankData['college_rank'],
                'city_rank' => $rankData['city_rank'],
                'state_rank' => $rankData['state_rank'],
                'all_india_rank' => $rankData['all_india_rank']
            ];
        }
    }

    echo json_encode($response);
}

$conn->close();
?>
