<?php
include "db.php";
include "crud.php";

$result = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CRUD Sinh Viên</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Thêm sinh viên</h2>

<form method="post" enctype="multipart/form-data">
    <input name="name" placeholder="Tên">
    <span><?= $errors["name"] ?? "" ?></span>

    <input name="email" placeholder="Email">
    <span><?= $errors["email"] ?? "" ?></span>

    <input name="age" placeholder="Tuổi">
    <input name="phone" placeholder="SĐT">
    <input name="address" placeholder="Địa chỉ">
    <input name="gpa" placeholder="GPA">
    <input name="student_id" placeholder="MSSV">

    <select name="gender">
        <option value="">-- Giới tính --</option>
        <option value="male">Nam</option>
        <option value="female">Nữ</option>
        <option value="other">Khác</option>
    </select>

    <input type="file" name="avatar">

    <button name="submit">Lưu</button>
</form>

<h2>Danh sách sinh viên</h2>

<table>
<tr>
    <th>ID</th>
    <th>Tên</th>
    <th>Email</th>
    <th>Ảnh</th>
    <th>Hành động</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?= $row["id"] ?></td>
    <td><?= $row["name"] ?></td>
    <td><?= $row["email"] ?></td>
    <td>
        <?php if ($row["avatar"]) { ?>
            <img src="<?= $row["avatar"] ?>" width="50">
        <?php } ?>
    </td>
    <td>
        <a href="edit.php?id=<?= $row["id"] ?>">Sửa</a> |
        <a href="delete.php?id=<?= $row["id"] ?>"
           onclick="return confirm('Xóa sinh viên này?')">Xóa</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
