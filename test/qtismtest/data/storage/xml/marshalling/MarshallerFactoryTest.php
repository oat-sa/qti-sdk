<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\state\AreaMapEntry;
use qtism\data\storage\xml\marshalling\Qti21MarshallerFactory;
use qtismtest\QtiSmTestCase;
use stdClass;
use qtism\data\storage\xml\marshalling\AreaMapEntryMarshaller;

/**
 * Class MarshallerFactyoryTest
 */
class MarshallerFactyoryTest extends QtiSmTestCase
{
    public function testFromDomElement()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<areaMapEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="0, 20, 100, 0" mappedValue="1.337"/>');
        $element = $dom->documentElement;

        $factory = new Qti21MarshallerFactory();
        $marshaller = $factory->createMarshaller($element);
        $this->assertInstanceOf(AreaMapEntryMarshaller::class, $marshaller);
    }

    public function testFromQtiComponent()
    {
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [0, 20, 100, 0]);
        $component = new AreaMapEntry($shape, $coords, 1.337);

        $factory = new Qti21MarshallerFactory();
        $marshaller = $factory->createMarshaller($component);
        $this->assertInstanceOf(AreaMapEntryMarshaller::class, $marshaller);
    }

    public function testFromInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $component = new stdClass();
        $factory = new Qti21MarshallerFactory();
        $marshaller = $factory->createMarshaller($component);
    }
}
