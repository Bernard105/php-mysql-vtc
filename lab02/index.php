<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Lab02 - Fix code (No EX8)</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 16px; }
    .box { border: 1px solid #ddd; border-radius: 10px; padding: 12px; margin: 12px 0; }
    .ok { color: green; font-weight: 700; }
    .bad { color: crimson; font-weight: 700; }
    code { background: #f6f6f6; padding: 2px 6px; border-radius: 6px; }

    .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .card { border: 1px solid #eee; border-radius: 12px; padding: 12px; }
    .thumb { width: 100%; height: 160px; object-fit: contain; background: #fafafa; border-radius: 10px; }
    .name { margin: 10px 0 6px; font-weight: 700; font-size: 14px; min-height: 38px; }
    .price { font-weight: 800; }
    .old { color: #888; text-decoration: line-through; margin-left: 6px; font-size: 13px; }
    .discount { color: #d0021b; font-weight: 800; margin-left: 6px; font-size: 13px; }
    @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, 1fr);} }
    @media (max-width: 600px) { .grid { grid-template-columns: 1fr;} }
  </style>
</head>

<h2>Đổi màu thẻ</h2>

<body>

<?php
$age = 20;
if ($age < 18) {
    echo "Bạn chưa đủ tuổi để truy cập.";
}
if (18 < $age && $age < 65) {
    echo "Chào mừng bạn đến với hệ thống.";
}
if ($age > 65) {
    echo "Chúc mừng bạn đã nghỉ hưu!";
}

$weight = 1.5;

// switch ($height) {
//     case 0 : 
//         echo "Phi la 0";
//         break;
//     case $height > 10:
//         echo "Phí là 100.000 VNĐ";
//         break;
//     case $height > 5:
//         echo "Phí là 50.000 VNĐ.";
//         break;
//     case $height > 1:
//         echo "Phí là 30.000 VNĐ";
//         break;
//     case $height <= 1 && $height > 0:
//         echo "Phí là 10.000 VNĐ";
//         break;
//     default:
//         echo "Trọng lượng không hợp lệ.";
// } sai ban chat

switch (true) {
    case ($weight < 0):
        echo "<br>Trọng lượng không hợp lệ.";
        break;

    case ($weight <= 1):
        echo "<br>Phí là 10.000 VNĐ.";
        break;

    case ($weight <= 5):
        echo "<br>Phí là 30.000 VNĐ.";
        break;

    case ($weight <= 10):
        echo "<br>Phí là 50.000 VNĐ.";
        break;

    default:
        echo "<br>Phí là 100.000 VNĐ.";

        $x = 2;
        $x1 = 0;
        $y = 1;
        $y1 = 0;

        for ($x = 2; $x < 9; $x++) {
            echo "<br>Bang cuu chuong " . $x . "<br>";
            for ($y = 1; $y < 9; $y++) {
                $x1 = $x * $y;
                if ($x % 2 == 0 || $y % 2 == 0) {
                    echo $x . '*' . $y . '=' . "<b>$x1</b> ";
                } else echo $x . '*' . $y . '=' . "<b>$x1</b> ";
            }
            echo "<br>";
        }

        // FIX: while này trước bị treo vì $y không tăng và $y1 chưa gán ban đầu
        $y = 1;
        $y1 = 0;
        while ($y < 100) {
            $y1 += $y;
            $y++;
        }
        echo "<br>Tổng y1 = " . $y1;
}

//ex1 demonstrate:chung to, case-sensitive:phân biệt chữ hoa chữ thường. viet doan code chung to php co phan biet ten bien viet hoa viet thuong
$color = "red";
$coLor = "green";
$COLOR = "blue";
echo "<p style='color: $color;'>1st house'color is $color</p>";
echo "<p style='color: $coLor;'>2st house'color is $coLor</p>";
echo "<p style='color: $COLOR;'>3st house'color is $COLOR</p>";

//ex2 if-else
$condition = true;

if ($condition) {
    echo '<p style="color: red;">Nội dung màu đỏ (true)</p>';
} else {
    echo '<p style="color: blue;">Nội dung màu xanh (false)</p>';
}

//ex3 foreach
$color = array("red", "green", "blue");
foreach ($color as $value) {
    echo "<p style='color: $value;'>Giá trị là $value</p>";
}

//ex4
function sum($a, $b)
{
    $c = $a + $b;
    return $c;
}

echo "10 + 5 = " . sum(10, 5) . "<br>";
echo "123 + 321 = " . sum(123, 321) . "<br>";
echo "666 + 999 = " . sum(666, 999) . "<br>";

//ex5 associative:tập hợp, Associative Array: mảng kết hợp giống hashtable
$color = array("brand color" => "red", "primary color" => "green", "secondary color" => "blue");
foreach ($color as $x => $x_value) {
    echo "<p style='color: $x_value;'>Key là $x có Value là $x_value</p>";
}

//ex6 function with parameters.
function sum_ex6($x, $y, $z)
{
    $sum = $x + $y + $z;
    return $sum;
}
function average($x, $y, $z)
{
    $avg = ($x + $y + $z);
    return $avg;
}

echo "10 + 5 = " . sum_ex6(10, 5, 15) . "<br>";
echo "123 + 321 = " . average(123, 321, 312) . "<br>";

//ex7 identify the email addresses which are not unique. unique: duy nhất

// function array_not_unique($my_array)
// {
//     $same = array();
//     natcasesort($my_array);
//     reset($my_array);

//     $old_key = NULL;
//     $old_value = NULL;

//     foreach ($my_array as $key => $value) {
//         if ($value === NULL) {
//             continue;
//         }

//         if ($old_value == $value) {
//             $same[$key] = $value;
//         }

//         $old_value = $value;
//         $old_key = $key;
//     }
//     return $same;
// }

function array_not_unique($my_array)
{
    $count = [];
    $same = [];

    foreach ($my_array as $value) {
        if ($value !== NULL) {
            if (isset($count[$value])) {
                $count[$value]++;
            } else {
                $count[$value] = 1;
            }
        }
    }

    foreach ($my_array as $key => $value) {
        if ($value !== NULL && $count[$value] > 1) {
            $same[$key] = $value;
        }
    }

    return $same;
}
$email_list1 = array(
    "a@gmail.com",
    "b@gmail.com",
    "b@gmail.com",
    "c@gmail.com",
    "d@gmail.com",
    "a@gmail.com"
);

echo "<h3>Test 1: Mảng có email trùng lặp</h3>";
echo "<pre>";
echo "Email list:\n";
print_r($email_list1);
echo "Email trùng lặp:\n";
print_r(array_not_unique($email_list1));
echo "</pre>";

//ex8
function check_palindrome($str) {
    if($str == strrev($str)) {
        return 1;
    }
    return 0;
}
$str = "Helleh";
echo "Chuoi " . $str . "<br>";
echo "Kiem tra palindrome (basic): " . check_palindrome($str) . "<br>";


//ex9
echo "<div class='box'>";
echo "<h3>Exercise 9: Check String All Lowercase</h3>";

function is_str_lowercase($str) {
  for ($i = 0; $i < strlen($str); $i++) {
    $ord = ord($str[$i]);
    if ($ord >= ord('A') && $ord <= ord('Z')) return false;
  }
  return true;
}

$tests_ex9 = [
  "hello world",
  "Hello world",
  "php123",
  "ABC",
  "hello-php!",
];

for ($i = 0; $i < count($tests_ex9); $i++) {
  $s = $tests_ex9[$i];
  echo "<p>Chuỗi: <code>" . htmlspecialchars($s) . "</code> → ";
  if (is_str_lowercase($s)) {
    echo "<span class='ok'>✅ All lowercase (không có A-Z)</span>";
  } else {
    echo "<span class='bad'>❌ Có chữ hoa</span>";
  }
  echo "</p>";
}
echo "</div>";


//ex10
echo "<div class='box'>";
echo "<h3>Exercise 10: Products of Canon (6 cameras)</h3>";

function vnd($n) {
  return number_format($n, 0, ",", ".") . " đ";
}

$cameras = [
  [
    "name" => "Máy Ảnh Canon SX730 HS (Hàng Nhập Khẩu)",
    "price" => 7690000, "old_price" => 9370000, "discount" => 18,
    "img" => "https://via.placeholder.com/400x260?text=Canon+SX730+HS"
  ],
  [
    "name" => "Máy Ảnh Canon SX720 HS (Hàng Nhập Khẩu)",
    "price" => 6290000, "old_price" => 7870000, "discount" => 20,
    "img" => "https://via.placeholder.com/400x260?text=Canon+SX720+HS"
  ],
  [
    "name" => "Máy Ảnh Canon SX620 HS (Hàng Nhập Khẩu)",
    "price" => 4890000, "old_price" => 6240000, "discount" => 22,
    "img" => "https://via.placeholder.com/400x260?text=Canon+SX620+HS"
  ],
  [
    "name" => "Máy Ảnh Canon SX730 HS (Hàng Chính Hãng)",
    "price" => 9170000, "old_price" => 10620000, "discount" => 14,
    "img" => "https://via.placeholder.com/400x260?text=Canon+SX730+HS+CH"
  ],
  [
    "name" => "Máy Ảnh Canon Powershot G3X (Lê Bảo Minh)",
    "price" => 16990000, "old_price" => 22500000, "discount" => 24,
    "img" => "https://via.placeholder.com/400x260?text=Canon+G3X"
  ],
  [
    "name" => "Máy Ảnh Canon G9X Mark II (Hàng Nhập Khẩu)",
    "price" => 9490000, "old_price" => 11990000, "discount" => 21,
    "img" => "https://via.placeholder.com/400x260?text=Canon+G9X+Mark+II"
  ],
];

echo "<div class='grid'>";
for ($i = 0; $i < 6; $i++) {
  echo "<div class='card'>";
  echo "<img class='thumb' src='" . htmlspecialchars($cameras[$i]['img']) . "' alt='camera'>";
  echo "<div class='name'>" . htmlspecialchars($cameras[$i]['name']) . "</div>";
  echo "<div>";
  echo "<span class='price'>" . vnd($cameras[$i]['price']) . "</span>";
  echo "<span class='old'>" . vnd($cameras[$i]['old_price']) . "</span>";
  echo "<span class='discount'>-" . (int)$cameras[$i]['discount'] . "%</span>";
  echo "</div>";
  echo "</div>";
}
echo "</div>";

echo "</div>";
?>

</body>
</html>
