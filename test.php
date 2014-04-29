<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     29/04/14 10:52
 */

abstract class A{

    protected static $value = null;
    protected static $bound = false;

    public function __construct(){

      if(!static::$bound){
          $tmp = 'x';
          static::$bound = &$tmp;
          static::$bound = true;


          static::$value = &$tmp;
          static::$value = null;
      }
    }

    public static function setValue1($val){
        static::$value = $val;
    }

    public static function setValue2($val){
        self::$value = $val;
    }

    public static function getValue1(){
        print get_called_class();
        return static::$value;
    }

    public static function getValue2(){
        return self::$value;
    }
}

class B extends A{ };
class C extends A{ };

$b = new B();
$c = new C();
$c2 = new C();

print "<pre>";
print "----------\n";
$b->setValue1(11);
print "\$b->setValue1(11);\n";
print "\$b->getValue1();\n";
print $b->getValue1(). "\n\n";
print "\$b->getValue2();\n";
print $b->getValue2() . "\n\n";
print "\$c->getValue1();\n";
print $c->getValue1(). "\n\n";
print "\$c->getValue2();\n";
print $c->getValue2(). "\n\n";
print "\$c2->getValue1();\n";
print $c2->getValue1(). "\n\n";
print "\$c2->getValue2();\n";
print $c2->getValue2(). "\n\n";

print "----------\n";
$c->setValue1(22);
print "\$c->setValue1(22);\n";
print "\$b->getValue1();\n";
print $b->getValue1(). "\n\n";
print "\$b->getValue2();\n";
print $b->getValue2() . "\n\n";
print "\$c->getValue1();\n";
print $c->getValue1(). "\n\n";
print "\$c->getValue2();\n";
print $c->getValue2(). "\n\n";
print "\$c2->getValue1();\n";
print $c2->getValue1(). "\n\n";
print "\$c2->getValue2();\n";
print $c2->getValue2(). "\n\n";

$b2 = new B();

print "----------\n";
print "\$b->getValue1();\n";
print $b->getValue1(). "\n\n";
print "\$b->getValue2();\n";
print $b->getValue2() . "\n\n";
print "\$b2->getValue1();\n";
print $b2->getValue1(). "\n\n";
print "\$b2->getValue2();\n";
print $b2->getValue2() . "\n\n";
print "\$c->getValue1();\n";
print $c->getValue1(). "\n\n";
print "\$c->getValue2();\n";
print $c->getValue2(). "\n\n";
print "\$c2->getValue1();\n";
print $c2->getValue1(). "\n\n";
print "\$c2->getValue2();\n";
print $c2->getValue2(). "\n\n";
