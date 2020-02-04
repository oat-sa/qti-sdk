<?php

namespace qtismtest\data\state;

use qtism\data\state\MatchTableEntry;
use qtismtest\QtiSmTestCase;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntryCollection;

class MatchTableTest extends QtiSmTestCase
{
    public function testCreateNotEnoughMatchTableEntries()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "A MatchTable object must contain at least one MatchTableEntry object."
        );
        
        new MatchTable(new MatchTableEntryCollection());
    }
    
    public function testGetComponents()
    {
        $matchTable = new MatchTable(
            new MatchTableEntryCollection(
                array(
                    new MatchTableEntry(1, 1.1)
                )
            )
        );
        
        $components = $matchTable->getComponents();
        $this->assertCount(1, $components);
        $this->assertInstanceOf('qtism\\data\\state\\MatchTableEntry', $components[0]);
    }
}
