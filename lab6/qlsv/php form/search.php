<?php
// search.php
include "db.php";

// ====== Cấu hình ======
$perPage = 5; // số dòng / trang (đổi tùy bạn)
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// ====== Nhận input lọc/tìm ======
$q        = trim($_GET["q"] ?? "");
$gender   = trim($_GET["gender"] ?? "");
$min_gpa  = trim($_GET["min_gpa"] ?? "");
$max_gpa  = trim($_GET["max_gpa"] ?? "");
$min_age  = trim($_GET["min_age"] ?? "");
$max_age  = trim($_GET["max_age"] ?? "");
$sort     = trim($_GET["sort"] ?? "id_desc");

// ====== Build WHERE + params ======
$where = [];
$params = [];
$types  = "";

// keyword search nhiều cột
if ($q !== "") {
    $where[] = "(name LIKE ? OR email LIKE ? OR student_id LIKE ? OR phone LIKE ? OR address LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like; $types .= "s";
    $params[] = $like; $types .= "s";
    $params[] = $like; $types .= "s";
    $params[] = $like; $types .= "s";
    $params[] = $like; $types .= "s";
}

// gender whitelist
$allowedGenders = ["male", "female", "other"];
if ($gender !== "" && in_array($gender, $allowedGenders, true)) {
    $where[] = "gender = ?";
    $params[] = $gender;
    $types .= "s";
}

// GPA range
if ($min_gpa !== "" && is_numeric($min_gpa)) {
    $where[] = "gpa >= ?";
    $params[] = (float)$min_gpa;
    $types .= "d";
}
if ($max_gpa !== "" && is_numeric($max_gpa)) {
    $where[] = "gpa <= ?";
    $params[] = (float)$max_gpa;
    $types .= "d";
}

// Age range
if ($min_age !== "" && ctype_digit($min_age)) {
    $where[] = "age >= ?";
    $params[] = (int)$min_age;
    $types .= "i";
}
if ($max_age !== "" && ctype_digit($max_age)) {
    $where[] = "age <= ?";
    $params[] = (int)$max_age;
    $types .= "i";
}

$whereSql = "";
if (!empty($where)) {
    $whereSql = " WHERE " . implode(" AND ", $where);
}

// ====== Sort whitelist ======
$sortMap = [
    "id_desc"   => "id DESC",
    "id_asc"    => "id ASC",
    "name_asc"  => "name ASC",
    "name_desc" => "name DESC",
    "gpa_desc"  => "gpa DESC",
    "gpa_asc"   => "gpa ASC",
];
$orderBy = $sortMap[$sort] ?? $sortMap["id_desc"];

// ====== Helper bind params động ======
function bindDynamicParams($stmt, $types, &$params) {
    if ($types === "" || empty($params)) return;
    $bind = [];
    $bind[] = $types;
    foreach ($params as $k => $v) {
        $bind[] = &$params[$k]; // bind_param cần reference
    }
    call_user_func_array([$stmt, "bind_param"], $bind);
}

// ====== Query COUNT ======
$sqlCount = "SELECT COUNT(*) AS total FROM students" . $whereSql;
$stmtCount = $conn->prepare($sqlCount);
if (!$stmtCount) die("SQL lỗi: " . $conn->error);

bindDynamicParams($stmtCount, $types, $params);
$stmtCount->execute();
$total = 0;
$resCount = $stmtCount->get_result();
if ($rowCount = $resCount->fetch_assoc()) {
    $total = (int)$rowCount["total"];
}
$stmtCount->close();

$totalPages = (int)ceil($total / $perPage);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// ====== Query DATA ======
$sqlData = "SELECT * FROM students" . $whereSql . " ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmtData = $conn->prepare($sqlData);
if (!$stmtData) die("SQL lỗi: " . $conn->error);

bindDynamicParams($stmtData, $types, $params);
$stmtData->execute();
$result = $stmtData->get_result();

// ====== Build query string (giữ filter khi bấm trang) ======
$queryParams = $_GET;
unset($queryParams["page"]);
$baseQS = http_build_query($queryParams);
function pageLink($p, $baseQS) {
    $qs = $baseQS ? ($baseQS . "&page=" . $p) : ("page=" . $p);
    return "search.php?" . $qs;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tìm kiếm / Lọc sinh viên</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pagination a, .pagination span { margin: 0 4px; text-decoration: none; }
        .pagination .active { font-weight: bold; }
        .filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 12px; }
        .filters input, .filters select { padding: 6px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

<div class="topbar">
    <h2>Tìm kiếm / Lọc sinh viên</h2>
    <a href="index.php">← Quay về CRUD</a>
</div>

<form method="get" class="filters">
    <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm theo tên/email/MSSV/SĐT/địa chỉ">

    <select name="gender">
        <option value="">-- Giới tính --</option>
        <option value="male"   <?= $gender==="male"?"selected":"" ?>>Nam</option>
        <option value="female" <?= $gender==="female"?"selected":"" ?>>Nữ</option>
        <option value="other"  <?= $gender==="other"?"selected":"" ?>>Khác</option>
    </select>

    <input name="min_age" value="<?= htmlspecialchars($min_age) ?>" placeholder="Tuổi từ" style="width:90px">
    <input name="max_age" value="<?= htmlspecialchars($max_age) ?>" placeholder="đến" style="width:70px">

    <input name="min_gpa" value="<?= htmlspecialchars($min_gpa) ?>" placeholder="GPA từ" style="width:90px">
    <input name="max_gpa" value="<?= htmlspecialchars($max_gpa) ?>" placeholder="đến" style="width:70px">

    <select name="sort">
        <option value="id_desc"   <?= $sort==="id_desc"?"selected":"" ?>>Mới nhất</option>
        <option value="id_asc"    <?= $sort==="id_asc"?"selected":"" ?>>Cũ nhất</option>
        <option value="name_asc"  <?= $sort==="name_asc"?"selected":"" ?>>Tên A→Z</option>
        <option value="name_desc" <?= $sort==="name_desc"?"selected":"" ?>>Tên Z→A</option>
        <option value="gpa_desc"  <?= $sort==="gpa_desc"?"selected":"" ?>>GPA cao→thấp</option>
        <option value="gpa_asc"   <?= $sort==="gpa_asc"?"selected":"" ?>>GPA thấp→cao</option>
    </select>

    <button type="submit">Lọc / Tìm</button>
    <a href="search.php" style="margin-left:8px;">Xóa lọc</a>
</form>

<p>
    Kết quả: <b><?= $total ?></b> sinh viên —
    Trang <b><?= $page ?></b>/<b><?= $totalPages ?></b>
</p>

<table>
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Email</th>
        <th>MSSV</th>
        <th>GPA</th>
        <th>Ảnh</th>
        <th>Hành động</th>
    </tr>

    <?php if ($result->num_rows === 0) { ?>
        <tr><td colspan="7" style="text-align:center;">Không tìm thấy dữ liệu phù hợp.</td></tr>
    <?php } ?>

    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= (int)$row["id"] ?></td>
            <td><?= htmlspecialchars($row["name"] ?? "") ?></td>
            <td><?= htmlspecialchars($row["email"] ?? "") ?></td>
            <td><?= htmlspecialchars($row["student_id"] ?? "") ?></td>
            <td><?= htmlspecialchars($row["gpa"] ?? "") ?></td>
            <td>
                <?php if (!empty($row["avatar"])) { ?>
                    <img src="<?= htmlspecialchars($row["avatar"]) ?>" width="50" alt="avatar">
                <?php } ?>
            </td>
            <td>
                <a href="edit.php?id=<?= (int)$row["id"] ?>">Sửa</a> |
                <a href="delete.php?id=<?= (int)$row["id"] ?>"
                   onclick="return confirm('Xóa sinh viên này?')">Xóa</a>
            </td>
        </tr>
    <?php } ?>
</table>

<div class="pagination" style="margin-top:12px;">
    <?php if ($page > 1) { ?>
        <a href="<?= pageLink(1, $baseQS) ?>">« Đầu</a>
        <a href="<?= pageLink($page-1, $baseQS) ?>">‹ Trước</a>
    <?php } ?>

    <?php
    // Hiển thị 1 cụm trang cho gọn
    $start = max(1, $page - 2);
    $end   = min($totalPages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="'.pageLink($i, $baseQS).'">'.$i.'</a>';
        }
    }
    ?>

    <?php if ($page < $totalPages) { ?>
        <a href="<?= pageLink($page+1, $baseQS) ?>">Sau ›</a>
        <a href="<?= pageLink($totalPages, $baseQS) ?>">Cuối »</a>
    <?php } ?>
</div>

<?php
$stmtData->close();
$conn->close();
?>
</body>
</html>
