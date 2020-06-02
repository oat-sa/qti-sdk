<?php

namespace qtismtest\runtime\storage\binary;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\storage\MemoryStream;
use qtism\runtime\storage\binary\QtiBinaryStreamAccess;
use qtism\runtime\storage\binary\QtiBinaryVersion;
use qtismtest\QtiSmTestCase;

class QtiBinaryVersionTest extends QtiSmTestCase
{
    public function testPersist()
    {
        $stream = new MemoryStream();
        $stream->open();
        $access = new QtiBinaryStreamAccess($stream, new FileSystemFileManager());

        $subject = new QtiBinaryVersion();
        $subject->persist($access);

        // Persist must alwas persist the current version and the current branch.
        $this->assertEquals(chr(QtiBinaryVersion::CURRENT_VERSION) . pack('S', 1) . 'M', $stream->getBinary());
    }

    public function testRetrieveCurrentLegacy()
    {
        $access = $this->createAccessMock(QtiBinaryVersion::CURRENT_VERSION, 'L');

        $subject = new QtiBinaryVersion();
        $subject->retrieve($access);

        $this->assertEquals(QtiBinaryVersion::CURRENT_VERSION, $subject->isCurrentVersion());
        $this->assertTrue($subject->isLegacy());
        $this->assertFalse($subject->isMaster());
    }

    public function testRetrieveCurrentMaster()
    {
        $access = $this->createAccessMock(QtiBinaryVersion::CURRENT_VERSION, 'M');

        $subject = new QtiBinaryVersion();
        $subject->retrieve($access);

        $this->assertEquals(QtiBinaryVersion::CURRENT_VERSION, $subject->isCurrentVersion());
        $this->assertFalse($subject->isLegacy());
        $this->assertTrue($subject->isMaster());
    }

    /**
     * @dataProvider legacyFeaturesToTest
     * @param int $versionNumber
     * @throws \qtism\common\storage\BinaryStreamAccessException
     */
    public function testLegacyFeatures(int $versionNumber, array $expectedFeatures)
    {
        $access = $this->createAccessMock($versionNumber, 'L');

        $subject = new QtiBinaryVersion();
        $subject->retrieve($access);

        $this->assertTrue($subject->isLegacy());
        $this->assertFalse($subject->isMaster());
        
        foreach($expectedFeatures as $featureMethod => $expectedSupported) {
            $this->assertEquals($expectedSupported, $subject->$featureMethod());
        }
    }

    public function legacyFeaturesToTest(): array
    {
        return $this->createFeatureArray(
            [
                QtiBinaryVersion::VERSION_ATTEMPTING => 'storesAttempting',
                QtiBinaryVersion::VERSION_MULTIPLE_SECTIONS => 'storesMultipleSections',
                QtiBinaryVersion::VERSION_DURATIONS => 'storesDurations',
                QtiBinaryVersion::VERSION_LAST_ACTION => 'storesLastAction',
                QtiBinaryVersion::VERSION_FORCE_BRANCHING_PRECONDITIONS => 'storesForceBranchingAndPreconditions',
                QtiBinaryVersion::VERSION_ALWAYS_ALLOW_JUMPS => 'storesAlwaysAllowJumps',
                QtiBinaryVersion::VERSION_TRACK_PATH => 'storesTrackPath',
                QtiBinaryVersion::VERSION_POSITION_INTEGER => 'storesPositionAndRouteCountAsInteger',
                QtiBinaryVersion::VERSION_FIRST_MASTER => 'isInBothBranches',
            ]
        );
    }

    /**
     * @dataProvider masterFeaturesToTest
     */
    public function testMasterFeatures(int $versionNumber, array $expectedFeatures)
    {
        $access = $this->createAccessMock($versionNumber, 'M');

        $subject = new QtiBinaryVersion();
        $subject->retrieve($access);

        $this->assertFalse($subject->isLegacy());
        $this->assertTrue($subject->isMaster());

        foreach($expectedFeatures as $featureMethod => $expectedSupported) {
            $this->assertEquals($expectedSupported, $subject->$featureMethod());
        }
    }

    public function masterFeaturesToTest(): array
    {
        return $this->createFeatureArray(
            [
                QtiBinaryVersion::VERSION_FIRST_MASTER => 'isInBothBranches',
            ]
        );
    }

    private function createFeatureArray(array $features): array
    {
        $return = [];

        foreach ($features as $versionNumber => $featureMethod) {
            $selectedFeatures = [];
            foreach ($features as $selectedVersionNumber => $selectedFeatureMethod) {
                $selectedFeatures [$selectedFeatureMethod] = $versionNumber >= $selectedVersionNumber;
            }
            $return[] = [$versionNumber, $selectedFeatures];
        }

        return $return;
    }

    public function createAccessMock(int $versionNumber, string $branch): QtiBinaryStreamAccess
    {
        $binary = chr($versionNumber);
        if ($versionNumber >= QtiBinaryVersion::VERSION_FIRST_MASTER) {
            $binary .= pack('S', 1) . $branch;
        }
        $stream = new MemoryStream($binary);
        $stream->open();
        return new QtiBinaryStreamAccess($stream, new FileSystemFileManager());
    }
}
