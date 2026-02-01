<?php
include "db.php";
include "valid.php";
include "crud.php";

/* ===== KHỞI TẠO ===== */
$errors = [];
$student = [
    "name"       => "",
    "email"      => "",
    "age"        => "",
    "phone"      => "",
    "address"    => "",
    "gpa"        => "",
    "student_id" => "",
    "gender"     => "",
    "avatar"     => ""
];

/* ===== XỬ LÝ SUBMIT ===== */
if (isset($_POST["submit"])) {

    // Lấy dữ liệu form (trừ avatar)
    foreach ($student as $key => $value) {
        if ($key !== "avatar") {
            $student[$key] = $_POST[$key] ?? "";
        }
    }

    // Validate (có check trùng)
    $errors = validateStudent($student, $conn);

    // Upload avatar nếu không có lỗi
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

    // Insert
    if (empty($errors)) {
        insertStudent($student, $conn);
        header("Location: index.php");
        exit;
    }
}

/* ===== LẤY DANH SÁCH ===== */
$result = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CRUD Sinh Viên</title>
    <style>
        .error {
            color: red;
            font-size: 13px
        }
    </style>
</head>

<body>

    <h2>Thêm sinh viên</h2>

    <form method="post" enctype="multipart/form-data">

        <input name="name" placeholder="Tên"
            value="<?= htmlspecialchars($student["name"]) ?>">
        <div class="error"><?= $errors["name"] ?? "" ?></div>

        <input name="email" placeholder="Email"
            value="<?= htmlspecialchars($student["email"]) ?>">
        <div class="error"><?= $errors["email"] ?? "" ?></div>

        <input name="age" placeholder="Tuổi"
            value="<?= htmlspecialchars($student["age"]) ?>">

        <input name="phone" placeholder="SĐT"
            value="<?= htmlspecialchars($student["phone"]) ?>">

        <input name="address" placeholder="Địa chỉ"
            value="<?= htmlspecialchars($student["address"]) ?>">

        <input name="gpa" placeholder="GPA"
            value="<?= htmlspecialchars($student["gpa"]) ?>">
        <div class="error"><?= $errors["gpa"] ?? "" ?></div>

        <input name="student_id" placeholder="MSSV"
            value="<?= htmlspecialchars($student["student_id"]) ?>">
        <div class="error"><?= $errors["student_id"] ?? "" ?></div>

        <select name="gender">
            <option value="">-- Giới tính --</option>
            <option value="male" <?= $student["gender"] == "male" ? "selected" : "" ?>>Nam</option>
            <option value="female" <?= $student["gender"] == "female" ? "selected" : "" ?>>Nữ</option>
            <option value="other" <?= $student["gender"] == "other" ? "selected" : "" ?>>Khác</option>
        </select>

        <input type="file" name="avatar">

        <button name="submit">Lưu</button>
    </form>

    <h2>Danh sách sinh viên</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Ảnh</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row["id"] ?></td>
                <td><?= htmlspecialchars($row["name"]) ?></td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td>
                    <?php if ($row["avatar"]) { ?>
                        <img src="<?= $row["avatar"] ?>" width="50">
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>

    </table>

</body>

</html>