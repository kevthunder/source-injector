<?php
namespace Kevthunder\SourceInjector\Source;


class FileSource implements ISource
{
    /**
     * @var string
     */
    protected $fileName;

    public function getFileName()
    {
        return $this->fileName;
    }
    
    public function __construct($fileName){
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getContent(){
        return file_get_contents($this->fileName);
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        file_put_contents($this->fileName, $content);
    }

    /**
     * @return int
     */
    public function getLength(){
        return filesize($this->fileName);
    }

    /**
     * @param int $start
     * @param int $length
     * @return string
     */
    public function getSubString($start, $length){
        $handle = fopen($this->fileName, 'r');
        fseek($handle, $start);
        $res = fread($handle, $length);
        fclose($handle);
        return $res;
    }

    /**
     * @param $input
     * @return bool
     */
    public static function detect($input){
        return is_string($input) && preg_match('/^([\/\\\\]|[a-zA-Z]:\\\\)?(([a-zA-Z_][\w-]*|\.\.)[\/\\\\])*[a-zA-Z_][\w-]*(\.[a-zA-Z][a-zA-Z0-9]*)*$/',$input);
    }
}