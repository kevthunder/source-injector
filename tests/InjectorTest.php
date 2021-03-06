<?php
use Kevthunder\SourceInjector\Injector;
use Kevthunder\SourceInjector\Test\Helper\FileTestTrait;

class InjectorTest extends PHPUnit_Framework_TestCase
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


    protected function resetTestFiles(){
        $this->removeTestFiles();
        $this->copyTestFiles();
    }

	
    public function testProperties()
    {
        $injector = new Injector($this->files_tmp.'/Foo.php',5,10);

        $this->assertEquals($this->files_tmp.'/Foo.php', $injector->getFileName());
        $this->assertEquals(5, $injector->getStart());
        $this->assertEquals(10, $injector->getEnd());
    }

    public function testGetAbsEnd(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php',5,10);
        $this->assertEquals(10, $injector->getAbsEnd());
        $this->assertEquals(strlen($originalContent), $injector->getAbsEnd(0));
        $this->assertEquals(strlen($originalContent)-10, $injector->getAbsEnd(-10));
    }

    public function testLengthAt(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $this->assertEquals(5, $injector->getLengthAt(0,5));
        $this->assertEquals(strlen($originalContent), $injector->getLengthAt(0,0));
        $this->assertEquals(strlen($originalContent)-5, $injector->getLengthAt(0,-5));
        $this->assertEquals(strlen($originalContent)-10, $injector->getLengthAt(5,-5));
    }

    public function testLength(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $this->assertEquals(strlen($originalContent), $injector->getLength());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,-5);
        $this->assertEquals(strlen($originalContent)-10, $injector->getLength());
    }

    public function testContentAt(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $this->assertEquals($originalContent, $injector->getContentAt(0,0));
        $this->assertEquals(substr($originalContent,0,15), $injector->getContentAt(0,15));
        $this->assertEquals(substr($originalContent,10,15), $injector->getContentAt(10,25));
        $this->assertEquals(substr($originalContent,10,strlen($originalContent)-35), $injector->getContentAt(10,-25));
    }

    public function testContent(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $this->assertEquals($originalContent, $injector->getContent());

        $injector = new Injector($this->files_tmp.'/Foo.php',10,-25);
        $this->assertEquals(substr($originalContent,10,strlen($originalContent)-35), $injector->getContent());
    }

    public function testCopy(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->copy();
        $this->assertEquals(10, $copy->getStart());
        $this->assertEquals(20, $copy->getEnd());

        $copy = $injector->copy(5);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(20, $copy->getEnd());

        $copy = $injector->copy(null,25);
        $this->assertEquals(10, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());

        $copy = $injector->copy(5,25);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());
    }

    public function testReset(){
        $injector = new Injector($this->files_tmp.'/Foo.php');

        $copy = $injector->copy(5,25);
        $reseted = $copy->reset();

        $this->assertEquals($injector->getStart(), $reseted->getStart());
        $this->assertEquals($injector->getEnd(), $reseted->getEnd());

        $copy = $injector->fail();
        $reseted = $copy->reset();

        $this->assertTrue($copy->failed());
        $this->assertFalse($reseted->failed());
    }

    public function testFail(){
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->fail();
        $this->assertTrue($copy->failed());
    }

    public function testAfter(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->after(5);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(20, $copy->getEnd());
    }

    public function testBefore(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->before(25);
        $this->assertEquals(10, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());
    }

    public function testOffset()
    {
        $injector = new Injector($this->files_tmp . '/Foo.php', 10, 20);

        $copy = $injector->offset(-5, 5);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());
    }
    
    public function testOffsetStart(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->offsetStart(-5);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(20, $copy->getEnd());
    }

    public function testOffsetEnd(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->offsetEnd(5);
        $this->assertEquals(10, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());
    }
    
    public function testBetween(){
        $injector = new Injector($this->files_tmp.'/Foo.php',10,20);

        $copy = $injector->between(5,25);
        $this->assertEquals(5, $copy->getStart());
        $this->assertEquals(25, $copy->getEnd());
    }

    public function testAfterFind(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function hello';
        $copy = $injector->afterFind($find);
        $this->assertFalse($copy->failed());
        $pos = strpos($originalContent,$find)+strlen($find);
        $this->assertEquals($pos, $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php',$pos,-1);
        $find = 'public function';
        $copy = $injector->afterFind($find);
        $this->assertFalse($copy->failed());
        $this->assertEquals(strpos($originalContent,$find,$pos)+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'non-existing text';
        $copy = $injector->afterFind($find);
        $this->assertTrue($copy->failed());
    }

    public function testBeforeFind(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function hello';
        $copy = $injector->beforeFind($find);
        $this->assertFalse($copy->failed());
        $pos = strpos($originalContent,$find);
        $this->assertEquals($pos, $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php',$pos,-1);
        $find = 'public function';
        $copy = $injector->beforeFind($find);
        $this->assertFalse($copy->failed());
        $this->assertEquals(strpos($originalContent,$find,$pos), $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'non-existing text';
        $copy = $injector->beforeFind($find);
        $this->assertTrue($copy->failed());
    }

    public function testAfterFindLast(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function';
        $copy = $injector->afterFindLast($find);
        $this->assertFalse($copy->failed());
        $pos = strrpos($originalContent,$find);
        $this->assertEquals($pos+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,$pos);
        $copy = $injector->afterFindLast($find);
        $this->assertFalse($copy->failed());
        $this->assertEquals(strrpos(substr($originalContent,0,$pos),$find)+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'non-existing text';
        $copy = $injector->afterFindLast($find);
        $this->assertTrue($copy->failed());
    }
    
    public function testBeforeFindLast(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function';
        $copy = $injector->beforeFindLast($find);
        $this->assertFalse($copy->failed());
        $pos = strrpos($originalContent,$find);
        $this->assertEquals($pos, $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,$pos);
        $copy = $injector->beforeFindLast($find);
        $this->assertFalse($copy->failed());
        $this->assertEquals(strrpos(substr($originalContent,0,$pos),$find), $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'non-existing text';
        $copy = $injector->beforeFindLast($find);
        $this->assertTrue($copy->failed());
    }

    public function testAfterNextLine(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $find = 'public function hello';
        $pos = strpos($originalContent,$find)+strlen($find);

        $injector = new Injector($this->files_tmp.'/Foo.php',$pos,-1);
        $copy = $injector->afterNextLine();
        $this->assertFalse($copy->failed());
        $this->assertEquals(strpos($originalContent,"\n",$pos)+1, $copy->getStart());
    }

    public function testAfterPregFind(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function hello';
        $copy = $injector->afterPregFind('/Public f\w*n hello/i');
        $this->assertFalse($copy->failed());
        $pos = strpos($originalContent,$find)+strlen($find);
        $this->assertEquals($pos, $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php',$pos,-1);
        $find = 'public function';
        $copy = $injector->afterPregFind('/Public f\w*n/i');
        $this->assertFalse($copy->failed());
        $this->assertEquals(strpos($originalContent,$find,$pos)+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->afterPregFind('/non-existing\s*text/');
        $this->assertTrue($copy->failed());
    }

    public function testAfterPregFindLast(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $find = 'public function';
        $copy = $injector->afterPregFindLast('/Public f\w*n/i');
        $this->assertFalse($copy->failed());
        $pos = strrpos($originalContent,$find);
        $this->assertEquals($pos+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,$pos);
        $copy = $injector->afterPregFindLast('/Public f\w*n/i');
        $this->assertFalse($copy->failed());
        $this->assertEquals(strrpos(substr($originalContent,0,$pos),$find)+strlen($find), $copy->getStart());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->afterPregFindLast('/non-existing\s*text/');
        $this->assertTrue($copy->failed());
    }


    public function testAroundPregFind(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        
        $find = '/public function bar\([^)]*\)/';
        preg_match($find, $originalContent, $matches, PREG_OFFSET_CAPTURE);
        $start = $matches[0][1];
        $end = $matches[0][1] + strlen($matches[0][0]);

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundPregFind($find);
        $this->assertEquals($start, $copy->getStart());
        $this->assertEquals($end, $copy->getEnd());
        
        $injector = new Injector($this->files_tmp.'/Foo.php',5);
        $copy = $injector->aroundPregFind($find);
        $this->assertEquals($start, $copy->getStart());
        $this->assertEquals($end, $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,$start);
        $copy = $injector->aroundPregFind($find);
        $this->assertTrue($copy->failed());
        
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundPregFind('/non-existing\s*text/');
        $this->assertTrue($copy->failed());
    }

    public function testAroundFind(){
        
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $find = 'public function hello()';
        $start = strrpos($originalContent,$find);
        $end = $start+strlen($find);

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundFind($find,false);
        $this->assertEquals($start, $copy->getStart());
        $this->assertEquals($end, $copy->getEnd());

        $injector = new Injector($this->files_tmp.'/Foo.php',5,$start);
        $copy = $injector->aroundFind($find,false);
        $this->assertTrue($copy->failed());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundFind('non-existing text',false);
        $this->assertTrue($copy->failed());
        
        $find = "public function hello(){\nreturn 'Hello, world!';\n}";
        preg_match('/public function hello\(\)\{[^}]*\}/', $originalContent, $matches, PREG_OFFSET_CAPTURE);
        $start = $matches[0][1];
        $end = $matches[0][1] + strlen($matches[0][0]);

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundFind($find,false);
        $this->assertTrue($copy->failed());

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $copy = $injector->aroundFind($find,true);
        $this->assertEquals($start, $copy->getStart());
        $this->assertEquals($end, $copy->getEnd());
        
    }
    
    
    public function testIndentAt(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $injector = new Injector($this->files_tmp.'/Foo.php');

        $find = "public function hello(){";
        $pos = strpos($originalContent,$find);
        $this->assertEquals("    ",$injector->getIndentAt($pos),'End of a line');

        $pos = strlen($originalContent);
        $this->assertEquals("",$injector->getIndentAt($pos),'End of a file');

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $this->assertEquals("        ",$injector->getIndentAt($pos),'Start of a line');

        $find = "\n    public function hello(){";
        $pos = strpos($originalContent,$find);
        $this->assertEquals("    ",$injector->getIndentAt($pos),'On empty line');

        $find = "\n    public function bar";
        $pos = strpos($originalContent,$find);
        $this->assertEquals("    ",$injector->getIndentAt($pos),'On multiple empty line');
    }

    public function testApplyIndentOf(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');
        $injector = new Injector($this->files_tmp.'/Foo.php');

        $find = "\n    public function hello(){";
        $pos = strpos($originalContent,$find);
        $insert = "// Lorem Ipsum\n// Dolor";
        $this->assertEquals("    // Lorem Ipsum\n    // Dolor",$injector->applyIndentOf($insert,$pos));

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $insert = "// Lorem Ipsum\n// Dolor";
        $this->assertEquals("        // Lorem Ipsum\n        // Dolor",$injector->applyIndentOf($insert,$pos));
    }
    
    public function testReplaceSegment(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        preg_match('/public function hello\(\)\{[^}]*\}/', $originalContent, $matches, PREG_OFFSET_CAPTURE);
        $start = $matches[0][1];
        $end = $matches[0][1] + strlen($matches[0][0]);

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $injector->replaceSegment($insert,$start,$end,false);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals(substr($originalContent,0,$start).$insert.substr($originalContent,$end),$newContent);

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "public function test(){\n    return true;\n}";
        $injector->replaceSegment($insert,$start,$end,false);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals(substr($originalContent,0,$start).$insert.substr($originalContent,$end),$newContent);

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "public function test(){\n    return true;\n}";
        $injector->replaceSegment($insert,$start,$end,true);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $expect = "public function test(){\n        return true;\n    }";
        $this->assertEquals(substr($originalContent,0,$start).$expect.substr($originalContent,$end),$newContent);
        
        $this->resetTestFiles();
        
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $copy = $injector->fail()->replaceSegment($insert,$start,$end,false);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertTrue($copy->failed());
        $this->assertNotEquals(substr($originalContent,0,$start).$insert.substr($originalContent,$end),$newContent);
        $this->assertEquals($originalContent,$newContent);
    }

    public function testInsertAt(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $injector->insertAt($insert,0,false);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,0,strlen($insert)));

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $injector->insertAt($insert,10,false);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,10,strlen($insert)));

        $this->resetTestFiles();

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "// Lorem Ipsum\n// Dolor";
        $injector->insertAt($insert,$pos,false);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,$pos,strlen($insert)));

        $this->resetTestFiles();

        $find = "\n    public function hello(){";
        $pos = strpos($originalContent,$find);
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "// Lorem Ipsum\n// Dolor";
        $injector->insertAt($insert,$pos,true);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $insert = "    // Lorem Ipsum\n    // Dolor";
        $this->assertEquals($insert,substr($newContent,$pos,strlen($insert)));

        $this->resetTestFiles();

        $find = "public function hello(){";
        $pos = strpos($originalContent,$find);
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "// Lorem Ipsum\n// Dolor\n";
        $injector->insertAt($insert,$pos,true);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $expect = "// Lorem Ipsum\n    // Dolor\n";
        $this->assertEquals($expect,substr($newContent,$pos,strlen($expect)));

        $this->resetTestFiles();

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "// Lorem Ipsum\n// Dolor";
        $injector->insertAt($insert,$pos,true);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $expect = "        // Lorem Ipsum\n        // Dolor";
        $this->assertEquals($expect,substr($newContent,$pos,strlen($expect)));

        $this->resetTestFiles();

        $find = "'bar' => 'Bar',";
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "'test1' => 'Test 1',\n'test2' => 'Test 2',\n";
        $injector->insertAt($insert,$pos,true);
        $expect = 'public $data = array('."\n".
            "        'foo' => 'Foo',\n".
            "        'bar' => 'Bar',\n".
            "        'test1' => 'Test 1',\n".
            "        'test2' => 'Test 2',\n".
            "        'foo_bar' => 'Foo bar'\n".
            "    );";
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $start = strpos($newContent,'public $data = array(');
        $end = strpos($newContent,');',$start)+2;
        $this->assertEquals($expect,substr($newContent,$start,$end-$start));
    }

    public function testPrepend(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $injector->prepend($insert,false);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,0,strlen($insert)));

        $this->resetTestFiles();

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $injector = new Injector($this->files_tmp.'/Foo.php',$pos);
        $insert = "// Lorem Ipsum\n// Dolor";
        $injector->prepend($insert,true);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $insert = "        // Lorem Ipsum\n        // Dolor";
        $this->assertEquals($insert,substr($newContent,$pos,strlen($insert)));

    }

    public function testAppend()
    {
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php',0,10);
        $insert = 'Lorem Ipsum';
        $injector->append($insert);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,10,strlen($insert)),'With positive end');

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'Lorem Ipsum';
        $injector->append($insert);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,strlen($originalContent),strlen($insert)),'With default Injector');

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php',0,-20);
        $insert = 'Lorem Ipsum';
        $injector->append($insert);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,strlen($originalContent)-20,strlen($insert)),'With negative end');

        $this->resetTestFiles();

        $find = 'public function hello(){';
        $pos = strpos($originalContent,$find)+strlen($find)+1;
        $injector = new Injector($this->files_tmp.'/Foo.php',0,$pos-strlen($originalContent));
        $insert = "// Lorem Ipsum\n// Dolor";
        $injector->append($insert,true);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $insert = "        // Lorem Ipsum\n        // Dolor";
        $this->assertEquals($insert,substr($newContent,$pos,strlen($insert)));

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php',0,10);
        $insert = 'Lorem Ipsum';
        $copy = $injector->fail()->append($insert);
        $this->assertTrue($copy->failed());
        $this->assertFileEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertNotEquals($insert,substr($newContent,10,strlen($insert)),'With positive end');
    }

    public function testContains(){
        $injector = new Injector($this->files_tmp.'/Foo.php');

        $find = 'public function hello(){';
        $this->assertTrue($injector->contains($find,false),'Contains function hello');

        $find = "public function hello(){\n".
                "    return 'Hello, world!';\n".
                "}";
        $this->assertFalse($injector->contains($find,false),'Fails to find function hello while sensitive to indentation');
        $this->assertTrue($injector->contains($find,true),'Find function hello while not sensitive to indentation');

        $find = 'non-existing text';
        $this->assertFalse($injector->contains($find,false),'Fails to find non-existent string');


        $injector = new Injector($this->files_tmp.'/Foo.php',0,10);
        $find = 'public function hello(){';
        $this->assertFalse($injector->contains($find,false),'Fails to find function hello while out of bound');
    }

    public function testReplace(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        preg_match('/public function hello\(\)\{[^}]*\}/', $originalContent, $matches, PREG_OFFSET_CAPTURE);
        $start = $matches[0][1];
        $end = $matches[0][1] + strlen($matches[0][0]);

        $injector = new Injector($this->files_tmp.'/Foo.php',$start,$end);
        $insert = 'Lorem Ipsum';
        $injector->replace($insert,false);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals(substr($originalContent,0,$start).$insert.substr($originalContent,$end),$newContent);

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php',$start,$end);
        $insert = "public function test(){\n    return true;\n}";
        $injector->replace($insert,false);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals(substr($originalContent,0,$start).$insert.substr($originalContent,$end),$newContent);

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php',$start,$end);
        $insert = "public function test(){\n    return true;\n}";
        $injector->replace($insert,true);
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $expect = "public function test(){\n        return true;\n    }";
        $this->assertEquals(substr($originalContent,0,$start).$expect.substr($originalContent,$end),$newContent);
        
    }



    public function testPrependOnce(){
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'non-existing text';
        $injector->prependOnce($insert,false);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,0,strlen($insert)));

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "public function hello(){\n".
                  "    return 'Hello, world!';\n".
                  "}";
        $injector->prependOnce($insert);
        $this->assertFileEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
    }

    public function testAppendOnce()
    {
        $originalContent = file_get_contents($this->files_source.'/Foo.php');

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = 'non-existing text';
        $injector->appendOnce($insert);
        $this->assertFileNotEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
        $newContent = file_get_contents($this->files_tmp.'/Foo.php');
        $this->assertEquals($insert,substr($newContent,strlen($originalContent),strlen($insert)));

        $this->resetTestFiles();

        $injector = new Injector($this->files_tmp.'/Foo.php');
        $insert = "public function hello(){\n".
                  "    return 'Hello, world!';\n".
                  "}";
        $injector->appendOnce($insert);
        $this->assertFileEquals($this->files_source.'/Foo.php',$this->files_tmp.'/Foo.php');
    }

    public function testRun()
    {
        $injector = new Injector($this->files_tmp.'/Foo.php');
        $tester = $this;
        $injector->run(function ($injector2) use ($injector,$tester){
            $tester->assertEquals($injector,$injector2);
        });
    }
}
?>