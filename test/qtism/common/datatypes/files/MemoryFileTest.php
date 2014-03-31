<?php

use qtism\common\datatypes\files\MemoryFile;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class MemoryFileTest extends QtiSmTestCase {
    
    public function testInstantiationNoFilename() {
        $mFile = new MemoryFile('Some text', 'text/plain');
        $this->assertEquals('Some text', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertFalse($mFile->hasFilename());
    }
    
    public function testInstantiationWithFilename() {
        $mFile = new MemoryFile('Some text', 'text/plain', 'data.txt');
        $this->assertEquals('Some text', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertTrue($mFile->hasFilename());
    }
}