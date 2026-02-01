<?php
include "db.php";
include "schema.php";
include "valid.php";

function insertStudent($student, $conn) {
    $sql = "INSERT INTO students
        (name,email,age,phone,address,gpa,student_id,gender,avatar)
        VALUES (?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssissdsss",
        $student["name"],
        $student["email"],
        $student["age"],
        $student["phone"],
        $student["address"],
        $student["gpa"],
        $student["student_id"],
        $student["gender"],
        $student["avatar"]
    );

    $stmt->execute();
}

$errors = [];
$student = $studentSchema;

if (isset($_POST["submit"])) {

    foreach ($student as $key => $value) {
        if ($key !== "id" && $key !== "avatar") {
            $student[$key] = $_POST[$key] ?? "";
        }
    }

    $errors = validateStudent($student, $conn);

    if (empty($errors) && !empty($_FILES["avatar"]["name"])) {

        if (!is_dir("uploads")) {
            mkdir("uploads");
        }

        $filename = time() . "_" . $_FILES["avatar"]["name"];
        move_uploaded_file(
            $_FILES["avatar"]["tmp_name"],
            "uploads/" . $filename
        );

        $student["avatar"] = "uploads/" . $filename;
    }

    if (empty($errors)) {
        insertStudent($student, $conn);
        header("Location: index.php");
        exit;
    }
}
