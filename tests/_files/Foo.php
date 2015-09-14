<?php
namespace Kevthunder\SourceInjector;

// Special characters éèçî

class Foo{


    public $data = array(
        'foo' => 'Foo',
        'bar' => 'Bar',
        'foo_bar' => 'Foo bar'
    );

    public $param;

    /**
     *
     * @param string $param
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    public function hello(){
        return 'Hello, world!';
    }




    public function bar($val){

        return 'bar :'. $val;
    }

}