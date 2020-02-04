<?php

namespace qtismtest\data\state;

use qtism\data\state\InterpolationTableEntry;
use qtism\data\state\InterpolationTableEntryCollection;
use qtismtest\QtiSmTestCase;
use qtism\data\state\InterpolationTable;

class InterpolationTableTest extends QtiSmTestCase
{
    public function testCreateNotEnoughInterpolationEntries()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "An InterpolationTable object must contain at least one InterpolationTableEntry object."
        );
        
        new InterpolationTable(new InterpolationTableEntryCollection());
    }
    
    public function testGetComponents()
    {
        $interpolationTable = new InterpolationTable(
            new InterpolationTableEntryCollection(
                array(
                    new InterpolationTableEntry(0.5, 0.5)
                )
            )
        );
        
        $components = $interpolationTable->getComponents();
        $this->assertCount(1, $components);
        $this->assertInstanceOf('qtism\\data\\state\InterpolationTableEntry', $components[0]);
    }
}
