<!DOCTYPE html>
<html>

<body>
  <?php
  class Fruit
  {
    // Properties
    public $name;
    public $color;

    function __construct($name="apple", $color="red") {
      $this->name = $name;
      $this->color = $color;
    }

    // Methods
    function set_name($name)
    {
      $this->name = $name;
    }
    function get_name()
    {
      return $this->name;
    }
  }
  $apple
    = new Fruit();
  $banana = new Fruit();
  $mango = new Fruit();
  $tomato = new
    Fruit();
  $pineapple = new Fruit();
  $cherry = new Fruit();
  $orange = new
    Fruit();
  $kiwi = new Fruit();
  $blueberry = new Fruit();
  $peach = new Fruit();

  $apple = new Fruit();
  echo $apple->get_name();

  // $apple->set_name('Apple');
  // $banana->set_name('Banana');
  // $mango->set_name('Mango');
  // $tomato->set_name('Tomato');
  // $pineapple->set_name('Pineapple');
  // $cherry->set_name('Cherry');
  // $orange->set_name('Orange');
  // $kiwi->set_name('Kiwi');
  // $blueberry->set_name('Blueberry');
  // $peach->set_name('Peach');
  // echo
  // $apple->get_name();
  // echo "<br />";
  // echo $banana->get_name();
  // echo "<br />";
  // echo $mango->get_name();
  // echo "<br />";
  // echo $tomato->get_name();
  // echo "<br />";
  // echo $pineapple->get_name();
  // echo "<br />";
  // echo $cherry->get_name();
  // echo
  // "<br />";
  // echo $orange->get_name();
  // echo "<br />";
  // echo $kiwi->get_name();
  // echo "<br />";
  // echo $blueberry->get_name();
  // echo "<br />";
  // echo
  // $peach->get_name();
  // echo "<br />"; ?>
</body>

</html>