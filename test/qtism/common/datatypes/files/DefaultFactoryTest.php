<?php

use qtism\common\datatypes\files\DefaultFactory;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class DefaultFactoryTest extends QtiSmTestCase {

    public function testCreateMemoryFile() {
        $factory = new DefaultFactory();
        $mFile = $factory->createMemoryFile('Some text', 'text/plain', 'my.txt');
        
        $this->assertEquals('Some text', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertTrue($mFile->hasFilename());
        $this->assertEquals('my.txt', $mFile->getFilename());
    }
}