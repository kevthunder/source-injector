<?php
use Kevthunder\SourceInjector\Source\FileSource;
use Kevthunder\SourceInjector\Test\Helper\FileTestTrait;

class FileSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $files_source;
    /**
     * @var string
     */
    public $files_tmp;


    use FileTestTrait;
    protected function setUp()
    {
        $this->files_source = __DIR__.'/_files';
        $this->files_tmp = __DIR__.'/_tmp';
        $this->copyTestFiles();
    }

    protected function tearDown()
    {
        $this->removeTestFiles();
    }


    public function testDetect(){
        $this->assertTrue(FileSource::detect($f = 'test_1/test.php'), $f);
        $this->assertTrue(FileSource::detect($f = 'with\window\style.txt'), $f);
        $this->assertTrue(FileSource::detect($f = 'C:\window\drive.txt'), $f);
        $this->assertTrue(FileSource::detect($f = '/../test_1/test.php'), $f);
        $this->assertTrue(FileSource::detect($f = 'no_ext'), $f);
        $this->assertTrue(FileSource::detect($f = '_start_under.js'), $f);
        $this->assertTrue(FileSource::detect($f = '_folder/start_under.js'), $f);
        $this->assertTrue(FileSource::detect($f = '../no_ext'), $f);
        $this->assertTrue(FileSource::detect($f = 'numberExt.a1a'), $f);
        $this->assertTrue(FileSource::detect($f = 'test/file_with.many.ext'), $f);
        $this->assertTrue(FileSource::detect($f = 'with-dash/more-dash.ext'), $f);
        $this->assertTrue(FileSource::detect($f = 'ThisIs/CamelCase.EXT'), $f);
        $this->assertfalse(FileSource::detect($f = '123BeginsWithNumber.5aa'), $f);
        $this->assertfalse(FileSource::detect($f = 'Lorem ipsum'), $f);
        $this->assertfalse(FileSource::detect($f = 'un.der_score_ext'), $f);
        $this->assertfalse(FileSource::detect($f = "multi\nLine"), $f);
    }

    public function testGetContent(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $source = new FileSource($this->files_tmp.'/Foo.php');
        $this->assertEquals($originalContent, $source->getContent());
    }

    public function testSetContent()
    {
        $source = new FileSource($this->files_tmp.'/Foo.php');
        $source->setContent('Lorem Ipsum');
        $this->assertEquals('Lorem Ipsum', file_get_contents($this->files_tmp . '/Foo.php'));
    }

    public function testGetLength(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $source = new FileSource($this->files_tmp.'/Foo.php');
        $this->assertEquals(strlen($originalContent), $source->getLength());
    }

    public function testGetSubString(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $source = new FileSource($this->files_tmp.'/Foo.php');
        $this->assertEquals(substr($originalContent,0,15), $source->getSubString(0,15));
        $this->assertEquals(substr($originalContent,10,15), $source->getSubString(10,15));
    }
}
?>