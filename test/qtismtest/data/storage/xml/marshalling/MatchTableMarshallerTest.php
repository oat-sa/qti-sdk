<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPair;
use qtism\common\enums\BaseType;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntry;
use qtism\data\state\MatchTableEntryCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class MatchTableMarshallerTest
 */
class MatchTableMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $matchTableEntryCollection = new MatchTableEntryCollection();
        $matchTableEntryCollection[] = new MatchTableEntry(1, new QtiPair('A', 'B'));
        $matchTableEntryCollection[] = new MatchTableEntry(2, new QtiPair('A', 'C'));

        $component = new MatchTable($matchTableEntryCollection);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, [BaseType::PAIR]);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('matchTable', $element->nodeName);

        $entryElements = $element->getElementsByTagName('matchTableEntry');
        $this::assertEquals(2, $entryElements->length);
        $entry = $entryElements->item(0);
        $this::assertEquals('A B', $entry->getAttribute('targetValue'));
        $this::assertEquals('matchTableEntry', $entry->nodeName);
        $this::assertEquals('1', $entry->getAttribute('sourceValue'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<matchTable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<matchTableEntry sourceValue="1" targetValue="A B"/>
				<matchTableEntry sourceValue="2" targetValue="A C"/>
			</matchTable>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::DIRECTED_PAIR]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(MatchTable::class, $component);
        $matchTableEntries = $component->getMatchTableEntries();
        $this::assertEquals(2, count($matchTableEntries));
        $entry = $matchTableEntries[0];
        $this::assertInstanceOf(MatchTableEntry::class, $entry);
        $this::assertEquals(1, $entry->getSourceValue());
        $this::assertInstanceOf(QtiDirectedPair::class, $entry->getTargetValue());
    }
}
