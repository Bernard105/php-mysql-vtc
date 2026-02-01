<!DOCTYPE html>
<html>
<head>
    <title>PHP Examples for Beginners</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .menu { margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; }
        .menu button { margin: 5px; padding: 10px 15px; cursor: pointer; }
        .result { padding: 20px; border: 1px solid #ddd; background: #fff; border-radius: 5px; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Learning Examples Lab 1</h1>
        
        <div class="menu">
            <form method="post">
                <button type="submit" name="example" value="0">Example 0: Basic Info</button>
                <button type="submit" name="example" value="1">Example 1: Comments & Variables</button>
                <button type="submit" name="example" value="2">Example 2: Print Statements</button>
                <button type="submit" name="example" value="3">Example 3: Comments Again</button>
                <button type="submit" name="example" value="4">Example 4: Text Output</button>
                <button type="submit" name="example" value="5">Example 5: Image Display</button>
            </form>
        </div>
        
        <div class="result">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['example'])) {
                $selected = $_POST['example'];
                
                switch ($selected) {
                    case '0':
                        echo "<h2>Example 0: Basic Info</h2>";
                        $name = "Tran Manh Tai<br>";
                        $nganh = "Xay dung<br>";
                        $dream = "Xay dung 1 server tai trong lon";
                        echo "My name is $name" . "Nganh hoc cu la $nganh" . "My dream is $dream";
                        break;
                        
                    case '1':
                        echo "<h2>Example 1: Comments & Variables</h2>";
                        echo "// This is a single-line comment<br>";
                        echo "# This is also a single-line comment<br>";
                        echo "/*<br>This is a multiple-lines comment block<br>that spans over multiple<br>lines<br>*/<br><br>";
                        echo "// You can also use comments to leave out parts of a code line<br>";
                        $x = 69 /* + 15 */ + 96;
                        echo "\$x = 69 /* + 15 */ + 96;<br>";
                        echo "Result: \$x = " . $x;
                        break;
                        
                    case '2':
                        echo "<h2>Example 2: Print Statements</h2>";
                        $txt1 = "Learn PHP";
                        $txt2 = "VTC Academy";
                        $x = 5;
                        $y = 4;
                        print "<h3>" . $txt1 . "</h3>";
                        print "Study PHP at " . $txt2 . "<br>";
                        print "Calculation: \$x + \$y = " . ($x + $y);
                        break;
                        
                    case '3':
                        echo "<h2>Example 3: Comments Again</h2>";
                        echo "// This is a single-line comment<br>";
                        echo "# This is also a single-line comment<br>";
                        echo "/*<br>This is a multiple-lines comment block<br>that spans over multiple<br>lines<br>*/<br><br>";
                        echo "// You can also use comments to leave out parts of a code line<br>";
                        $y = 10;
                        $x = $y /* + 15 */ + 5;
                        echo "\$y = 10;<br>";
                        echo "\$x = \$y /* + 15 */ + 5;<br>";
                        echo "Result: \$x = " . $x;
                        break;
                        
                    case '4':
                        echo "<h2>Example 4: Text Output</h2>";
                        $txt1 = "Hello";
                        $txt2 = "You delivered your assignment online";
                        $txt3 = "Thanks<br>Mahnaz";
                        echo "<p style='color: blue;'>$txt1</p>";
                        echo "<h3 style='color: green;'>$txt2</h3>";
                        echo "<p>$txt3</p>";
                        break;
                        
                    case '5':
                        echo "<h2>Example 5: Image Display</h2>";
                        $image1 = "images1.png";
                        echo "<center>";
                        echo "<img src='$image1' width='500' height='500' alt='PHP Example Image'>";
                        echo "<p>Sample image displayed using PHP variable</p>";
                        echo "</center>";
                        break;
                        
                    default:
                        echo "<p style='color: #666;'>Please select an example from the menu above.</p>";
                        break;
                }
            } else {
                echo "<p style='color: #666;'>Please select an example from the menu above.</p>";
            }
            ?>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f0f8ff; border-radius: 5px;">
            <h3>Explanation:</h3>
            <ul>
            </ul>
        </div>
    </div>
</body>
</html>