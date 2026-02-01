<?php
session_start();

$dbHost = "localhost";
$dbName = "qlsv_final";
$dbUser = "root";
$dbPass = "";

function db(): PDO
{
    global $dbHost, $dbName, $dbUser, $dbPass;
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    return new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function test_input($data): string
{
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function gender_label(string $g): string {
    return match ($g) {
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
        default => $g,
    };
}

if (!isset($_SESSION["form_data"])) {
    header("Location: index.php");
    exit;
}

$actionMsg = "";
$actionErr = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int)($_SESSION["form_data"]["id"] ?? 0);

    if ($id <= 0) {
        $actionErr = "Không tìm thấy ID sinh viên trong session.";
    } else {
        try {
            $pdo = db();

            if ($action === "delete") {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
                $stmt->execute([":id" => $id]);

                unset($_SESSION["form_data"], $_SESSION["avail"]);
                header("Location: index.php");
                exit;
            }

            if ($action === "update") {
                $name    = test_input($_POST["name"] ?? "");
                $age     = test_input($_POST["age"] ?? "");
                $phone   = test_input($_POST["phone"] ?? "");
                $address = test_input($_POST["address"] ?? "");
                $gpa     = test_input($_POST["gpa"] ?? "");
                $gender  = test_input($_POST["gender"] ?? "");
                $avatar  = test_input($_POST["avatar"] ?? "");

                if ($name === "") {
                    $actionErr = "Name is required";
                } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                    $actionErr = "Only letters and white space allowed";
                }

                if ($actionErr === "" && $phone === "") {
                    $actionErr = "Phone number is required";
                } elseif ($actionErr === "" && !preg_match("/^0\d{9}$/", $phone) && !preg_match("/^\+84\d{9}$/", $phone)) {
                    $actionErr = "Phone must start with 0 (10 digits) or +84 (11 digits)";
                }

                if ($actionErr === "" && $gender === "") {
                    $actionErr = "Gender is required";
                }

                if ($actionErr === "" && $gpa !== "") {
                    if (!is_numeric($gpa)) {
                        $actionErr = "GPA must be a number";
                    } else {
                        $gpaFloat = (float)$gpa;
                        if ($gpaFloat < 0 || $gpaFloat > 4) {
                            $actionErr = "GPA must be between 0 and 4";
                        }
                    }
                }

                if ($actionErr === "") {
                    $stmt = $pdo->prepare("SELECT 1 FROM students WHERE phone = :phone AND id <> :id LIMIT 1");
                    $stmt->execute([":phone" => $phone, ":id" => $id]);
                    if ($stmt->fetchColumn()) {
                        $actionErr = "sđt đã tồn tại";
                    }
                }

                if ($actionErr === "") {
                    $sql = "UPDATE students
                            SET name = :name,
                                age = :age,
                                phone = :phone,
                                address = :address,
                                gpa = :gpa,
                                gender = :gender,
                                avatar = :avatar
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ":name" => $name,
                        ":age" => $age,
                        ":phone" => $phone,
                        ":address" => $address,
                        ":gpa" => $gpa,
                        ":gender" => $gender,
                        ":avatar" => $avatar,
                        ":id" => $id,
                    ]);

                    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
                    $stmt->execute([":id" => $id]);
                    $fresh = $stmt->fetch();

                    if ($fresh) {
                        $_SESSION["form_data"] = [
                            "id" => $fresh["id"],
                            "name" => $fresh["name"],
                            "email" => $fresh["email"],
                            "age" => $fresh["age"],
                            "phone" => $fresh["phone"],
                            "address" => $fresh["address"],
                            "gpa" => $fresh["gpa"],
                            "student_id" => $fresh["student_id"],
                            "gender" => $fresh["gender"],
                            "avatar" => $fresh["avatar"],
                        ];
                    }

                    $actionMsg = "Cập nhật thành công.";
                }
            }
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'uq_students_phone') || str_contains($msg, 'phone')) {
                $actionErr = "sđt đã tồn tại";
            } else {
                $actionErr = "Lỗi DB: " . $msg;
            }
        } catch (Throwable $e) {
            $actionErr = "Lỗi: " . $e->getMessage();
        }
    }
}

$data = $_SESSION["form_data"];
$avail = $_SESSION["avail"] ?? [];
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

        <?php if (!empty($avail["email"])): ?>
          <p style="color:green;"><strong>Email:</strong> <?= e($avail["email"]) ?></p>
        <?php endif; ?>
        <?php if (!empty($avail["phone"])): ?>
          <p style="color:green;"><strong>SĐT:</strong> <?= e($avail["phone"]) ?></p>
        <?php endif; ?>

        <?php if ($actionMsg): ?>
          <p style="color:green;"><strong><?= e($actionMsg) ?></strong></p>
        <?php endif; ?>
        <?php if ($actionErr): ?>
          <p style="color:red;"><strong><?= e($actionErr) ?></strong></p>
        <?php endif; ?>

        <p><strong>Mã sinh viên:</strong> <?= e($data["student_id"] ?? "") ?></p>
        <p><strong>Email:</strong> <?= e($data["email"] ?? "") ?></p>

        <hr />

        <h3>Cập nhật thông tin</h3>
        <form method="post" action="welcome.php">
          <input type="hidden" name="action" value="update" />

          <p>
            <label>Họ và tên</label><br />
            <input type="text" name="name" required value="<?= e($data["name"] ?? "") ?>" />
          </p>

          <p>
            <label>Tuổi</label><br />
            <input type="number" name="age" min="0" value="<?= e($data["age"] ?? "") ?>" />
          </p>

          <p>
            <label>Số điện thoại</label><br />
            <input
              type="text"
              name="phone"
              placeholder="0xxxxxxxxx hoặc +84xxxxxxxxx"
              required
              value="<?= e($data["phone"] ?? "") ?>"
            />
          </p>

          <p>
            <label>Địa chỉ</label><br />
            <input type="text" name="address" value="<?= e($data["address"] ?? "") ?>" />
          </p>

          <p>
            <label>GPA</label><br />
            <input type="text" name="gpa" placeholder="Ví dụ: 3.2" value="<?= e($data["gpa"] ?? "") ?>" />
          </p>

          <p>
            <label>Avatar (URL/đường dẫn)</label><br />
            <input type="text" name="avatar" value="<?= e($data["avatar"] ?? "") ?>" />
          </p>

          <p>
            <label>Giới tính</label><br />
            <?php $g = $data["gender"] ?? ""; ?>
            <label><input type="radio" name="gender" value="male" required <?= $g==="male" ? "checked" : "" ?> /> Nam</label>
            <label><input type="radio" name="gender" value="female" <?= $g==="female" ? "checked" : "" ?> /> Nữ</label>
            <label><input type="radio" name="gender" value="other" <?= $g==="other" ? "checked" : "" ?> /> Khác</label>
          </p>

          <button type="submit">Update</button>
        </form>

        <hr />

        <h3>Xoá sinh viên</h3>
        <form method="post" action="welcome.php" onsubmit="return confirm('Bạn chắc chắn muốn xoá sinh viên này?');">
          <input type="hidden" name="action" value="delete" />
          <button type="submit" style="background:#b00020;border-color:#b00020;">Delete</button>
        </form>

        <hr />

        <h3>Thông tin hiện tại</h3>
        <p><strong>Họ và tên:</strong> <?= e($data["name"] ?? "") ?></p>
        <p><strong>Tuổi:</strong> <?= e($data["age"] ?? "") ?></p>
        <p><strong>Số điện thoại:</strong> <?= e($data["phone"] ?? "") ?></p>
        <p><strong>Địa chỉ:</strong> <?= e($data["address"] ?? "") ?></p>
        <p><strong>GPA:</strong> <?= e($data["gpa"] ?? "") ?></p>
        <p><strong>Giới tính:</strong> <?= e(gender_label($data["gender"] ?? "")) ?></p>

        <?php if (!empty($data["avatar"])): ?>
          <p><strong>Avatar:</strong></p>
          <img src="<?= e($data["avatar"]) ?>" alt="Avatar" />
        <?php endif; ?>

        <p><a href="index.php">Quay lại form</a></p>
      </section>
    </main>

    <footer>
      <p>© 2026 – Hệ thống quản lý sinh viên</p>
    </footer>
  </body>
</html>
