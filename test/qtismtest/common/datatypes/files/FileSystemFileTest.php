<?php
namespace qtismtest\common\datatypes\files;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\files\FileSystemFile;

class FileSystemFileTest extends QtiSmTestCase {
    
    /**
     * @dataProvider retrieveProvider
     * 
     * @param string $path The path to the QTI file instance.
     */
    public function testRetrieve($path, $expectedFilename, $expectedMimeType, $expectedData) {
        $pFile = FileSystemFile::retrieveFile($path);
        $this->assertEquals($expectedFilename, $pFile->getFilename());
        $this->assertEquals($expectedMimeType, $pFile->getMimeType());
        $this->assertEquals($expectedData, $pFile->getData());
    }
    
    /**
     * @dataProvider createFromExistingFileProvider
     * 
     * @param string $source
     * @param string $mimeType
     * @param boolean|string $withFilename
     */
    public function testCreateFromExistingFile($source, $mimeType, $withFilename = true)
    {
        $destination = tempnam('/tmp', 'qtism');
        $pFile = FileSystemFile::createFromExistingFile($source, $destination, $mimeType, $withFilename);
        
        $expectedContent = file_get_contents($source);
        
        if ($withFilename === true) {
            // Check if the name is the original one.
            $pathinfo = pathinfo($source);
            $this->assertEquals($pathinfo['basename'], $pFile->getFilename());
        }
        else {
            $this->assertEquals($withFilename, $pFile->getFilename());
        }
        
        $this->assertEquals($expectedContent, $pFile->getData());
        $this->assertEquals($mimeType, $pFile->getMimeType());
        
        unlink($destination);
    }
    
    public function testCreateFromExistingFileMalformedDestinationPath()
    {
        try {
            FileSystemFile::createFromExistingFile(
                self::samplesDir() . 'datatypes/file/text-plain_name.txt',
                '/root/root/root/root.txt',
                'text/plain'
            );
            
            $this->assertFalse(true, "Should throw an error.");
        } catch (\RuntimeException $e) {
            $this->assertEquals("Unable to create destination directory at '/root/root/root'.", $e->getMessage());
        }
    }
    
    public function testCreateFromExistingFileMalformedDestinationPathTwo()
    {
        try {
            FileSystemFile::createFromExistingFile(
                self::samplesDir() . 'datatypes/file/text-plain_name.txt',
                sys_get_temp_dir() . '/abcd\\t/v**',
                'text/plain'
            );
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf('\\RuntimeException', $e);
        }
    }
    
    public function testCreateFromExistingFileSourceIsNotAFile()
    {
        try {
            FileSystemFile::createFromExistingFile(
                self::samplesDir() . 'datatypes/file',
                '/root/root/root/root.txt',
                'text/plain'
            );
            $this->assertFalse(true, "Should throw an error.");
        } catch (\RuntimeException $e) {
            $this->assertEquals("Unable to find source file at '/home/jerome/Development/QTI/qtisdk/test/qtismtest/../samples/datatypes/file'.", $e->getMessage());
        }
    }
    
    /**
     * @dataProvider getStreamProvider
     * @depends testRetrieve
     * 
     * @param string $path
     * @param string $expectedData
     */
    public function testGetStream($path, $expectedData)
    {
        $pFile = FileSystemFile::retrieveFile($path);
        $stream = $pFile->getStream();
        
        $data = '';
        
        while (!feof($stream)) {
            $data .= fread($stream, 2048);
        }
        
        @fclose($stream);
        
        $this->assertEquals($expectedData, $data);
    }
    
    public function testGetStreamError()
    {
        $path = tempnam(sys_get_temp_dir(), 'qtism');
        file_put_contents(
            $path,
            file_get_contents(self::samplesDir() . 'datatypes/file/text-plain_name.txt')
        );
        
        $pFile = FileSystemFile::retrieveFile($path);
        @unlink($path);
        
        try {
            $pFile->getStream();
            $this->assertFalse(true, "calling FileSystemFile::getStream() on a non-existing file must throw an exception!");
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testInstantiationWrongPath()
    {
        $this->setExpectedException('\\RuntimeException');
        new FileSystemFile('/qtism/test');
    }
    
    public function retrieveProvider()
    {
        return array(
            array(self::samplesDir() . 'datatypes/file/text-plain_name.txt', 'yours.txt', 'text/plain', ''),
            array(self::samplesDir() . 'datatypes/file/text-plain_noname.txt', '', 'text/plain', ''),
            array(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt', 'text.txt', 'text/plain', 'Some text...'),
        );
    }
    
    public function getStreamProvider()
    {
        return array(
            array(self::samplesDir() . 'datatypes/file/text-plain_name.txt', ''),          
            array(self::samplesDir() . 'datatypes/file/text-plain_noname.txt', ''),
            array(self::samplesDir() . 'datatypes/file/text-plain_text_data.txt', 'Some text...'),
        );
    }
    
    public function createFromExistingFileProvider()
    {
        return array(
            array(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', true),
            array(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'new-name.txt'),
        );
    }
    
    public function testEqualsNonQtiFile()
    {
        $destination = tempnam('/tmp', 'qtism');
        $pFile = FileSystemFile::createFromExistingFile(
            self::samplesDir() . 'datatypes/file/text-plain_name.txt',
            $destination,
            'text/plain'
        );
        $this->assertFalse($pFile->equals(new \stdClass()));
        @unlink($destination);
    }
    
    public function testEqualsMimeTypeDifference()
    {
        $destination1 = tempnam('/tmp', 'qtism');
        $pFile1 = FileSystemFile::createFromExistingFile(
            self::samplesDir() . 'datatypes/file/text-plain_name.txt',
            $destination1,
            'text/plain'
        );
    
        $destination2 = tempnam('/tmp', 'qtism');
        $pFile2 = FileSystemFile::createFromExistingFile(
            self::samplesDir() . 'datatypes/file/text-css_name.txt',
            $destination2,
            'text/css'
        );
        
        $this->assertFalse($pFile1->equals($pFile2));
        @unlink($destination1);
        @unlink($destination2);
    }
}
