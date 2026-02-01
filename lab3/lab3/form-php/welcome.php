<?php
session_start();

if (!isset($_SESSION["form_data"])) {
    header("Location: index.html");
    exit;
}

$data = $_SESSION["form_data"];

function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function gender_label($g): string {
    return match ($g) {
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
        default => (string)$g,
    };
}
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thông tin sinh viên</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <header>
      <h1>Hệ Thống Quản Lý Sinh Viên</h1>
    </header>

    <main>
      <section>
        <h2>Đăng ký thành công</h2>

        <?php if (!empty($_SESSION["write_error"])): ?>
          <p style="color: red;">
            <strong>Cảnh báo:</strong> <?= e($_SESSION["write_error"]) ?>
          </p>
        <?php else: ?>
          <p style="color: green;">
            Dữ liệu đã được lưu vào <strong>file.txt</strong>.
          </p>
        <?php endif; ?>

        <p><strong>Mã sinh viên:</strong> <?= e($data["student_id"] ?? "") ?></p>
        <p><strong>Họ và tên:</strong> <?= e($data["name"] ?? "") ?></p>
        <p><strong>Email:</strong> <?= e($data["email"] ?? "") ?></p>
        <p><strong>Tuổi:</strong> <?= e($data["age"] ?? "") ?></p>
        <p><strong>Số điện thoại:</strong> <?= e($data["phone"] ?? "") ?></p>
        <p><strong>Địa chỉ:</strong> <?= e($data["address"] ?? "") ?></p>
        <p><strong>GPA:</strong> <?= e($data["gpa"] ?? "") ?></p>
        <p><strong>Giới tính:</strong> <?= e(gender_label($data["gender"] ?? "")) ?></p>

        <?php if (!empty($data["avatar"])): ?>
          <p><strong>Avatar:</strong></p>
          <img
            src="<?= e($data["avatar"]) ?>"
            alt="Avatar"
            style="max-width: 160px; height: auto;"
          />
        <?php endif; ?>

        <p><a href="index.html">Quay lại form</a></p>
      </section>
    </main>

    <footer>
      <p>© 2026 – Hệ thống quản lý sinh viên</p>
    </footer>
  </body>
</html>
