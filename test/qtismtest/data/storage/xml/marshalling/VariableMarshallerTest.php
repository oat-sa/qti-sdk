<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\Variable;
use qtismtest\QtiSmTestCase;

class VariableMarshallerTest extends QtiSmTestCase
{
    public function testMarshallWeight()
    {
        $identifier = 'myVariable1';
        $weightIdentifier = 'myWeight1';

        $component = new Variable($identifier, $weightIdentifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('variable', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
        $this->assertEquals($weightIdentifier, $element->getAttribute('weightIdentifier'));
    }

    public function testMarshallNoWeight()
    {
        $identifier = 'myVariable1';

        $component = new Variable($identifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('variable', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
        $this->assertEquals('', $element->getAttribute('weightIdentifier')); // should have no weightIdentifier attr.
    }

    public function testUnmarshallWeight()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<variable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myVariable1" weightIdentifier="myWeight1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Variable::class, $component);
        $this->assertEquals('myVariable1', $component->getIdentifier());
        $this->assertEquals('myWeight1', $component->getWeightIdentifier());
    }

    public function testUnmarshallNoWeight()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<variable xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myVariable1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Variable::class, $component);
        $this->assertEquals('myVariable1', $component->getIdentifier());
        $this->assertEquals('', $component->getWeightIdentifier());
    }
}
