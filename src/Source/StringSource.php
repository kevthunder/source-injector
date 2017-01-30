<?php
namespace Kevthunder\SourceInjector\Source;


class StringSource implements ISource
{
    /**
     * @var string
     */
    protected $string;

    
    public function __construct($string){
        $this->string = $string;
    }

    /**
     * @return string
     */
    public function getContent(){
        return $this->string;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->string = $content;
    }

    /**
     * @return int
     */
    public function getLength(){
        return strlen($this->string);
    }

    /**
     * @param int $start
     * @param int $length
     * @return string
     */
    public function getSubString($start, $length){
        return substr($this->string, $start, $length);
    }

    /**
     * @param $input
     * @return bool
     */
    public static function detect($input){
        return is_string($input);
    }
}