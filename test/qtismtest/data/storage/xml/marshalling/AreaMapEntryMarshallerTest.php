<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\state\AreaMapEntry;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class AreaMapEntryMarshallerTest
 */
class AreaMapEntryMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $mappedValue = 1.337;
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [0, 20, 100, 0]);
        $component = new AreaMapEntry($shape, $coords, $mappedValue);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('areaMapEntry', $element->nodeName);
        $this::assertEquals('rect', $element->getAttribute('shape'));
        $this::assertEquals('0,20,100,0', $element->getAttribute('coords'));
        $this::assertEquals('1.337', $element->getAttribute('mappedValue'));
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="0, 20, 100, 0" mappedValue="1.337"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AreaMapEntry::class, $component);
        $this::assertInstanceOf(QtiCoords::class, $component->getCoords());
        $this::assertEquals([0, 20, 100, 0], $component->getCoords()->getArrayCopy());
        $this::assertEquals(QtiShape::RECT, $component->getShape());
        $this::assertIsFloat($component->getMappedValue());
        $this::assertEquals(1.337, $component->getMappedValue());
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoMappedValue(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="0, 20, 100, 0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'mappedValue' is missing from element 'areaMapEntry'.");

        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallWrongCoords(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="xxx" mappedValue="1.337"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The attribute 'coords' with value 'xxx' has an invalid value.");

        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoCoords(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" mappedValue="1.337"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'coords' is missing from element 'areaMapEntry'.");

        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidShape(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rectangle" mappedValue="1.337" coords="0, 20, 100, 0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The 'shape' attribute value 'rectangle' is not a valid value to represent QTI shapes.");

        $component = $marshaller->unmarshall($element);
    }

    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallNoShape(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" mappedValue="1.337" coords="0, 20, 100, 0"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'shape' is missing from element 'areaMapEntry'.");

        $component = $marshaller->unmarshall($element);
    }
}
