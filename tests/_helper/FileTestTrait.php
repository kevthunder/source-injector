<?php
namespace Kevthunder\SourceInjector\Test\Helper;


trait FileTestTrait
{


    protected function copyTestFiles(){
        foreach(scandir($this->files_source) as $filename){
            if(!in_array($filename,['.','..'])) {
                copy($this->files_source . '/' . $filename, $this->files_tmp . '/' . $filename);
            }
        }
    }

    protected function removeTestFiles(){
        foreach(scandir($this->files_tmp) as $filename){
            if(!in_array($filename,['.','..'])) {
                unlink($this->files_tmp.'/'.$filename);
            }
        }
    }
}