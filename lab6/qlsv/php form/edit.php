<?php
include "db.php";
include "valid.php";

$id = $_GET["id"];
$result = $conn->query("SELECT * FROM students WHERE id=$id");
$student = $result->fetch_assoc();
$errors = [];

if (isset($_POST["update"])) {

    $student = array_merge($student, $_POST);
    $errors = validateStudent($student);

    // Upload ảnh mới
    if (!empty($_FILES["avatar"]["name"])) {
        if ($student["avatar"] && file_exists($student["avatar"])) {
            unlink($student["avatar"]);
        }

        $filename = time() . "_" . $_FILES["avatar"]["name"];
        move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/" . $filename);
        $student["avatar"] = "uploads/" . $filename;
    }

    if (empty($errors)) {
        $sql = "UPDATE students SET
            name=?, email=?, age=?, phone=?, address=?, gpa=?, student_id=?, gender=?, avatar=?
            WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissdsssi",
            $student["name"],
            $student["email"],
            $student["age"],
            $student["phone"],
            $student["address"],
            $student["gpa"],
            $student["student_id"],
            $student["gender"],
            $student["avatar"],
            $id
        );
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Cập nhật sinh viên</h2>

<form method="post" enctype="multipart/form-data">
    <input name="name" value="<?= $student["name"] ?>">
    <input name="email" value="<?= $student["email"] ?>">
    <input name="age" value="<?= $student["age"] ?>">
    <input name="phone" value="<?= $student["phone"] ?>">
    <input name="address" value="<?= $student["address"] ?>">
    <input name="gpa" value="<?= $student["gpa"] ?>">
    <input name="student_id" value="<?= $student["student_id"] ?>">

    <select name="gender">
        <option value="male" <?= $student["gender"]=="male"?"selected":"" ?>>Nam</option>
        <option value="female" <?= $student["gender"]=="female"?"selected":"" ?>>Nữ</option>
        <option value="other" <?= $student["gender"]=="other"?"selected":"" ?>>Khác</option>
    </select>

    <input type="file" name="avatar">

    <button name="update">Cập nhật</button>
</form>

</body>
</html>
