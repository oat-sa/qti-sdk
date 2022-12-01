<?php

namespace qtismtest\data\state;

use InvalidArgumentException;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntry;
use qtism\data\state\MatchTableEntryCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class MatchTableTest
 */
class MatchTableTest extends QtiSmTestCase
{
    public function testCreateNotEnoughMatchTableEntries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A MatchTable object must contain at least one MatchTableEntry object.');

        new MatchTable(new MatchTableEntryCollection());
    }

    public function testGetComponents(): void
    {
        $matchTable = new MatchTable(
            new MatchTableEntryCollection(
                [
                    new MatchTableEntry(1, 1.1),
                ]
            )
        );

        $components = $matchTable->getComponents();
        $this::assertCount(1, $components);
        $this::assertInstanceOf(MatchTableEntry::class, $components[0]);
    }
}
