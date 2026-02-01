<?php
function validateStudent($data, $conn) {
    $errors = [];

    /* ===== NAME ===== */
    if (empty(trim($data["name"]))) {
        $errors["name"] = "Tên không được rỗng";
    }

    /* ===== EMAIL ===== */
    if (empty(trim($data["email"]))) {
        $errors["email"] = "Email không được rỗng";
    } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Email không hợp lệ";
    } else {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM students WHERE email = ?"
        );
        $stmt->bind_param("s", $data["email"]);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $errors["email"] = "Email đã tồn tại";
        }
    }

    /* ===== MSSV ===== */
    if (!empty(trim($data["student_id"]))) {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM students WHERE student_id = ?"
        );
        $stmt->bind_param("s", $data["student_id"]);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $errors["student_id"] = "MSSV đã tồn tại";
        }
    }

    /* ===== GPA ===== */
    if ($data["gpa"] !== "") {
        if (!is_numeric($data["gpa"]) || $data["gpa"] < 0 || $data["gpa"] > 4) {
            $errors["gpa"] = "GPA phải từ 0 đến 4";
        }
    }

    return $errors;
}
