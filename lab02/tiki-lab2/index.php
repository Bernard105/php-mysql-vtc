<?php
$pageTitle = "Exercise 11 - Tiki Product Page";

function vnd($n) {
  return number_format($n, 0, ",", ".") . " đ";
}

$products = [
  ["name"=>"Canon SX730 HS (Nhập khẩu)", "price"=>7690000, "old_price"=>9370000, "discount"=>18, "img"=>"images1.png", "rating"=>4.8],
  ["name"=>"Canon SX720 HS (Nhập khẩu)", "price"=>6290000, "old_price"=>7870000, "discount"=>20, "img"=>"images1.png", "rating"=>4.6],
  ["name"=>"Canon SX620 HS (Nhập khẩu)", "price"=>4890000, "old_price"=>6240000, "discount"=>22, "img"=>"images1.png", "rating"=>4.5],
  ["name"=>"Canon SX730 HS (Chính hãng)", "price"=>9170000, "old_price"=>10620000, "discount"=>14, "img"=>"images1.png", "rating"=>4.7],
  ["name"=>"Canon Powershot G3X", "price"=>16990000, "old_price"=>22500000, "discount"=>24, "img"=>"images1.png", "rating"=>4.9],
  ["name"=>"Canon G9X Mark II", "price"=>9490000, "old_price"=>11990000, "discount"=>21, "img"=>"images1.png", "rating"=>4.4],
];

$categories = ["Điện thoại", "Máy tính bảng", "Laptop", "Máy ảnh", "Tai nghe", "Phụ kiện"];

include "header.php";
?>

<div class="layout">
  <div class="panel">
    <h3>Danh mục</h3>
    <?php foreach ($categories as $c): ?>
      <div class="muted">• <?php echo htmlspecialchars($c); ?></div>
    <?php endforeach; ?>

    <hr style="border:none; border-top:1px solid #eee; margin:12px 0;">
    <div class="muted"><b>Note:</b> Bài 11 yêu cầu include header/footer và hiển thị 6 sản phẩm.</div>
  </div>

  <div class="panel">
    <h3>Máy ảnh - 6 sản phẩm</h3>
    <div class="grid">
      <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="card">
          <img class="thumb" src="<?php echo htmlspecialchars($products[$i]["img"]); ?>" alt="product">
          <div class="name"><?php echo htmlspecialchars($products[$i]["name"]); ?></div>
          <div class="rating">★★★★★ <?php echo $products[$i]["rating"]; ?></div>
          <div style="margin-top:6px;">
            <span class="price"><?php echo vnd($products[$i]["price"]); ?></span>
            <span class="old"><?php echo vnd($products[$i]["old_price"]); ?></span>
            <span class="discount">-<?php echo (int)$products[$i]["discount"]; ?>%</span>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>
