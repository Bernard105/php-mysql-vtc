<?php
//mục đích: tối ưu performance + bảo mật + code gọn hơn (ít lặp) cho trang list student
//em thấy code cũ bị:
// - SQL select bị lặp 2 nhánh (có/không gender)
// - phân trang đang hardcode (1-2-3) -> data nhiều là sai
// - link filter/phan trang đôi khi mất params nếu không build chuẩn
// - edit.php truyền thêm name lên URL (không cần thiết) -> dài link + dễ lỗi ký tự + lộ data
//nên em tối ưu:
// - gom điều kiện WHERE vào 1 chỗ rồi bind param động
// - query COUNT(*) để ra tổng trang và render pagination theo tổng record
// - buildUrl() dùng http_build_query để giữ params sạch
// - edit chỉ cần id, name lấy từ DB ở edit.php là đủ

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "student_management_db_25";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

//mục đích: đảm bảo encoding đúng (tiếng Việt không lỗi) + an toàn khi so sánh chuỗi
//tại sao phải làm vậy? nếu không set utf8mb4 thì dữ liệu unicode/emoji có thể lỗi khi lưu/đọc
$conn->set_charset("utf8mb4");

const LIMIT = 10;

//mục đích: validate input từ URL cho chắc chắn, tránh user nhập bậy làm sai logic
//em thấy code cũ lấy $_GET trực tiếp -> dễ dính page=abc, gender=999, keyword có khoảng trắng rác...
//nên em dùng filter_input + ép kiểu + chặn range hợp lệ
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
  'options' => ['default' => 1, 'min_range' => 1]
]);

$gender = filter_input(INPUT_GET, 'gender', FILTER_VALIDATE_INT, [
  'options' => ['default' => null]
]);
//chỉ nhận 1/2/3, còn lại coi như không lọc
if (!in_array($gender, [1,2,3], true)) $gender = null;

$keyword = filter_input(INPUT_GET, 'keyword', FILTER_UNSAFE_RAW);
$keyword = trim($keyword ?? "Thị");

//offset tính từ page đã validate
$offset = ($page - 1) * LIMIT;

//mục đích: build WHERE linh hoạt để khỏi lặp code SQL
//em thấy code cũ phải if/else 2 câu SQL gần giống nhau -> maintain mệt
//nên em gom điều kiện vào mảng, có gì thì add vào
$where = [];
$types = "";
$values = [];

//luôn có keyword search (LIKE)
$where[] = "name LIKE ?";
$types  .= "s";
$values[] = "%{$keyword}%";

if ($gender !== null) {
  $where[] = "gender = ?";
  $types  .= "i";
  $values[] = $gender;
}

$whereSql = "WHERE " . implode(" AND ", $where);

//mục đích: lấy tổng record để phân trang đúng theo data thật (không hardcode 1-2-3)
//tại sao phải làm vậy? nếu data 200 record mà chỉ show 1-2-3 thì user không đi tiếp được
$sqlCount = "SELECT COUNT(*) AS total FROM students $whereSql";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param($types, ...$values);
$stmtCount->execute();
$totalRows = (int)$stmtCount->get_result()->fetch_assoc()['total'];
$stmtCount->close();

$totalPages = max(1, (int)ceil($totalRows / LIMIT));

//mục đích: nếu user cố tình nhập page quá lớn (vd page=999) thì kéo về trang cuối để khỏi query vô nghĩa
//tại sao phải làm vậy? query offset quá lớn vừa tốn, vừa trả empty làm UX tệ
if ($page > $totalPages) {
  $page = $totalPages;
  $offset = ($page - 1) * LIMIT;
}

//mục đích: query list với prepared statement + bind động (an toàn + nhanh)
//tại sao phải làm vậy? tránh SQL injection + không bị vỡ query khi keyword có dấu nháy
$sqlList = "SELECT id, name, gender
            FROM students
            $whereSql
            ORDER BY id DESC
            LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sqlList);

//thêm LIMIT/OFFSET vào bind (ii)
$typesList = $types . "ii";
$valuesList = array_merge($values, [LIMIT, $offset]);

$stmt->bind_param($typesList, ...$valuesList);
$stmt->execute();
$result = $stmt->get_result();

//mục đích: build URL giữ params sạch và tái sử dụng (filter/pagination khỏi viết tay)
//em thấy code cũ build link lắt nhắt -> dễ quên gender/keyword khi bấm page
function buildUrl(array $changes = []): string {
  $params = $_GET;
  foreach ($changes as $k => $v) {
    if ($v === null || $v === '') unset($params[$k]);
    else $params[$k] = $v;
  }
  return "index.php?" . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Management</title>
  <style>
    .button, .button-gender {
      background-color: #4CAF50;
      border: none;
      color: white;
      padding: 10px 20px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 16px;
      margin: 4px 2px;
      cursor: pointer;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <h1>Student management application</h1>
  <a href="form.php">Add New Student</a>
  <br><br>

  Bộ lọc: Giới tính

  <!-- mục đích: filter gender nhưng vẫn giữ keyword/page đúng cách
       em thấy code cũ bấm filter có thể làm mất keyword hoặc phải tự sửa URL
       nên em dùng buildUrl() để chỉ thay gender, còn params khác giữ nguyên
  -->
  <a class="button-gender" href="<?= buildUrl(['gender' => 1, 'page' => 1]) ?>">Nam</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => 2, 'page' => 1]) ?>">Nữ</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => 3, 'page' => 1]) ?>">Khác</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => null, 'page' => 1]) ?>">Tất cả</a>

  <br><br>

  <!-- mục đích: hiển thị keyword hiện tại (debug/UX) + chuẩn bị cho form search sau này -->
  <div>Keyword: <b><?= htmlspecialchars($keyword, ENT_QUOTES, "UTF-8") ?></b></div>
  <div>Total: <b><?= $totalRows ?></b> students</div>
  <br>

<?php
//mục đích: in HTML an toàn (chống XSS) + link edit gọn
//em thấy code cũ truyền name lên URL vừa dài vừa không cần thiết
//tại sao phải bỏ? edit.php chỉ cần id, còn name lấy từ DB theo id là chuẩn nhất
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $g  = (int)$row['gender'];
    $name = htmlspecialchars($row['name'], ENT_QUOTES, "UTF-8");

    echo "id: {$id} - Name: {$name} - Gender: {$g} ";
    echo "<a href='edit.php?id={$id}'>Edit</a><br>";
  }
} else {
  echo "0 results";
}

$stmt->close();
$conn->close();
?>

  <!-- mục đích: phân trang theo tổng trang thật + vẫn giữ filter/keyword
       em thấy code cũ hardcode 1-2-3 -> data nhiều là không đi tiếp được
       nên em render từ 1..totalPages, đồng thời highlight trang hiện tại
  -->
  <div class="pagination">
    <?php
      //mục đích: tránh render quá dài nếu totalPages lớn (performance/UX)
      //em chỉ show cửa sổ trang quanh current (vd current±2) + 1 + last
      $window = 2;
      $start = max(1, $page - $window);
      $end   = min($totalPages, $page + $window);

      //luôn show trang 1
      echo "<a class='button' href='" . buildUrl(['page' => 1]) . "'>1</a>";

      if ($start > 2) echo "<span> ... </span>";

      for ($p = $start; $p <= $end; $p++) {
        if ($p === 1 || $p === $totalPages) continue;

        //highlight trang hiện tại bằng cách disable click nhẹ (ở đây giữ đơn giản)
        $label = ($p === $page) ? "<b>{$p}</b>" : (string)$p;
        echo "<a class='button' href='" . buildUrl(['page' => $p]) . "'>{$label}</a>";
      }

      if ($end < $totalPages - 1) echo "<span> ... </span>";

      if ($totalPages > 1) {
        echo "<a class='button' href='" . buildUrl(['page' => $totalPages]) . "'>{$totalPages}</a>";
      }
    ?>
  </div>
</body>
</html>
