<?php
namespace Kevthunder\SourceInjector;


class Injector{

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     *
     * @param string $fileName
     * @param int    $start
     * @param int    $end
     */
    public function __construct($fileName,$start = 0,$end = 0)
    {
        $this->fileName = $fileName;
        $this->start = $start;
        $this->end = $end;
    }

    public function getFileName(){
        return $this->fileName;
    }
    public function getStart(){
        return $this->start;
    }
    public function getEnd(){
        return $this->end;
    }
    public function getAbsEnd($end = null){
        if(is_null($end)) $end = $this->end;
        if($end > 0) {
            return $end;
        }else{
            return filesize($this->fileName) + $end;
        }
    }

    public function getLength(){
        return $this->getLengthAt($this->start,$this->end);
    }
    public function getLengthAt($start,$end){
        return $this->getAbsEnd($end) - $start;
    }

    public function getContent(){
        return $this->getContentAt($this->start,$this->end);
    }
    public function getContentAt($start,$end){
        if($start == 0 && $end == 0){
            return file_get_contents($this->fileName);
        }
        $handle = fopen($this->fileName, 'r');
        fseek($handle, $start);
        $res = fread($handle, $this->getLengthAt($start,$end));
        fclose($handle);
        return $res;
    }

    public function copy($start = null,$end = null){
        return new Injector($this->fileName,is_null($start)?$this->start:$start,is_null($end)?$this->end:$end);
    }

    public function reset()
    {
        return $this->copy(0,0);
    }

    public function after($pos)
    {
        return $this->copy($pos);
    }

    public function before($pos)
    {
        return $this->copy(null,$pos);
    }

    public function between($start,$end)
    {
        return $this->copy($start,$end);
    }

    public function failed(){
        return false;
    }

    public function afterFind($needle){
        $pos = strpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy($this->start + $pos + strlen($needle));
        }else{
            return new FailedInjector($this->fileName);
        }
    }

    public function beforeFind($needle){
        $pos = strpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy(null,$this->start + $pos);
        }else{
            return new FailedInjector($this->fileName);
        }
    }

    public function afterFindLast($needle){
        $pos = strrpos($this->getContent(),$needle);
        if($pos !== false){
            return $this->copy($this->start + $pos + strlen($needle));
        }else{
            return new FailedInjector($this->fileName);
        }
    }
    public function afterNextLine(){
        return $this->afterFind("\n");
    }

    public function afterPregFind($pattern){
        if(preg_match($pattern, $this->getContent(), $matches, PREG_OFFSET_CAPTURE)){
            return $this->copy($this->start + $matches[0][1] + strlen($matches[0][0]));
        }else{
            return new FailedInjector($this->fileName);
        }
    }

    public function afterPregFindLast($pattern){
        $lastPos = 0;
        $content = $this->getContent();
        while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE,$lastPos?:0)){
            $lastPos = $matches[0][1] + strlen($matches[0][0]);
        }
        if($lastPos){
            return $this->copy($this->start + $lastPos);
        }else{
            return new FailedInjector($this->fileName);
        }
    }

    public function aroundFind($content, $ignoreIndent = true){
        if($ignoreIndent){
            return $this->aroundPregFind($this->getIgnoreIndentPreg($content));
        }else{
            $pos = strpos($this->getContent(),$content);
            if($pos !== false) {
                return $this->copy($this->start + $pos,$this->start + $pos + strlen($content));
            }else{
                return new FailedInjector($this->fileName);
            }
        }
    }

    public function aroundPregFind($pattern){
        if(preg_match($pattern, $this->getContent(), $matches, PREG_OFFSET_CAPTURE)){
            return $this->copy($this->start + $matches[0][1],$this->start + $matches[0][1] + strlen($matches[0][0]));
        }else{
            return new FailedInjector($this->fileName);
        }
    }

    public function prepend($content, $applyIndent = true)
    {
        return $this->insertAt($content,$this->start, $applyIndent);
    }

    public function prependOnce($content, $applyIndent = true)
    {
        if($this->reset()->contains($content, $applyIndent)){
            return $this;
        }else{
            return $this->prepend($content, $applyIndent);
        }
    }

    public function append($content, $applyIndent = true)
    {
        return $this->insertAt($content,$this->getAbsEnd(), $applyIndent);
    }

    public function appendOnce($content, $applyIndent = true)
    {
        if($this->reset()->contains($content, $applyIndent)){
            return $this;
        }else{
            return $this->append($content, $applyIndent);
        }
    }

    public function insertAt($content, $pos, $applyIndent = true)
    {
        return $this->replaceSegment($content, $pos, $pos, $applyIndent);
    }

    public function replace($content, $applyIndent = true)
    {
        return $this->replaceSegment($content, $this->start, $this->getAbsEnd(), $applyIndent);
    }
    
    
    public function replaceSegment($content, $start, $end, $applyIndent = true){
        if($applyIndent){
            $content = $this->applyIndentOf($content,$start);
        }
        $all = file_get_contents($this->fileName);
        file_put_contents($this->fileName,substr($all,0,$start).$content.substr($all,$end));
        return $this->copy(null,$this->end + strlen($content) - $end + $start);
    }

    public function contains($content, $ignoreIndent = true){
        if($ignoreIndent){
            return (bool)preg_match($this->getIgnoreIndentPreg($content),$this->getContent());
        }else{
            return strpos($this->getContent(),$content) !== false;
        }
    }
    
    protected function getIgnoreIndentPreg($content){
        return '/'.preg_replace('/\s*\n\s*/','\s*',preg_quote($content,'/')).'/';
    }

    public function getIndentAtStart(){
        return $this->getIndentAt($this->start);
    }

    public function getIndentAt($pos)
    {
        $all = file_get_contents($this->fileName);
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
    public function applyIndentOf($content,$pos){
        $onNewLine = $this->getContentAt($pos-1,$pos) == "\n";
        $indent = $this->getIndentAt($pos);
        $content = preg_replace('/\n(.)/',"\n$indent$1",$content);
        if($onNewLine){
            $content = $indent . $content;
        }
        return $content;
    }

    public function run($callback){
        call_user_func($callback,$this);
        return $this;
    }

}