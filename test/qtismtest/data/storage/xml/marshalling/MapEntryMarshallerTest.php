<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\state\MapEntry;
use qtismtest\QtiSmTestCase;

/**
 * Class MapEntryMarshallerTest
 */
class MapEntryMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $component = new MapEntry(1337, 1.377, true);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, [BaseType::INTEGER]);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('mapEntry', $element->nodeName);
        $this::assertEquals('1337', $element->getAttribute('mapKey'));
        $this::assertEquals('1.377', $element->getAttribute('mappedValue'));
        $this::assertEquals('true', $element->getAttribute('caseSensitive'));
    }

    public function testUnmarshall21()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" mapKey="1337" mappedValue="1.377" caseSensitive="true"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::INTEGER]);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(MapEntry::class, $component);
        $this::assertIsInt($component->getMapKey());
        $this::assertEquals(1337, $component->getMapKey());
        $this::assertIsFloat($component->getMappedValue());
        $this::assertEquals(1.377, $component->getMappedValue());
        $this::assertIsBool($component->isCaseSensitive());
        $this::assertEquals(true, $component->isCaseSensitive());
    }
}
