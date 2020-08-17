<?php

namespace qtismtest\data\storage\xml\versions;

use qtism\data\storage\xml\versions\CompactVersion;
use qtism\data\storage\xml\versions\CompactVersion21;
use qtism\data\storage\xml\versions\CompactVersion22;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtismtest\QtiSmTestCase;

class CompactVersionTest extends QtiSmTestCase
{
    public function testVersionCompareSupported()
    {
        $this->assertTrue(CompactVersion::compare('2.1', '2.1.0', '='));
    }

    /**
     * @dataProvider versionsToCreate
     * @param string $version
     * @param string $expectedVersion
     * @param string $expectedClass
     */
    public function testCreateWithSupportedVersion(string $version, string $expectedVersion, string $expectedClass)
    {
        $versionObject = CompactVersion::create($version);
        $this->assertInstanceOf($expectedClass, $versionObject);
        $this->assertEquals($expectedVersion, (string)$versionObject);
    }

    public function versionsToCreate(): array
    {
        return [
            ['2.1', '2.1.0', CompactVersion21::class],
            ['2.1.1', '2.1.1', CompactVersion21::class],
            ['2.2', '2.2.0', CompactVersion22::class],
            ['2.2.1', '2.2.1', CompactVersion22::class],
            ['2.2.2', '2.2.2', CompactVersion22::class],
        ];
    }

    public function testCreateWithUnsupportedVersionThrowsException()
    {
        $wrongVersion = '2.0';

        $this->expectException(QtiVersionException::class);
        CompactVersion::create($wrongVersion);
    }
}
