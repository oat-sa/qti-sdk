<?php

namespace qtismtest\data\storage\xml\versions;

use qtism\data\storage\xml\versions\QtiVersionException;
use qtism\data\storage\xml\versions\ResultVersion;
use qtism\data\storage\xml\versions\ResultVersion21;
use qtism\data\storage\xml\versions\ResultVersion22;
use qtismtest\QtiSmTestCase;

/**
 * Class ResultVersionTest
 */
class ResultVersionTest extends QtiSmTestCase
{
    public function testVersionCompareSupported(): void
    {
        $this::assertTrue(ResultVersion::compare('2.1', '2.1.0', '='));
    }

    /**
     * @dataProvider versionsToCreate
     * @param string $version
     * @param string $expectedVersion
     * @param string $expectedClass
     */
    public function testCreateWithSupportedVersion(string $version, string $expectedVersion, string $expectedClass): void
    {
        $versionObject = ResultVersion::create($version);
        $this::assertInstanceOf($expectedClass, $versionObject);
        $this::assertEquals($expectedVersion, (string)$versionObject);
    }

    /**
     * @return array
     */
    public function versionsToCreate(): array
    {
        return [
            ['2.1', '2.1.0', ResultVersion21::class],
            ['2.1.1', '2.1.1', ResultVersion21::class],
            ['2.2', '2.2.0', ResultVersion22::class],
            ['2.2.1', '2.2.1', ResultVersion22::class],
            ['2.2.2', '2.2.2', ResultVersion22::class],
            ['2.2.3', '2.2.3', ResultVersion22::class],
            ['2.2.4', '2.2.4', ResultVersion22::class],
        ];
    }

    public function testCreateWithUnsupportedVersionThrowsException(): void
    {
        $wrongVersion = '2.0';

        $this->expectException(QtiVersionException::class);
        ResultVersion::create($wrongVersion);
    }
}
