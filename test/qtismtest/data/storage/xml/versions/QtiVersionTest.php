<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\versions;

use qtism\data\storage\xml\versions\QtiVersion;
use qtism\data\storage\xml\versions\QtiVersion200;
use qtism\data\storage\xml\versions\QtiVersion210;
use qtism\data\storage\xml\versions\QtiVersion211;
use qtism\data\storage\xml\versions\QtiVersion220;
use qtism\data\storage\xml\versions\QtiVersion221;
use qtism\data\storage\xml\versions\QtiVersion222;
use qtism\data\storage\xml\versions\QtiVersion223;
use qtism\data\storage\xml\versions\QtiVersion224;
use qtism\data\storage\xml\versions\QtiVersion300;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtismtest\QtiSmTestCase;

/**
 * Class QtiVersionTest
 */
class QtiVersionTest extends QtiSmTestCase
{
    public function testVersionCompareValid(): void
    {
        $this::assertTrue(QtiVersion::compare('2', '2.0.0', '='));
        $this::assertFalse(QtiVersion::compare('2', '2.1', '='));
    }

    public function testVersionCompareInvalidVersion1(): void
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "2.2.3", "2.2.4", "3.0.0".';
        $this->expectException(QtiVersionException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.4', '2.1.1', '>');
    }

    public function testVersionCompareInvalidVersion2(): void
    {
        $msg = 'QTI version "2.1.4" is not supported. Supported versions are "2.0.0", "2.1.0", "2.1.1", "2.2.0", "2.2.1", "2.2.2", "2.2.3", "2.2.4", "3.0.0".';
        $this->expectException(QtiVersionException::class);
        $this->expectExceptionMessage($msg);
        QtiVersion::compare('2.1.0', '2.1.4', '<');
    }

    /**
     * @dataProvider versionsToCreate
     * @param string $version
     * @param string $expectedVersion
     * @param string $expectedClass
     */
    public function testCreateWithSupportedVersion(string $version, string $expectedVersion, string $expectedClass): void
    {
        $versionObject = QtiVersion::create($version);
        $this::assertInstanceOf($expectedClass, $versionObject);
        $this::assertEquals($expectedVersion, (string)$versionObject);
    }

    /**
     * @return array
     */
    public function versionsToCreate(): array
    {
        return [
            ['2', '2.0.0', QtiVersion200::class],
            ['2.1', '2.1.0', QtiVersion210::class],
            ['2.1.1', '2.1.1', QtiVersion211::class],
            ['2.2', '2.2.0', QtiVersion220::class],
            ['2.2.1', '2.2.1', QtiVersion221::class],
            ['2.2.2', '2.2.2', QtiVersion222::class],
            ['2.2.3', '2.2.3', QtiVersion223::class],
            ['2.2.4', '2.2.4', QtiVersion224::class],
            ['3', '3.0.0', QtiVersion300::class],
        ];
    }

    public function testCreateWithUnsupportedVersionThrowsException(): void
    {
        $wrongVersion = '36.15';

        $this->expectException(QtiVersionException::class);
        QtiVersion::create($wrongVersion);
    }
}
