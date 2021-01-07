<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\DefaultVal;
use qtismtest\QtiSmTestCase;

/**
 * Class DefaultValMarshallerTest
 */
class DefaultValMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myDefault1';

        $component = new DefaultVal($identifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('default', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<default xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myDefault1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(DefaultVal::class, $component);
        $this->assertEquals($component->getIdentifier(), 'myDefault1');
    }
}
