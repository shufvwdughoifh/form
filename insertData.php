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

// Insert data into main_table
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $test_id = $_POST['test_id'];
    $score = $_POST['score'];
    $college = $_POST['college'];
    $city = $_POST['city'];
    $state = $_POST['state'];

    $sql = "INSERT INTO main_table (student_id, test_id, score, college, city, state)
    VALUES ('$student_id', '$test_id', $score, '$college', '$city', '$state')";

    if ($conn->query($sql) === TRUE) {
        calculateRanks($conn, $test_id);
        echo "<div class='container mt-5'><h1>New record created successfully!!!</h1></div>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Function to calculate ranks
function calculateRanks($conn, $test_id) {
    // Fetch all scores for the test_id
    $sql = "SELECT * FROM main_table WHERE test_id='$test_id'";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Calculate ranks
    $collegeRank = rankScores($data, 'college');
    $cityRank = rankScores($data, 'city');
    $stateRank = rankScores($data, 'state');
    $allIndiaRank = rankScores($data, null);

    // Store ranks in ranks_table
    foreach ($data as $row) {
        $student_id = $row['student_id'];
        $test_id = $row['test_id'];
        $college = $row['college'];
        $city = $row['city'];
        $state = $row['state'];

        $sql = "INSERT INTO ranks_table (test_id, college_rank, city_rank, state_rank, all_india_rank)
        VALUES ('$test_id', " . $collegeRank[$college] . ", " . $cityRank[$city] . ", " . $stateRank[$state] . ", " . $allIndiaRank[$row['student_id']] . ")
        ON DUPLICATE KEY UPDATE 
        college_rank=" . $collegeRank[$college] . ", 
        city_rank=" . $cityRank[$city] . ", 
        state_rank=" . $stateRank[$state] . ", 
        all_india_rank=" . $allIndiaRank[$row['student_id']];
        
        $conn->query($sql);
    }
}

function rankScores($data, $groupBy) {
    $scores = [];
    foreach ($data as $row) {
        $key = $groupBy ? $row[$groupBy] : $row['student_id'];
        if (!isset($scores[$key])) {
            $scores[$key] = [];
        }
        $scores[$key][] = $row['score'];
    }

    $ranks = [];
    foreach ($scores as $key => $scoreArray) {
        rsort($scoreArray);
        foreach ($scoreArray as $index => $score) {
            $ranks[$key] = $index + 1;
        }
    }

    return $ranks;
}

$conn->close();
?>