<?php


use qtism\common\datatypes\files\FileSystemFileManager;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class FileSystemFileManagerTest extends QtiSmTestCase {
    
    public function testCreateFromFile() {
        $manager = new FileSystemFileManager(sys_get_temp_dir());
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        
        // Created in temp dir?
        $this->assertTrue(strpos($mFile->getPath(), sys_get_temp_dir()) !== false);
        
        $this->assertEquals('I contain some text...', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertEquals('newname.txt', $mFile->getFilename());
        
        unlink($mFile->getPath());
    }
    
    public function testDelete() {
        $manager = new FileSystemFileManager(sys_get_temp_dir());
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        
        $this->assertTrue(is_file($mFile->getPath()));
        $manager->delete($mFile);
        $this->assertFalse(is_file($mFile->getPath()));
    }
}