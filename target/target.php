<?php
class good {
    protected $a;

    function __construct() {
        $this->a = new hello();
    }

    function __destruct() {
        $this->a->action();
    }
}

class hello {
    function action() {
        echo "hello";
    }
}

class shell {
    private $data;
    function action() {
        eval($this->data);
    }
}

@unserialize($_GET['data']);
?>