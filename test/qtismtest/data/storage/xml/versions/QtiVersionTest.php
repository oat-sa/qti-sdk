<?php

namespace qtismtest\data\storage\xml\versions;

use InvalidArgumentException;
use qtism\data\storage\xml\versions\QtiVersion;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtismtest\QtiSmTestCase;

class QtiVersionTest extends QtiSmTestCase
{
    public function testVersionCompareSupported()
    {
        $this->assertTrue(QtiVersion::compare('2', '2.0.0', '='));
    }

    public function testVersionCompareInvalidVersion1()
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "3.0.0".';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.4', '2.1.1', '>');
    }

    public function testVersionCompareInvalidVersion2()
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "3.0.0".';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.0', '2.1.4', '<');
    }
    
    public function testCreateWithSupportedVersion()
    {
        $version = '2.1';
        $patchedVersion = $version . '.0';
        
        $versionObject = QtiVersion::create($version);
        $this->assertInstanceOf(QtiVersion::class, $versionObject);
        $this->assertEquals($patchedVersion, (string)$versionObject);
    }
    
    public function testCreateWithUnsupportedVersionThrowsException()
    {
        $wrongVersion = '36.15';

        $this->expectException(QtiVersionException::class);
        QtiVersion::create($wrongVersion);
    }
}
