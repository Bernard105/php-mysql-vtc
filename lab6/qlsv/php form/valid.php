<?php
// valid.php
function validateStudent($data) {
    $errors = [];

    if (empty($data["name"])) {
        $errors["name"] = "Tên không được rỗng";
    }

    if (empty($data["email"])) {
        $errors["email"] = "Email không được rỗng";
    } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Email không hợp lệ";
    }

    if (!empty($data["gpa"]) && ($data["gpa"] < 0 || $data["gpa"] > 4)) {
        $errors["gpa"] = "GPA phải từ 0 đến 4";
    }

    return $errors;
}
