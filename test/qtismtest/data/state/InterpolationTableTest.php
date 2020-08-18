<?php

namespace qtismtest\data\state;

use qtism\data\state\InterpolationTable;
use qtism\data\state\InterpolationTableEntry;
use qtism\data\state\InterpolationTableEntryCollection;
use qtismtest\QtiSmTestCase;

class InterpolationTableTest extends QtiSmTestCase
{
    public function testCreateNotEnoughInterpolationEntries()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'An InterpolationTable object must contain at least one InterpolationTableEntry object.'
        );

        new InterpolationTable(new InterpolationTableEntryCollection());
    }

    public function testGetComponents()
    {
        $interpolationTable = new InterpolationTable(
            new InterpolationTableEntryCollection(
                [
                    new InterpolationTableEntry(0.5, 0.5),
                ]
            )
        );

        $components = $interpolationTable->getComponents();
        $this->assertCount(1, $components);
        $this->assertInstanceOf(InterpolationTableEntry::class, $components[0]);
    }
}
