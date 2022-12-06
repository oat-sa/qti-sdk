<?php

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\data\state\InterpolationTable;
use qtism\data\state\InterpolationTableEntry;
use qtism\data\state\InterpolationTableEntryCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class InterpolationTableTest
 */
class InterpolationTableTest extends QtiSmTestCase
{
    public function testCreateNotEnoughInterpolationEntries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An InterpolationTable object must contain at least one InterpolationTableEntry object.');

        new InterpolationTable(new InterpolationTableEntryCollection());
    }

    public function testGetComponents(): void
    {
        $interpolationTable = new InterpolationTable(
            new InterpolationTableEntryCollection(
                [
                    new InterpolationTableEntry(0.5, 0.5),
                ]
            )
        );

        $components = $interpolationTable->getComponents();
        $this::assertCount(1, $components);
        $this::assertInstanceOf(InterpolationTableEntry::class, $components[0]);
    }
}
