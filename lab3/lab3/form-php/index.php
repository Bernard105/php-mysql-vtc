<?php
session_start();

require_once __DIR__ . '/schema.php';

$nameErr = $emailErr = $genderErr = $phoneErr = $gpaErr = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

function test_input($data): string
{
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function generate_student_id(): string
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    return '1005' . date('y') . date('dm') . date('Hi');
}

function get_next_id(string $filePath): int
{
    if (!file_exists($filePath)) {
        return 1;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false || count($lines) === 0) {
        return 1;
    }

    $maxId = 0;

    foreach ($lines as $line) {
        $row = json_decode($line, true);
        if (is_array($row) && isset($row['id']) && is_numeric($row['id'])) {
            $id = (int)$row['id'];
            if ($id > $maxId) $maxId = $id;
        }
    }

    return $maxId + 1;
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
} elseif (
    !preg_match("/^0\d{9}$/", $phone) &&
    !preg_match("/^\+84\d{9}$/", $phone)
) {
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
        "name"   => $nameErr,
        "email"  => $emailErr,
        "phone"  => $phoneErr,
        "gender" => $genderErr,
        "gpa"    => $gpaErr,
    ];

    $_SESSION["old"] = [
        "name"    => $name,
        "email"   => $email,
        "age"     => $age,
        "phone"   => $phone,
        "address" => $address,
        "gpa"     => $gpa,
        "gender"  => $gender,
        "avatar"  => $avatar,
    ];

    header("Location: index.html");
    exit;
}

$student_id = generate_student_id();

$filePath = __DIR__ . '/file.txt';

$nextId = get_next_id($filePath);

$studentData = apply_schema($studentSchema, [
    "id"         => $nextId,
    "name"       => $name,
    "email"      => $email,
    "age"        => $age,
    "phone"      => $phone,
    "address"    => $address,
    "gpa"        => $gpa,
    "student_id" => $student_id,
    "gender"     => $gender,
    "avatar"     => $avatar,
]);

$line = json_encode($studentData, JSON_UNESCAPED_UNICODE) . PHP_EOL;

if (file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX) === false) {
    $_SESSION["write_error"] = "Không ghi được file.txt. Kiểm tra quyền ghi (permission) hoặc đường dẫn.";
} else {
    unset($_SESSION["write_error"]);
}

$_SESSION["form_data"] = $studentData;

unset($_SESSION["errors"], $_SESSION["old"]);

header("Location: welcome.php");
exit;
