<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\state\MapEntry;
use qtism\data\state\MapEntryCollection;
use qtism\data\state\Mapping;
use qtismtest\QtiSmTestCase;

/**
 * Class MappingMarshallerTest
 */
class MappingMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal()
    {
        $defaultValue = 6.66;
        $mapEntries = new MapEntryCollection();
        $mapEntries[] = new MapEntry(1337, 1.337, false);

        $component = new Mapping($mapEntries, $defaultValue);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, [BaseType::INTEGER]);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('mapping', $element->nodeName);
        $this->assertEquals($defaultValue . '', $element->getAttribute('defaultValue'));
        $this->assertEquals('', $element->getAttribute('lowerBound')); // empty
        $this->assertEquals('', $element->getAttribute('upperBound'));

        $mapEntryElts = $element->getElementsByTagName('mapEntry');
        $this->assertEquals(1, $mapEntryElts->length);
        $mapEntryElt = $mapEntryElts->item(0);
        $this->assertEquals('1337', $mapEntryElt->getAttribute('mapKey'));
        $this->assertEquals('1.337', $mapEntryElt->getAttribute('mappedValue'));
        $this->assertEquals('false', $mapEntryElt->getAttribute('caseSensitive'));
    }

    public function testUnmarshallMinimal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<mapping xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" defaultValue="6.66">
				<mapEntry mapKey="1337" mappedValue="1.337" caseSensitive="false"/>
			</mapping>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, [BaseType::INTEGER]);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Mapping::class, $component);
        $this->assertFalse($component->hasLowerBound());
        $this->assertFalse($component->hasUpperBound());

        $mapEntries = $component->getMapEntries();
        $this->assertEquals(1, count($mapEntries));
        $this->assertIsInt($mapEntries[0]->getMapKey());
        $this->assertEquals(1337, $mapEntries[0]->getMapKey());
        $this->assertIsFloat($mapEntries[0]->getMappedValue());
        $this->assertEquals(1.337, $mapEntries[0]->getMappedValue());
        $this->assertFalse($mapEntries[0]->isCaseSensitive());
    }
}
