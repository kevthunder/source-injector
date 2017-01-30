<?php
namespace Kevthunder\SourceInjector;


class FailedInjector extends Injector{

    /**
     *
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        parent::__construct($fileName,null,null);
    }

    public function copy($start = null,$end = null){
        return new FailedInjector($this->source);
    }

    public function failed(){
        return true;
    }

    public function replaceSegment($content, $start, $end, $applyIndent = true){
        return $this;
    }

}