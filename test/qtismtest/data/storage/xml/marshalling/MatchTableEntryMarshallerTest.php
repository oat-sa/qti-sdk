<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\state\MatchTableEntry;
use qtismtest\QtiSmTestCase;

/**
 * Class MatchTableEntryMarshallerTest
 */
class MatchTableEntryMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $sourceValue = 2;
        $targetValue = 'http://www.rdfabout.com';
        $component = new MatchTableEntry($sourceValue, $targetValue);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, [BaseType::URI]);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('matchTableEntry', $element->nodeName);
        $this::assertEquals('' . $sourceValue, $element->getAttribute('sourceValue'));
        $this::assertEquals($targetValue, $element->getAttribute('targetValue'));
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<matchTableEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" sourceValue="2" targetValue="http://www.mysite.com"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::URI]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(MatchTableEntry::class, $component);
        $this::assertEquals(2, $component->getSourceValue());
        $this::assertEquals('http://www.mysite.com', $component->getTargetValue());
    }
}
