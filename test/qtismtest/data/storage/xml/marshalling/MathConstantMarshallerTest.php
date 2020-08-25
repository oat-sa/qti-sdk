<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\MathConstant;
use qtism\data\expressions\MathEnumeration;
use qtismtest\QtiSmTestCase;

/**
 * Class MathConstantMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class MathConstantMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $name = MathEnumeration::PI;

        $component = new MathConstant($name);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('mathConstant', $element->nodeName);
        $this->assertEquals('pi', $element->getAttribute('name'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mathConstant xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" name="pi"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(MathConstant::class, $component);
        $this->assertEquals($component->getName(), MathEnumeration::PI);
    }
}
