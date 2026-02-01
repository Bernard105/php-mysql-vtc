<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u23";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM dsct";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . "<br>";
        echo "Name: " . $row["name"] . "<br>";
        echo "Position: " . $row["position"] . "<br>";
        echo "Value: " . $row["value"] . "<br>";
        echo "Image: " . $row["img"] . "<hr>"; // de hinh vao thang
    }
} else {
    echo "Không có dữ liệu";
}

$conn->close();
?>
