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

  Bộ lọc:
  Giới tính

  <!-- mục đích: thêm nút "Tất cả" để user bỏ lọc nhanh và quay lại danh sách full
       em thấy code cũ chỉ có Nam/Nữ/Khác -> nếu đang lọc rồi muốn xem lại full thì phải tự xóa ?gender trên URL (không tiện)
       nên em thêm link index.php (không truyền gender) làm "Tất cả" để thao tác 1 phát là về đầy đủ
  -->
  <a class="button-gender" href="index.php?gender=1">Nam</a>
  <a class="button-gender" href="index.php?gender=2">Nữ</a>
  <a class="button-gender" href="index.php?gender=3">Khác</a>
  <a class="button-gender" href="index.php">Tất cả</a>
  <br><br>

<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "student_management_db_25";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

/* mục đích: fix page/offset để phân trang ổn định và không bị user nhập bậy làm sai logic
   em thấy cách cũ dùng $_GET['page'] trực tiếp (hoặc không ép kiểu) -> user truyền page=abc/page=-5 thì offset tính sai
   nên em sửa:
   - ép kiểu int
   - nếu page < 1 thì set về 1
   -> đảm bảo offset luôn hợp lệ
*/
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

/* mục đích: lấy gender không bị undefined + validate để tránh giá trị linh tinh
   em thấy code cũ có thể bị:
   - undefined khi không truyền gender
   - hoặc nhận gender=abc => câu SQL/logic dễ lỗi
   nên em sửa:
   - ép kiểu int
   - chỉ cho 1/2/3, còn lại coi như null (không lọc)
*/
$gender = isset($_GET['gender']) ? (int)$_GET['gender'] : null;
if (!in_array($gender, [1,2,3], true)) {
  $gender = null;
}

/* mục đích: đưa keyword thành biến để dễ mở rộng search sau này
   em thấy code cũ keyword bị hardcode -> muốn search theo input thì phải sửa tay trong code
   nên em sửa:
   - nếu URL có keyword thì lấy, không có thì default "Thị"
   - trim() cho gọn
*/
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "Thị";
$like = "%$keyword%";

/* mục đích: chống SQL Injection + tránh lỗi query khi keyword có ký tự đặc biệt
   em thấy code cũ ghép chuỗi thẳng vào SQL:
   - user có thể chèn SQL phá dữ liệu (SQL Injection)
   - keyword có dấu nháy ' dễ làm vỡ query
   nên em dùng prepare + bind_param để MySQL hiểu keyword/gender là data chứ không phải lệnh
*/
if ($gender === null) {
  //không lọc gender -> chỉ tìm theo name
  $sql = "SELECT id, name, gender FROM students
          WHERE name LIKE ?
          LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $like, $limit, $offset);
} else {
  //có lọc gender
  $sql = "SELECT id, name, gender FROM students
          WHERE gender = ? AND name LIKE ?
          LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isii", $gender, $like, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

/* mục đích: hiển thị dữ liệu ra HTML an toàn + link edit không bị lỗi ký tự
   em thấy có 2 vấn đề hay dính:
   - XSS: nếu name trong DB có <script>...</script> mà echo thẳng thì browser chạy luôn
   - link edit: nếu name có dấu/space thì nối URL thẳng dễ hỏng
   nên em sửa:
   - htmlspecialchars() khi in name ra HTML
   - urlencode() khi nhét name lên URL
*/
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {

    $id = (int)$row["id"];
    $g  = (int)$row["gender"];

    $name = htmlspecialchars($row["name"], ENT_QUOTES, "UTF-8");

    echo "id: {$id} - Name: {$name} - Gender: {$g} ";

    echo "<a href='edit.php?id={$id}&name=" . urlencode($row["name"]) . "'>Edit</a><br>";
  }
} else {
  echo "0 results";
}

$stmt->close();
$conn->close();

/* mục đích: giữ nguyên filter khi bấm phân trang
   em thấy code cũ bấm page=2 là mất gender/keyword vì link chỉ có mỗi page
   nên em viết buildPageLink():
   - lấy toàn bộ params hiện tại từ $_GET
   - chỉ thay mỗi page
   -> bấm trang nào cũng giữ đúng filter đang chọn
*/
function buildPageLink($pageNum) {
  $params = $_GET;
  $params['page'] = $pageNum;
  return "index.php?" . http_build_query($params);
}
?>

  <div class="pagination">
    <a class="button" href="<?= buildPageLink(1) ?>">1</a>
    <a class="button" href="<?= buildPageLink(2) ?>">2</a>
    <a class="button" href="<?= buildPageLink(3) ?>">3</a>
  </div>
</body>
</html>
