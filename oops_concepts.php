<?php

namespace MyCar;

?>
<!DOCTYPE html>
<html>
<body>
<?php
// class Car
class Car
{
    public $brand;
    public $color;

    public function startEngine()
    {
        return "Engine started \n";
    }
}

$car1 = new Car(); //obejct of class
$car1->brand = "Toyota";
$car1->color = "Red";


echo $car1->startEngine(); //ouput Engine started
echo $car1->brand; // output Toyota

class Fruit
{
    public $name;
    public $color;

    public function __construct($name, $color) //on every object call it will be automatically called
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function getDetails()
    {
        echo "Name: " . $this->name . ". Color: " . $this->color . "\n";
    }
}

$apple = new Fruit('Apple', 'Red');
$apple->getDetails(); // Name : Appple Color : Red

$banana = new Fruit('Banana', 'Yellow');
$banana->getDetails();


// Private Access Modifiyer
class BankAccount
{
    private $balance = 1000;

    public function getBalance()
    {
        return $this->balance;
    }
}

$bank = new BankAccount();
//$bank->balance = "100003";
//$bank->getBalance(); // ouptut  Cannot access private property

// inheritance
class Vehicle
{
    public function start()
    {
        echo "Vehicle started \n";
    }
}

class Cars extends Vehicle // we use extend keyword in inheritance
{
    public function drive()
    {
        echo "Car driving \n";
    }
}

$car = new Cars();
$car->start(); // inherited output - Vehicle started

class Fruits
{
    public $name;
    public $color;

    public function __construct($name, $color)
    {
        $this->name = $name;
        $this->color = $color;
    }

    protected function intro()
    {
        echo "The fruit is $this->name and the color is $this->color.";
    }
}

class Strawberry extends Fruits
{
    public function message()
    {
        echo "Am I a fruit or a berry? ";
    }
}

// Try to call all three methods from outside class
$strawberry = new Strawberry("Strawberry", "red");  // OK. __construct() is public
$strawberry->message(); // OK. message() is public
//$strawberry->intro(); // in the output we got error calling a Protected method


class Animals
{
    public function sound()
    {
        echo "Some sound";
    }
}

class Dog extends Animals
{
    public function sound()
    {
        echo "Bark";
    }
}


class Cat extends Animals
{
    public function sound()
    {
        echo "Meow\n";
    }
}

// Polymorphic function
function makeSound(Animals $animal)
{
    $animal->sound();
}

// Different objects, same function
makeSound(new Dog()); //output Bark
makeSound(new Cat()); //output  Meow


abstract class Shape // we can't make the object of abstarct class
{
    abstract public function area();
}

class Circle extends Shape
{
    public function area()
    {
        return 3.14 * 5 * 5;
    }
}

// Create object of Circle (NOT Shape)
$circle = new Circle();

echo $circle->area();  //output - 78.5


//interface- in this method define which public methods a class MUST implement, without defining how they should be implemented.
interface AnimalNew
{
    public function makeSound();
}

// Implement the interface in a class
class Cats implements AnimalNew
{
    public function makeSound()
    {
        echo "Meow \n";
    }
}

// Implement the interface in another class
class Dogs implements AnimalNew
{
    public function makeSound()
    {
        echo "Woff \n";
    }
}

$cat = new Cats();
$cat->makeSound(); // output -> Meow

if (class_exists('Dogs')) :  // example for class_exsists - aslo it help in preventing from fatal errors
    $dog = new Dogs();
    $dog->makeSound(); // Output -> Woff
else :
    echo "create a class first \n";
endif;


//Traits - As in php we cant use multiple inheritance so we use trait
trait Message1
{
    public function msg1()
    {
        echo "PHP OOP is fun! \n";
    }
    public function msg2()
    {
        echo "Traits reduce code duplication! \n";
    }
    public function msg3()
    {
        echo "Hello World! \n";
    }
}

class Welcome
{
    use Message1;
}

class Welcome2
{
    use Message1;
}

$obj = new Welcome();
$obj->msg1();
echo "<br>";

$obj2 = new Welcome2();
$obj2->msg1();
$obj2->msg2();
/* PHP OOP is fun!  output of $obj
PHP OOP is fun! 
Traits reduce code duplication! Output of $obj2 
*/


class CarMotor
{
    private $engine;

    public function __construct($engine) {
        if (!($engine instanceof Engine)) {
            throw new Exception("Invalid Engine dependency");
        }

        $this->engine = $engine;
    }
}

$engine = new Engine();
$car = new CarMotor($engine);  //exepcetion is throw


?>
</body>
</html>