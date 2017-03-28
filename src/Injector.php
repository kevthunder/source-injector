<?php
namespace Kevthunder\SourceInjector;


use Kevthunder\SourceInjector\Source\FileSource;
use Kevthunder\SourceInjector\Source\StringSource;
use Kevthunder\SourceInjector\Source\ISource;

class Injector{

    /**
     * @var ISource
     */
    protected $source;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;
    
    public static $detectedSources = array(
        FileSource::class,
        StringSource::class
    );

    /**
     *
     * @param $source
     * @param int    $start
     * @param int    $end
     */
    public function __construct($source,$start = 0,$end = 0)
    {
        $this->source = $this->detectSource($source);
        $this->start = $start;
        $this->end = $end;
    }

    protected function getDetectedSources()
    {
        return self::$detectedSources;
    }

    /**
     * @param $input
     * @return ISource
     */
    protected function detectSource($input){
        if($input instanceof ISource){
            return $input;
        }
        foreach ($this->getDetectedSources() as $sourceClass) {
            if (call_user_func("$sourceClass::detect", $input)) {
                return new $sourceClass($input);
            }
        }
        throw(new \InvalidArgumentException('Could not find a source handler for fist argument of injector'));
    }

    /**
     * @return ISource
     */
    public function getSource(){
        return $this->source;
    }

    /**
     * @return null|string
     */
    public function getFileName(){
        if (method_exists($this->source, 'getFileName')) {
            return $this->source->getFileName();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getStart(){
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(){
        return $this->end;
    }

    /**
     * @param null $end
     * @return int
     */
    public function getAbsEnd($end = null){
        if(is_null($end)) $end = $this->end;
        if($end > 0) {
            return $end;
        }else{
            return $this->source->getLength() + $end;
        }
    }

    /**
     * @return int
     */
    public function getLength(){
        return $this->getLengthAt($this->start,$this->end);
    }

    /**
     * @param int $start
     * @param int $end
     * @return int
     */
    public function getLengthAt($start,$end){
        return $this->getAbsEnd($end) - $start;
    }

    /**
     * @return string
     */
    public function getContent(){
        return $this->getContentAt($this->start,$this->end);
    }

    /**
     * @param int $start
     * @param int $end
     * @return string
     */
    public function getContentAt($start,$end){
        if($start == 0 && $end == 0){
            return $this->source->getContent();
        }
        
        return $this->source->getSubString($start,$this->getLengthAt($start,$end));
    }

    /**
     * @param int|null $start
     * @param int|null $end
     * @return Injector
     */
    public function copy($start = null, $end = null)
    {
        return new Injector($this->source, is_null($start) ? $this->start : $start, is_null($end) ? $this->end : $end);
    }

    /**
     * @return Injector
     */
    public function reset()
    {
        return new Injector($this->source,0,0);
    }

    /**
     * @param int $pos
     * @return Injector
     */
    public function after($pos)
    {
        return $this->copy($pos);
    }

    /**
     * @param int $pos
     * @return Injector
     */
    public function before($pos)
    {
        return $this->copy(null,$pos);
    }

    /**
     * @param int $offsetStart
     * @param int $offsetEnd
     * @return Injector
     */
    public function offset($offsetStart, $offsetEnd)
    {
        return $this->copy($this->start + $offsetStart, $this->end + $offsetEnd);
    }

    /**
     * @param int $offset
     * @return Injector
     */
    public function offsetStart($offset)
    {
        return $this->copy($this->start + $offset);
    }

    /**
     * @param int $offset
     * @return Injector
     */
    public function offsetEnd($offset)
    {
        return $this->copy(null, $this->end + $offset);
    }

    /**
     * @param int $start
     * @param int $end
     * @return Injector
     */
    public function between($start,$end)
    {
        return $this->copy($start,$end);
    }

    /**
     * @return bool
     */
    public function failed(){
        return false;
    }

    /**
     * @return FailedInjector
     */
    public function fail(){
        return new FailedInjector($this->source);
    }

    /**
     * @param string $needle
     * @return Injector
     */
    public function afterFind($needle){
        $pos = strpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy($this->start + $pos + strlen($needle));
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $needle
     * @return Injector
     */
    public function beforeFind($needle){
        $pos = strpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy(null,$this->start + $pos);
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $needle
     * @return Injector
     */
    public function afterFindLast($needle){
        $pos = strrpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy($this->start + $pos + strlen($needle));
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $needle
     * @return Injector
     */
    public function beforeFindLast($needle){
        $pos = strrpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy(null, $this->start + $pos);
        }else{
            return $this->fail();
        }
    }

    /**
     * @return Injector
     */
    public function afterNextLine(){
        return $this->afterFind("\n");
    }

    /**
     * @param string $pattern
     * @return Injector
     */
    public function afterPregFind($pattern){
        if(preg_match($pattern, $this->getContent(), $matches, PREG_OFFSET_CAPTURE)){
            return $this->copy($this->start + $matches[0][1] + strlen($matches[0][0]));
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $pattern
     * @return Injector
     */
    public function afterPregFindLast($pattern){
        $lastPos = 0;
        $content = $this->getContent();
        while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE,$lastPos?:0)){
            $lastPos = $matches[0][1] + strlen($matches[0][0]);
        }
        if($lastPos){
            return $this->copy($this->start + $lastPos);
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $content
     * @param bool $ignoreIndent
     * @return Injector
     */
    public function aroundFind($content, $ignoreIndent = true){
        if($ignoreIndent){
            return $this->aroundPregFind($this->getIgnoreIndentPreg($content));
        }else{
            $pos = strpos($this->getContent(),$content);
            if($pos !== false) {
                return $this->copy($this->start + $pos,$this->start + $pos + strlen($content));
            }else{
                return $this->fail();
            }
        }
    }

    /**
     * @param string $pattern
     * @return Injector
     */
    public function aroundPregFind($pattern){
        if(preg_match($pattern, $this->getContent(), $matches, PREG_OFFSET_CAPTURE)){
            return $this->copy($this->start + $matches[0][1],$this->start + $matches[0][1] + strlen($matches[0][0]));
        }else{
            return $this->fail();
        }
    }

    /**
     * @param string $content
     * @param bool $applyIndent
     * @return Injector
     */
    public function prepend($content, $applyIndent = true)
    {
        return $this->insertAt($content,$this->start, $applyIndent);
    }

    /**
     * @param string $content
     * @param bool $applyIndent
     * @return Injector
     */
    public function prependOnce($content, $applyIndent = true)
    {
        if($this->reset()->contains($content, $applyIndent)){
            return $this;
        }else{
            return $this->prepend($content, $applyIndent);
        }
    }

    /**
     * @param string $content
     * @param bool $applyIndent
     * @return Injector
     */
    public function append($content, $applyIndent = true)
    {
        return $this->insertAt($content,$this->getAbsEnd(), $applyIndent);
    }

    /**
     * @param string $content
     * @param bool $applyIndent
     * @return Injector
     */
    public function appendOnce($content, $applyIndent = true)
    {
        if($this->reset()->contains($content, $applyIndent)){
            return $this;
        }else{
            return $this->append($content, $applyIndent);
        }
    }

    /**
     * @param string $content
     * @param int $pos
     * @param bool $applyIndent
     * @return Injector
     */
    public function insertAt($content, $pos, $applyIndent = true)
    {
        return $this->replaceSegment($content, $pos, $pos, $applyIndent);
    }

    /**
     * @param string $content
     * @param bool $applyIndent
     * @return Injector
     */
    public function replace($content, $applyIndent = true)
    {
        return $this->replaceSegment($content, $this->start, $this->getAbsEnd(), $applyIndent);
    }

    /**
     * @param string $content
     * @param int $start
     * @param int $end
     * @param bool $applyIndent
     * @return Injector
     */
    public function replaceSegment($content, $start, $end, $applyIndent = true){
        if($applyIndent){
            $content = $this->applyIndentOf($content,$start);
        }
        $all = $this->source->getContent();
        $this->source->setContent(substr($all, 0, $start) . $content . substr($all, $end));
        return $this->copy(null,$this->end + strlen($content) - $end + $start);
    }

    /**
     * @param string $content
     * @param bool $ignoreIndent
     * @return bool
     */
    public function contains($content, $ignoreIndent = true){
        if($ignoreIndent){
            return (bool)preg_match($this->getIgnoreIndentPreg($content),$this->getContent());
        }else{
            return strpos($this->getContent(),$content) !== false;
        }
    }

    /**
     * @param string $content
     * @return string
     */
    protected function getIgnoreIndentPreg($content){
        return '/'.preg_replace('/\s*\n\s*/','\s*',preg_quote($content,'/')).'/';
    }

    /**
     * @return string
     */
    public function getIndentAtStart(){
        return $this->getIndentAt($this->start);
    }

    /**
     * @param int $pos
     * @return string
     */
    public function getIndentAt($pos)
    {
        $all = $this->source->getContent();
        if(preg_match('/([ \t]*)[^\n]*[\r\n]+$/',substr($all,0,$pos+1),$match)) {
            return $match[1];
        }else{
            $nlPos = strrpos($all, "\n", $pos - strlen($all) - 1);
            if (preg_match('/^[ \t]*/', substr($all, $nlPos + 1), $match)) {
                return $match[0];
            } else {
                return '';
            }
        }
    }

    /**
     * @return string
     */
    public function getAllContent(){
        return $this->source->getContent();
    }

    /**
     * @param string $content
     * @param int $pos
     * @return string
     */
    public function applyIndentOf($content,$pos){
        $onNewLine = $this->getContentAt($pos-1,$pos) == "\n";
        $indent = $this->getIndentAt($pos);
        $content = preg_replace('/\n(.)/',"\n$indent$1",$content);
        if($onNewLine){
            $content = $indent . $content;
        }
        return $content;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function run($callback){
        call_user_func($callback,$this);
        return $this;
    }

}