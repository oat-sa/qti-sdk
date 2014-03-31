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
    
    public function testEquals() {
        $mFile = new MemoryFile('Some text', 'text/plain', 'data.txt');
        $mFile2 = new MemoryFile('Some text', 'text/plain', 'data.txt');
        $this->assertTrue($mFile->equals($mFile2));
        
        $mFile3 = new MemoryFile('Some text', 'text/plain', 'mydata.txt');
        $this->assertFalse($mFile->equals($mFile3));
        
        $mFile4 = new MemoryFile('Some text', 'text/html', 'data.txt');
        $this->assertFalse($mFile->equals($mFile4));
        
        $mFile5 = new MemoryFile('Different text', 'text/plain', 'data.txt');
        $this->assertFalse($mFile->equals($mFile5));
    }
}