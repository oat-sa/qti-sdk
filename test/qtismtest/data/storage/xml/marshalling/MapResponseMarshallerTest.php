<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\expressions\MapResponse;
use qtismtest\QtiSmTestCase;

class MapResponseMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myMapResponse1';

        $component = new MapResponse($identifier);
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('mapResponse', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapResponse xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myMapResponse1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(MapResponse::class, $component);
        $this->assertEquals($component->getIdentifier(), 'myMapResponse1');
    }
}
