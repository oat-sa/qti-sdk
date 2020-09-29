<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\state\AreaMapEntry;
use qtismtest\QtiSmTestCase;

/**
 * Class AreaMapEntryMarshallerTest
 */
class AreaMapEntryMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $mappedValue = 1.337;
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [0, 20, 100, 0]);
        $component = new AreaMapEntry($shape, $coords, $mappedValue);

        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('areaMapEntry', $element->nodeName);
        $this->assertEquals('rect', $element->getAttribute('shape'));
        $this->assertEquals('0,20,100,0', $element->getAttribute('coords'));
        $this->assertEquals('1.337', $element->getAttribute('mappedValue'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="0, 20, 100, 0" mappedValue="1.337"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(AreaMapEntry::class, $component);
        $this->assertInstanceOf(QtiCoords::class, $component->getCoords());
        $this->assertEquals([0, 20, 100, 0], $component->getCoords()->getArrayCopy());
        $this->assertEquals(QtiShape::RECT, $component->getShape());
        $this->assertInternalType('float', $component->getMappedValue());
        $this->assertEquals(1.337, $component->getMappedValue());
    }
}
