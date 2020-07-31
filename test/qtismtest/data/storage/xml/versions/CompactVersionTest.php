<?php

namespace qtismtest\data\storage\xml\versions;

use qtism\data\storage\xml\versions\CompactVersion;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtismtest\QtiSmTestCase;

class CompactVersionTest extends QtiSmTestCase
{
    public function testVersionCompareSupported()
    {
        $this->assertTrue(CompactVersion::compare('2.1', '2.1.0', '='));
    }

    public function testCreateWithSupportedVersion()
    {
        $version = '2.1';
        $patchedVersion = $version . '.0';
        
        $versionObject = CompactVersion::create($version);
        $this->assertInstanceOf(CompactVersion::class, $versionObject);
        $this->assertEquals($patchedVersion, (string)$versionObject);
    }
    
    public function testCreateWithUnsupportedVersionThrowsException()
    {
        $wrongVersion = '2.0';

        $this->expectException(QtiVersionException::class);
        CompactVersion::create($wrongVersion);
    }
}
