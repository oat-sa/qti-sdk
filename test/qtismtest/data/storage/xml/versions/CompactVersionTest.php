<?php

namespace qtismtest\data\storage\xml\versions;

use qtism\data\storage\xml\versions\CompactVersion;
use qtism\data\storage\xml\versions\CompactVersion21;
use qtism\data\storage\xml\versions\CompactVersion22;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtismtest\QtiSmTestCase;

/**
 * Class CompactVersionTest
 */
class CompactVersionTest extends QtiSmTestCase
{
    public function testVersionCompareSupported(): void
    {
        $this::assertTrue(CompactVersion::compare('2.1', '2.1.0', '='));
    }

    /**
     * @dataProvider versionsToCreate
     * @param string $version
     * @param string $expectedVersion
     * @param string $expectedClass
     */
    public function testCreateWithSupportedVersion(string $version, string $expectedVersion, string $expectedClass): void
    {
        $versionObject = CompactVersion::create($version);
        $this::assertInstanceOf($expectedClass, $versionObject);
        $this::assertEquals($expectedVersion, (string)$versionObject);
    }

    /**
     * @return array
     */
    public function versionsToCreate(): array
    {
        return [
            ['2.1', '2.1.0', CompactVersion21::class],
            ['2.1.1', '2.1.1', CompactVersion21::class],
            ['2.2', '2.2.0', CompactVersion22::class],
            ['2.2.1', '2.2.1', CompactVersion22::class],
            ['2.2.2', '2.2.2', CompactVersion22::class],
            ['2.2.3', '2.2.3', CompactVersion22::class],
            ['2.2.4', '2.2.4', CompactVersion22::class],
        ];
    }

    public function testCreateWithUnsupportedVersionThrowsException(): void
    {
        $wrongVersion = '2.0';

        $this->expectException(QtiVersionException::class);
        CompactVersion::create($wrongVersion);
    }
}
