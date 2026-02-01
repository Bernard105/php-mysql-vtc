<?php
include "db.php";

$id = $_GET["id"];
$result = $conn->query("SELECT avatar FROM students WHERE id=$id");
$row = $result->fetch_assoc();

if ($row["avatar"] && file_exists($row["avatar"])) {
    unlink($row["avatar"]);
}

$conn->query("DELETE FROM students WHERE id=$id");
header("Location: index.php");
exit;
?>