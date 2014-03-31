<?php

use qtism\common\datatypes\files\DefaultFileManager;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class DefaultFactoryTest extends QtiSmTestCase {

    public function testCreateMemoryFile() {
        $manager = new DefaultFileManager(sys_get_temp_dir());
        $mFile = $manager->createMemoryFile('Some text', 'text/plain', 'my.txt');
        
        $this->assertEquals('Some text', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertTrue($mFile->hasFilename());
        $this->assertEquals('my.txt', $mFile->getFilename());
    }
    
    public function testCreatePersistentFile() {
        $manager = new DefaultFileManager(sys_get_temp_dir());
        $mFile = $manager->createPersistentFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        
        // Created in temp dir?
        $this->assertTrue(strpos($mFile->getPath(), sys_get_temp_dir()) !== false);
        
        $this->assertEquals('I contain some text...', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertEquals('newname.txt', $mFile->getFilename());
        
        unlink($mFile->getPath());
    }
    
    public function testDeletePersistentFile() {
        $manager = new DefaultFileManager(sys_get_temp_dir());
        $mFile = $manager->createPersistentFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        
        $this->assertTrue(is_file($mFile->getPath()));
        $manager->deletePersistentFile($mFile);
        $this->assertFalse(is_file($mFile->getPath()));
    }
}