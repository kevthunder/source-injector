<?php
namespace Kevthunder\SourceInjector\Source;

interface ISource {
    /**
     * @return string
     */
    public function getContent();
    
    /**
     * @param string $content
     */
    public function setContent($content);
    
    /**
     * @return int
     */
    public function getLength();
    
    /**
     * @param int $start
     * @param int $length
     * @return string
     */
    public function getSubString($start, $length);
}