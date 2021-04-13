<?php

namespace qtismtest\common\utils;

use InvalidArgumentException;
use qtism\common\utils\Version;
use qtismtest\QtiSmTestCase;

/**
 * Class VersionTest
 */
class VersionTest extends QtiSmTestCase
{
    /**
     * @dataProvider versionCompareValidProvider
     *
     * @param string $version1
     * @param string $version2
     * @param string|null $operator
     * @param mixed $expected
     */
    public function testVersionCompareValid($version1, $version2, $operator, $expected)
    {
        $this::assertSame($expected, Version::compare($version1, $version2, $operator));
    }

    public function testUnknownOperator()
    {
        $msg = "Unknown operator '!=='. Known operators are '<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne'.";
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);
        Version::compare('2.1.1', '2.2.0', '!==');
    }

    /**
     * @return array
     */
    public function versionCompareValidProvider(): array
    {
        return [
            ['2', '2', null, 0],
            ['2', '2.0', null, 0],
            ['2', '2.0.0', null, 0],
            ['2.0', '2', null, 0],
            ['2.0', '2.0', null, 0],
            ['2.0', '2.0.0', null, 0],
            ['2.0.0', '2', null, 0],
            ['2.0.0', '2.0', null, 0],
            ['2.0.0', '2.0.0', null, 0],
            ['2.0', '2.1', null, -1],
            ['2.0.0', '2.1', null, -1],
            ['2.2', '2.1.1', null, 1],
            ['2.2', '2.0.0', null, 1],
            ['2.0', '2.0.0', '=', true],
            ['2.0', '2.0', 'eq', true],
            ['2.0.0', '2.1.0', '<', true],
            ['2.0.0', '2.1.0', 'lt', true],
            ['2.1', '2.1.0', '<=', true],
            ['2.1.0', '2.1.0', 'le', true],
            ['2.2', '2.0', '>', true],
            ['2.2.0', '2.1', 'gt', true],
            ['2.2', '2.0.0', '>=', true],
            ['2.2', '2.2.0', 'ge', true],
            ['2.1', '2.1.0', '!=', false],
            ['2.1', '2.2', 'ne', true],
        ];
    }

    /**
     * Append patch version to $version if $version only contains
     * major and minor versions.
     * Also adds minor version if it's not present (defaults to 0).
     *
     * @dataProvider appendPatchVersionProvider
     * @param $originalVersion
     * @param $patchedVersion
     */
    public function testAppendPatchVersion($originalVersion, $patchedVersion)
    {
        $this::assertEquals($patchedVersion, Version::appendPatchVersion($originalVersion));
    }

    /**
     * @return array
     */
    public function appendPatchVersionProvider(): array
    {
        return [
            ['2', '2.0.0'],
            ['2.0', '2.0.0'],
            ['2.0.0', '2.0.0'],
            ['2.1', '2.1.0'],
            ['2.1.0', '2.1.0'],
            ['2.1.1', '2.1.1'],
            ['2.2', '2.2.0'],
            ['2.2.0', '2.2.0'],
            ['2.2.1', '2.2.1'],
            ['2.2.2', '2.2.2'],
            ['2.2.3', '2.2.3'],
            ['2.2.4', '2.2.4'],
            ['3', '3.0.0'],
            ['3.0', '3.0.0'],
            ['3.0.0', '3.0.0'],
        ];
    }

    public function testAppendPatchVersionWithNonSemanticVersionThrowsException()
    {
        $versionNumber = 'whatever';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Provided version number '" . $versionNumber . "' is not compliant to semantic versioning.");
        Version::appendPatchVersion($versionNumber);
    }
}
