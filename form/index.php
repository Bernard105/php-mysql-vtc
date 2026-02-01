<?php
session_start();
require_once __DIR__ . '/schema.php';

$dbHost = "localhost";
$dbName = "qlsv_final";
$dbUser = "root";
$dbPass = "";

function e($str): string
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function test_input($data): string
{
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// 1005 + 2 số cuối năm + ngàytháng + giờphút
function generate_student_id(): string
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    return '1005' . date('y') . date('dm') . date('Hi');
}

function apply_schema(array $schema, array $data): array
{
    $result = $schema;
    foreach ($schema as $key => $_) {
        if (array_key_exists($key, $data)) {
            $result[$key] = $data[$key];
        }
    }
    return $result;
}

function db(): PDO
{
    global $dbHost, $dbName, $dbUser, $dbPass;
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    return new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $errors = $_SESSION["errors"] ?? [];
    $old    = $_SESSION["old"] ?? [];
    $avail  = $_SESSION["avail"] ?? [];

    unset($_SESSION["errors"], $_SESSION["old"], $_SESSION["avail"]);

    $g = $old["gender"] ?? "";
?>
    <!doctype html>
    <html lang="vi">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Đăng ký sinh viên</title>
        <link rel="stylesheet" href="style.css" />
    </head>

    <body>
        <header>
            <h1>Hệ Thống Quản Lý Sinh Viên</h1>
        </header>

        <main>
            <section>
                <h2>Đăng ký sinh viên</h2>

                <form method="post" action="index.php">
                    <p>
                        <label>Họ và tên</label><br />
                        <input type="text" name="name" required value="<?= e($old["name"] ?? "") ?>" />
                        <?php if (!empty($errors["name"])): ?>
                            <br><small style="color:red;"><?= e($errors["name"]) ?></small>
                        <?php endif; ?>
                    </p>

                    <p>
                        <label>Email</label><br />
                        <input type="email" name="email" required value="<?= e($old["email"] ?? "") ?>" />
                        <?php if (!empty($errors["email"])): ?>
                            <br><small style="color:red;"><?= e($errors["email"]) ?></small>
                        <?php elseif (!empty($avail["email"])): ?>
                            <br><small style="color:green;"><?= e($avail["email"]) ?></small>
                        <?php endif; ?>
                    </p>

                    <p>
                        <label>Tuổi</label><br />
                        <input type="number" name="age" min="0" value="<?= e($old["age"] ?? "") ?>" />
                    </p>

                    <p>
                        <label>Số điện thoại</label><br />
                        <input
                            type="text"
                            name="phone"
                            placeholder="0xxxxxxxxx hoặc +84xxxxxxxxx"
                            required
                            value="<?= e($old["phone"] ?? "") ?>" />
                        <?php if (!empty($errors["phone"])): ?>
                            <br><small style="color:red;"><?= e($errors["phone"]) ?></small>
                        <?php elseif (!empty($avail["phone"])): ?>
                            <br><small style="color:green;"><?= e($avail["phone"]) ?></small>
                        <?php endif; ?>
                    </p>

                    <p>
                        <label>Địa chỉ</label><br />
                        <input type="text" name="address" value="<?= e($old["address"] ?? "") ?>" />
                    </p>

                    <p>
                        <label>GPA</label><br />
                        <input type="text" name="gpa" placeholder="Ví dụ: 3.2" value="<?= e($old["gpa"] ?? "") ?>" />
                        <?php if (!empty($errors["gpa"])): ?>
                            <br><small style="color:red;"><?= e($errors["gpa"]) ?></small>
                        <?php endif; ?>
                    </p>

                    <p>
                        <label>Mã sinh viên</label><br />
                        <small>Mã sinh viên sẽ được hệ thống tự tạo sau khi bạn đăng ký.</small>
                    </p>

                    <p>
                        <label>Avatar (URL/đường dẫn)</label><br />
                        <input
                            type="text"
                            name="avatar"
                            placeholder="Ví dụ: https://... hoặc images/avatar.png"
                            value="<?= e($old["avatar"] ?? "") ?>" />
                    </p>

                    <p>
                        <label>Giới tính</label><br />
                        <label><input type="radio" name="gender" value="male" required <?= $g === "male" ? "checked" : "" ?> /> Nam</label>
                        <label><input type="radio" name="gender" value="female" <?= $g === "female" ? "checked" : "" ?> /> Nữ</label>
                        <label><input type="radio" name="gender" value="other" <?= $g === "other" ? "checked" : "" ?> /> Khác</label>
                        <?php if (!empty($errors["gender"])): ?>
                            <br><small style="color:red;"><?= e($errors["gender"]) ?></small>
                        <?php endif; ?>
                    </p>

                    <button type="submit">Đăng ký</button>
                </form>
            </section>
        </main>

        <footer>
            <p>© 2026 – Hệ thống quản lý sinh viên</p>
        </footer>
    </body>

    </html>
<?php
    exit;
}

$nameErr = $emailErr = $genderErr = $phoneErr = $gpaErr = "";

$name    = test_input($_POST["name"] ?? "");
$email   = test_input($_POST["email"] ?? "");
$age     = test_input($_POST["age"] ?? "");
$phone   = test_input($_POST["phone"] ?? "");
$address = test_input($_POST["address"] ?? "");
$gpa     = test_input($_POST["gpa"] ?? "");
$gender  = test_input($_POST["gender"] ?? "");
$avatar  = test_input($_POST["avatar"] ?? "");

if ($name === "") {
    $nameErr = "Name is required";
} elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
    $nameErr = "Only letters and white space allowed";
}

if ($email === "") {
    $emailErr = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailErr = "Invalid email format";
}

if ($phone === "") {
    $phoneErr = "Phone number is required";
} elseif (!preg_match("/^0\d{9}$/", $phone) && !preg_match("/^\+84\d{9}$/", $phone)) {
    $phoneErr = "Phone must start with 0 (10 digits) or +84 (11 digits)";
}

if ($gender === "") {
    $genderErr = "Gender is required";
}

if ($gpa !== "") {
    if (!is_numeric($gpa)) {
        $gpaErr = "GPA must be a number";
    } else {
        $gpaFloat = (float)$gpa;
        if ($gpaFloat < 0 || $gpaFloat > 4) {
            $gpaErr = "GPA must be between 0 and 4";
        }
    }
}

if ($nameErr || $emailErr || $genderErr || $phoneErr || $gpaErr) {
    $_SESSION["errors"] = [
        "name" => $nameErr,
        "email" => $emailErr,
        "phone" => $phoneErr,
        "gender" => $genderErr,
        "gpa" => $gpaErr,
    ];
    $_SESSION["old"] = [
        "name" => $name,
        "email" => $email,
        "age" => $age,
        "phone" => $phone,
        "address" => $address,
        "gpa" => $gpa,
        "gender" => $gender,
        "avatar" => $avatar,
    ];
    header("Location: index.php");
    exit;
}

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT 1 FROM students WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);
    $emailExists = (bool)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT 1 FROM students WHERE phone = :phone LIMIT 1");
    $stmt->execute([":phone" => $phone]);
    $phoneExists = (bool)$stmt->fetchColumn();

    if ($emailExists) $emailErr = "email đã tồn tại";
    if ($phoneExists) $phoneErr = "sđt đã tồn tại";

    if ($emailErr || $phoneErr) {
        $_SESSION["errors"] = [
            "email" => $emailErr,
            "phone" => $phoneErr,
        ];
        $_SESSION["old"] = [
            "name" => $name,
            "email" => $email,
            "age" => $age,
            "phone" => $phone,
            "address" => $address,
            "gpa" => $gpa,
            "gender" => $gender,
            "avatar" => $avatar,
        ];
        header("Location: index.php");
        exit;
    }

    $_SESSION["avail"] = [
        "email" => "email khả dụng",
        "phone" => "sđt khả dụng",
    ];
} catch (Throwable $e) {
    $_SESSION["errors"] = [
        "email" => "Không kiểm tra được DB: " . $e->getMessage(),
    ];
    $_SESSION["old"] = [
        "name" => $name,
        "email" => $email,
        "age" => $age,
        "phone" => $phone,
        "address" => $address,
        "gpa" => $gpa,
        "gender" => $gender,
        "avatar" => $avatar,
    ];
    header("Location: index.php");
    exit;
}

$student_id = generate_student_id();

$studentData = apply_schema($studentSchema, [
    "id" => null,
    "name" => $name,
    "email" => $email,
    "age" => $age,
    "phone" => $phone,
    "address" => $address,
    "gpa" => $gpa,
    "student_id" => $student_id,
    "gender" => $gender,
    "avatar" => $avatar,
]);

try {
    $pdo = db();
    $sql = "INSERT INTO students (name, email, age, phone, address, gpa, student_id, gender, avatar)
            VALUES (:name, :email, :age, :phone, :address, :gpa, :student_id, :gender, :avatar)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":name" => $studentData["name"],
        ":email" => $studentData["email"],
        ":age" => $studentData["age"],
        ":phone" => $studentData["phone"],
        ":address" => $studentData["address"],
        ":gpa" => $studentData["gpa"],
        ":student_id" => $studentData["student_id"],
        ":gender" => $studentData["gender"],
        ":avatar" => $studentData["avatar"],
    ]);

    $studentData["id"] = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    $msg = $e->getMessage();

    $emailErr = (str_contains($msg, 'uq_students_email') || str_contains($msg, 'email')) ? "email đã tồn tại" : "";
    $phoneErr = (str_contains($msg, 'uq_students_phone') || str_contains($msg, 'phone')) ? "sđt đã tồn tại" : "";

    $_SESSION["errors"] = [
        "email" => $emailErr ?: ("Không lưu DB: " . $msg),
        "phone" => $phoneErr,
    ];
    $_SESSION["old"] = [
        "name" => $name,
        "email" => $email,
        "age" => $age,
        "phone" => $phone,
        "address" => $address,
        "gpa" => $gpa,
        "gender" => $gender,
        "avatar" => $avatar,
    ];
    header("Location: index.php");
    exit;
}

$_SESSION["form_data"] = $studentData;

header("Location: welcome.php");
exit;
