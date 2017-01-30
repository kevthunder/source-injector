<?php
use Kevthunder\SourceInjector\Source\StringSource;

class StringSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $files_source;
    
    protected function setUp()
    {
        $this->files_source = __DIR__.'/_files';
    }

    protected function tearDown()
    {
    }


    public function testDetect(){
        $this->assertTrue(StringSource::detect('Lorem ipsum'), 'string');
        $this->assertFalse(StringSource::detect([]), 'array');
        $this->assertFalse(StringSource::detect(new stdClass()), 'obj');
    }

    public function testGetContent(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $source = new StringSource($originalContent);
        $this->assertEquals($originalContent, $source->getContent());
    }

    public function testSetContent()
    {
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $source = new StringSource($originalContent);
        $source->setContent('Lorem Ipsum');
        $this->assertEquals('Lorem Ipsum', $source->getContent());
    }

    public function testGetLength(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $source = new StringSource($originalContent);
        $this->assertEquals(strlen($originalContent), $source->getLength());
    }

    public function testGetSubString(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $source = new StringSource($originalContent);
        $this->assertEquals(substr($originalContent,0,15), $source->getSubString(0,15));
        $this->assertEquals(substr($originalContent,10,15), $source->getSubString(10,15));
    }
}
?>