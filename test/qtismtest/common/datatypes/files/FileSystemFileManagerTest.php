<?php

namespace qtismtest\common\datatypes\files;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtismtest\QtiSmTestCase;

class FileSystemFileManagerTest extends QtiSmTestCase
{
    public function testCreateFromFile()
    {
        $manager = new FileSystemFileManager();
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');

        // Created in temp dir?
        $this->assertTrue(strpos($mFile->getPath(), sys_get_temp_dir()) !== false);

        $this->assertEquals('I contain some text...', $mFile->getData());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertEquals('newname.txt', $mFile->getFilename());

        unlink($mFile->getPath());
    }

    public function testCreateFromData()
    {
        $manager = new FileSystemFileManager();
        $file = $manager->createFromData('Some <em>text</em>...', 'text/html');

        $this->assertEquals('Some <em>text</em>...', $file->getData());
        $this->assertEquals('text/html', $file->getMimeType());

        $manager->delete($file);
    }

    /**
     * @depends testCreateFromFile
     */
    public function testCreateFromFileError()
    {
        $manager = new FileSystemFileManager('/root');

        $this->setExpectedException(
            'qtism\\common\\datatypes\\files\\FileManagerException',
            'An error occurred while creating a QTI FileSystemFile object.'
        );

        $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
    }

    public function testCreateFromDataError()
    {
        $manager = new FileSystemFileManager('/root');

        $this->setExpectedException(
            'qtism\\common\\datatypes\\files\\FileManagerException',
            'An error occurred while creating a QTI FileSystemFile object.'
        );

        $manager->createFromData('Some <em>text</em>...', 'text/html');
    }

    public function testDelete()
    {
        $manager = new FileSystemFileManager();
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');

        $this->assertTrue(is_file($mFile->getPath()));
        $manager->delete($mFile);
        $this->assertFalse(is_file($mFile->getPath()));
    }

    /**
     * @depends testDelete
     * @depends testCreateFromFile
     */
    public function testRetrieve()
    {
        $manager = new FileSystemFileManager();
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        $mFile = $manager->retrieve($mFile->getIdentifier());
        $this->assertEquals('text/plain', $mFile->getMimeType());
        $this->assertEquals('newname.txt', $mFile->getFilename());
        $this->assertEquals('I contain some text...', $mFile->getData());
        $manager->delete($mFile);
    }

    /**
     * @depends testDelete
     */
    public function testDeleteError()
    {
        $manager = new FileSystemFileManager();
        $mFile = $manager->createFromFile(self::samplesDir() . 'datatypes/file/raw/text.txt', 'text/plain', 'newname.txt');
        unlink($mFile->getPath());

        $this->setExpectedException(
            'qtism\\common\\datatypes\\files\\FileManagerException'
        );

        $manager->delete($mFile);
    }
}
