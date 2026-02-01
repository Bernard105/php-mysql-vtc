<?php
//mục đích: tối ưu performance khi dữ liệu lớn (tránh COUNT và OFFSET), vẫn giữ filter/search/pagination
//em thấy code trước dù đã clean + prepared statement rồi nhưng vẫn còn 2 chỗ cực chậm khi data nhiều:
// - COUNT(*) mỗi lần load trang (đặc biệt kèm LIKE) => nặng DB
// - OFFSET pagination: page càng về sau OFFSET càng lớn => MySQL phải scan bỏ qua rất nhiều dòng
//nên em tối ưu tiếp bằng:
// - cursor pagination (keyset): dựa vào id để lấy trang tiếp theo, DB chạy rất nhanh nhờ index
// - LIMIT + 1 để biết còn trang sau không (khỏi COUNT)
// - search đổi sang prefix (keyword%) để có thể dùng index (còn %keyword% thì index hầu như vô dụng)

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "student_management_db_25";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

//mục đích: set utf8mb4 để tiếng Việt/emoji không lỗi + dữ liệu đọc/ghi ổn định
$conn->set_charset("utf8mb4");

const LIMIT = 10;

//mục đích: validate input URL để tránh user nhập bậy làm sai logic
$gender = filter_input(INPUT_GET, 'gender', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
if (!in_array($gender, [1,2,3], true)) $gender = null;

$keyword = trim(filter_input(INPUT_GET, 'keyword', FILTER_UNSAFE_RAW) ?? "");

//mục đích: cursor pagination thay OFFSET
//em dùng cursor là id cuối của trang trước (trang sau sẽ lấy id < cursor vì sort DESC)
//tại sao phải làm vậy? OFFSET lớn sẽ chậm dần theo page, còn id < cursor thì DB nhảy thẳng bằng index
$cursor = filter_input(INPUT_GET, 'cursor', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
if ($cursor !== null && $cursor < 1) $cursor = null;

/* ===================== BUILD WHERE + PARAMS (KHÔNG LẶP SQL) ===================== */
//mục đích: gom điều kiện WHERE vào 1 chỗ, bind động, khỏi if/else lặp query
$where = [];
$types = "";
$params = [];

//lọc theo gender nếu có
if ($gender !== null) {
  $where[] = "gender = ?";
  $types  .= "i";
  $params[] = $gender;
}

//search theo name nếu có keyword
//mục đích: dùng prefix search keyword% để DB có thể dùng index(name)
//tại sao phải làm vậy? %keyword% thường không dùng được index => chậm
if ($keyword !== "") {
  $where[] = "name LIKE ?";
  $types  .= "s";
  $params[] = $keyword . "%";
}

//cursor: chỉ lấy record có id < cursor (vì order by id DESC)
//mục đích: keyset pagination
if ($cursor !== null) {
  $where[] = "id < ?";
  $types  .= "i";
  $params[] = $cursor;
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

/* ===================== QUERY LIST ===================== */
//mục đích: lấy LIMIT+1 để biết có trang tiếp theo không
//tại sao phải làm vậy? để không cần COUNT(*) mà vẫn biết có next page
$sql = "SELECT id, name, gender
        FROM students
        $whereSql
        ORDER BY id DESC
        LIMIT ?";

$typesList = $types . "i";
$paramsList = array_merge($params, [LIMIT + 1]);

$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);

//mục đích: bind_param cho số lượng params động
//tại sao phải làm vậy? query filter/search/cursor thay đổi params theo từng request
bindDynamic($stmt, $typesList, $paramsList);

$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;

$stmt->close();
$conn->close();

//mục đích: xác định có next page bằng LIMIT+1
$hasNext = count($rows) > LIMIT;
if ($hasNext) array_pop($rows); //bỏ dòng dư ra (dòng +1)

//cursor cho trang tiếp theo là id nhỏ nhất trong trang hiện tại (vì đang DESC)
$nextCursor = null;
if ($hasNext && count($rows) > 0) {
  $last = end($rows);
  $nextCursor = (int)$last['id'];
}

/* ===================== URL BUILDER ===================== */
//mục đích: build URL giữ nguyên params (gender/keyword) và chỉ đổi cursor khi phân trang
function buildUrl(array $changes = []): string {
  $params = $_GET;

  foreach ($changes as $k => $v) {
    if ($v === null || $v === '') unset($params[$k]);
    else $params[$k] = $v;
  }

  //mục đích: tránh URL rác kiểu cursor=0 hoặc keyword=""
  //tại sao phải làm vậy? URL gọn + logic rõ ràng
  return "index.php" . (empty($params) ? "" : ("?" . http_build_query($params)));
}

//mục đích: helper bind dynamic params cho mysqli
//tại sao phải làm vậy? bind_param cần reference, không viết helper là code rất rối
function bindDynamic(mysqli_stmt $stmt, string $types, array $values): void {
  $refs = [];
  foreach ($values as $k => $v) $refs[$k] = &$values[$k];
  array_unshift($refs, $types);
  $stmt->bind_param(...$refs);
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

  <!-- mục đích: render filter gọn + giữ keyword khi đổi gender
       em thấy viết 4 link tay dễ quên giữ params
       nên em dùng buildUrl() và reset cursor về null khi đổi filter (vì filter đổi thì cursor cũ không còn đúng)
  -->
  <a class="button-gender" href="<?= buildUrl(['gender' => 1, 'cursor' => null]) ?>">Nam</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => 2, 'cursor' => null]) ?>">Nữ</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => 3, 'cursor' => null]) ?>">Khác</a>
  <a class="button-gender" href="<?= buildUrl(['gender' => null, 'cursor' => null]) ?>">Tất cả</a>
  <br><br>

  <!-- mục đích: hiển thị keyword hiện tại (debug/UX) -->
  Keyword: <b><?= htmlspecialchars($keyword === "" ? "(none)" : $keyword, ENT_QUOTES, "UTF-8") ?></b>
  <br><br>

<?php
//mục đích: in dữ liệu ra HTML an toàn (chống XSS)
//tại sao phải làm vậy? DB có thể chứa string nguy hiểm, echo thẳng là dính XSS
if (count($rows) > 0) {
  foreach ($rows as $row) {
    $id = (int)$row['id'];
    $g  = (int)$row['gender'];
    $name = htmlspecialchars($row['name'], ENT_QUOTES, "UTF-8");

    echo "id: {$id} - Name: {$name} - Gender: {$g} ";
    //mục đích: edit chỉ cần id, không truyền name lên URL
    //tại sao phải làm vậy? URL gọn + không lỗi ký tự + tránh lộ data thừa
    echo "<a href='edit.php?id={$id}'>Edit</a><br>";
  }
} else {
  echo "0 results";
}
?>

  <!-- mục đích: pagination tối ưu theo cursor (không COUNT, không OFFSET)
       em chỉ show: First + Next
       tại sao không show 1..N? vì muốn show 1..N thì phải COUNT(*) để biết N -> nặng DB
       còn kiểu cursor thì cực nhanh và phù hợp data lớn
  -->
  <div class="pagination">
    <a class="button" href="<?= buildUrl(['cursor' => null]) ?>">First</a>

    <?php if ($hasNext && $nextCursor !== null): ?>
      <a class="button" href="<?= buildUrl(['cursor' => $nextCursor]) ?>">Next</a>
    <?php endif; ?>
  </div>

  <!-- ghi chú nhỏ:
       nếu muốn có cả Prev (quay lại) theo cursor thì phải lưu stack cursor (session) hoặc dùng reverse query.
       em giữ bản này đơn giản để tối ưu performance tối đa theo yêu cầu.
  -->
</body>
</html>
