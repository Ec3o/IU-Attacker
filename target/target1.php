<?php
class start_gg
{
    public $mod1;
    public $mod2;
    public function __destruct()
    {
        $this->mod1->test1();
    }
}
class Call
{
    public $mod1;
    public $mod2;
    public function test1()
    {
        $this->mod1->test2();
    }
}
class funct
{
    public $mod1;
    public $mod2;
    public function __call($test2,$arr)
    {
        $s1 = $this->mod1;
        $s1();
    }
}
class func
{
    public $mod1;
    public $mod2;
    public function __invoke()
    {
        $this->mod2 = "字符串拼接".$this->mod1;
    }
}
class string1
{
    public $str1;
    public $str2;
    public function __toString()
    {
        $this->str1->get_flag();
        return "1";
    }
}
class GetFlag
{
    public function get_flag()
    {
        echo "flag:"."xxxxxxxxxxxx";
    }
}
$a = $_GET['string'];
unserialize($a);
?>