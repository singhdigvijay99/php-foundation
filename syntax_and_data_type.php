<?php

namespace MyProject;

?>
<!DOCTYPE html>
<html>
<body>
<?php
// single line comment
# commenting like this
/* multiline asdnsjdn
comment */

echo "hello \n"; //output as string
$txt = "this is demo";
print("Hello " . $txt . "\n"); // output Hello this is demo

//data type
$var = 30;
print(gettype($var) . "\n");  /* Out put as "integer" */
$var1 = 30.90;
print(gettype($var1) . "\n");  /* Out put as "double" */
$var3 = true;
print(gettype($var3) . "\n"); /* Out put as "boolean" */

$var4 = 70.990;
var_dump($var4);  //output is float(70.99)
// var_dump return the value in the form of array along with the datatype and can accept mutiple variable


// array
$arr = ["hello","Hi","Hoola"];  // we can also define like array("hello","Hi","Hoola")
var_dump($arr);
/*
array(3) {
  [0]=>
  string(5) "hello"
  [1]=>
  string(2) "Hi"
  [2]=>
  string(5) "Hoola"
}
*/

// scopes
$x = 5; //global variable
function myTest()
{
    // able to call inside the funcation due to scope of variable is global
    echo "$x"; //PHP Warning:  Undefined variable $x
}
myTest();

echo "varible is called outside the funcation as gloablly called $x \n";
//varible is called outside the funcation as gloablly called as 5


$x1 = 5; //global variable
function myTest1()
{
    $x1 = 40; //local variable
    // here we are calling the variable locally and our output will be 40
    echo "$x1 \n";
}
myTest1();



//static scoping
function myTest3()
{
    static $x = 0; // static scope
    echo "$x \n";
    $x++;
}

myTest3();
myTest3();
myTest3();
/* output - we use static keyword to retain the variable value from it's previous state.
0
1
2
*/

// without static scoping
function myTest4()
{
    $x = 0; // static scope
    echo "$x \n";
    $x++;
}

myTest4();
myTest4();
myTest4();
/* output - here the output will be zero everytime as value is getting refreshed on each function call
0
0
0
*/


$x = "Hello world!";
$x = null;  // here no value is assigned to this variable x
var_dump($x); //output - NULL
//here initially assigned string to variable which later got chage to NULL.


//typecasting
$var5 = 6;
print_r(gettype($var5) . "\n");  // integer
$var5 = (string)$var5;
print_r(gettype($var5) . "\n"); //string


// string function
$var6 = "WooFunnel";
print(strlen($var6) . "\n"); //output - 9 (this function return the lenght of string)
print(str_word_count($var6) . "\n"); //output - 1 (this function retrun the word count in the string)

$txt1 = "I really love PHP!";
var_dump(str_contains($txt1, "love"));
/*
output bool(true), here we checking exactly
same string if found it return True and it is case Sensitive
*/

$mystring = "Hello Funnel Kit!";
$mystring2 = "Delhi";
print(strtoupper($mystring) . "\n"); // output - HELLO FUNNEL KIT!
print(strtolower($mystring) . "\n"); //output - hello funnel kit!
print( str_replace("Hello", "Hi", $mystring) . "\n"); //output - Hi Funnel Kit!
echo $mystring . $mystring2 . "\n"; //output - Hello Funnel Kit!Delhi

//string slicing
$xString = "Hi, how are you?";
echo substr($xString, 6, 5); /* here we move upto 6 index and choose next 5 index
for printing including the 6 index as well output - w are */

echo substr($xString, -5, 3); // output will be "  yo"

//escape character
//echo "Our Company "Wisetr" is in delhi"; // this will show error as we can use double quotes inside the double quotes;

echo "Our Company \"Wisetr\" is in delhi  \n"; //this is the correct way

echo(pi()  . "\n");  //output - 3.1415926535898
echo(min(0, 150, -8, -200)  . "\n"); //output : -200 this will find minimum value
echo(max(0, 150, -8, -200)  . "\n"); //output : 150 this will find maximum value
echo(abs(-6.7)  . "\n"); // output : 6 return the abosule value

/* define - this we generally use to define the domain text or api key so that we dont have to write it again and again instead of that we call that name */
define("GREETING", "Welcome to Wisetr Technologies!");
echo GREETING;  //out put - Welcome to Wisetr Technologies

/* constant - this is need to be define on the top level as it comes into play at compile time ,
it is case sensitive and also we cant define contast inside functions, try catch, loops and if else statement*/
const FRUITS = ["Apple", "Banna", " Mango"]; //defining variable must be in capital letters
echo FRUITS[1]; //Banna

define("ANIMALS", ["Tiger", "Lion", "Cheeta"]);
echo ANIMALS[2];  // Cheeta

// Magic Operators
class Fruit
{
    public function myValue()
    {
        return __CLASS__;
    }
    public function myPath()
    {
        return __DIR__;
    }
    public function myFile()
    {
        return __FILE__;
    }

    public function myMessage()
    {
        return __FUNCTION__;
    }

    public function myMethod()
    {
        return __METHOD__;
    }

    public function myValues()
    {
        return __NAMESPACE__;
    }
}
$kiwi = new Fruit();

echo $kiwi->myValue() . "\n"; // output - return the class name - MyProject\Fruit

echo  $kiwi->myPath() . "\n";  /* return the path of the project /Users/dev/Documents/projects/foundations
( mostly called inside plugin to call the directory) */

echo $kiwi->myFile() . "\n"; /*  retutn the path with the file name
/Users/dev/Documents/projects/foundations/syntax_and_data_type.php */

echo $kiwi->myMessage() . "\n"; /* Return the function name output - myMessage */

echo $kiwi->myMethod() . "\n"; /* Output - MyProject\Fruit::myMethod return the
class name  along with the function name */

echo $kiwi->myValues() . "\n"; /*output - return the namespace name MyProject */

//condition and swtich
$common_varible = 10;
if ($common_varible > 20) :
    echo "True \n";
else :
    echo "False \n";
endif;
// output - False
if ($common_varible % 2 == 0) :
    echo "even \n";
elseif ($common_varible < 0) :
    echo "Give Positive number";
else :
    echo "Odd \n";
endif;
// output - even

if ($common_varible > 0) :
    if ($common_varible < 11 && $common_varible > 8) :
        echo "This the exact number we want \n";
    else :
        echo "Not a required number \n";
    endif;
else :
    echo " enter positive number \n";
endif;
// output - This the exact number we want

// Turnary operators
$var7 = 13;
$var8 = $var7 < 10 ? "Hello \n" : "Good Bye \n";
echo $var8;
//output - Good Bye


$favcolor = "red";

switch ($favcolor) {
    case "blue":
        echo "This is not my fav color \n ";
        break;
    case "red":
        echo " This is my fav color \n" ;
        break;
    case "yellow":
        echo " Mostly people like this color \n";
        break;
    default:
        echo "Looking forward to the Weekend \n";  // this is used  when nothing  matches with the requested data
}

// output - This is my fav color

$d = 3;

switch ($d) {
    case 1:
    case 2:
    case 3:
    case 4:
    case 5:
        echo "The week feels so long! \n";
        break;
    case 6:
    case 0:
        echo "Weekends are best! \n";
        break;
    default:
        echo "Something went wrong \n";
}
// Output - The week feel so long
// here from case 1 to case 5 returns the same output

// Match in Php after php 8.0 ( New learing for me)
$color = "red";
$textColor = match ($color) {
    "red" => " This my fav color \n",
    "yellow" => "Mostly people like this color ",
    "blue" => "I dont like this color \n",
    default => "Your favorite color is neither red, blue, nor green! \n",
};

echo $textColor; //This my fav color
// match use strict comparison === , returns a value , no need to add break / continue,is more readable and fast

// loops
$i = 1;
while ($i < 6) :
    echo "$i \n";
    $i++;
endwhile; // output 1 2 3 4 5

$j = 8;
do {
    echo "$j \n";
    $j++;
} while ($j < 6);

for ($x = 0; $x <= 10; $x++) {
    echo "The number is: $x \n";
}

$members = array ("Peter" => "35", "Ben" => "37", "Joe" => "43");

foreach ($members as $key => $value) {
    echo "$key : $value \n";
}
/* output Peter : 35 Ben : 37 Joe : 43 */

//functions
function familyName($fname)
{
    echo "$fname \n";
}

familyName("Jani");
familyName("Hege");
familyName("Stale");
/*Jani
Hege
Stale */

// default arrgument function
function greet($name = "Guest")
{
    echo "Hello, $name";
}

greet(); // Output -> Hello, Guest

// pass by value
function test($x)
{
    $x = $x + 5;
}
$a = 10;
test($a);
echo $a; // output will be 10

// pass by reference
function test2(&$y)
{
    $y = $y + 5;
}
$b = 10;
test2($b);
echo "$b \n"; //output is 15

// try , throw and catch
function checkNum($number)
{
    if ($number > 1) {
        throw new \Exception("Value must be 1 or below \n");
    }
    return true;
}
//trigger exception in a "try" block
try {
    checkNum(2);
    //If the exception is thrown, this text will not be shown
    echo 'If you see this, the number is 1 or below';
} catch (\Exception $e) {
    echo 'Message: ' . $e->getMessage();
}

//output - Message: Value must be 1 or below


class CustomException extends \Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile() . ': <b>' . $this->getMessage() . '</b> is not a valid E-Mail address';
        return $errorMsg;
    }
}

$email = "someone@example...com";

try {
    //check if
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
    {
        //throw exception if email is not valid
        throw new CustomException($email);
    }
} catch (CustomException $e) {
    //display custom message
    echo $e->errorMessage();
}

/*. output - Error on line 398 in /Users/dev/Documents/projects/foundations/syntax_and_data_type.php:
<b>someone@example...com</b> is not a valid E-Mail address</body> */
/* Error internaly used by php
Constant Meaning
E_ERROR Fatal error
E_WARNING Warning
E_NOTICE Notice
E_PARSE    Syntax error
E_DEPRECATED Deprecated.
 */

?>
</body>
</html>
