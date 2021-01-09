<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\state\Weight;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class WeightMarshallerTest
 */
class WeightMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myWeight1';
        $value = 3.45;

        $component = new Weight($identifier, $value);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('weight', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
        $this->assertEquals($value . '', $element->getAttribute('value'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myWeight1" value="3.45"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Weight::class, $component);
        $this->assertEquals('myWeight1', $component->getIdentifier());
        $this->assertEquals(3.45, $component->getValue());
    }

    public function testUnmarshallWrongIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="999" value="3.45"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The value of 'identifier' from element 'weight' is not a valid QTI Identifier.");

        $marshaller->unmarshall($element);
    }

    public function testUnmarshallNonFloatValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="my-identifier" value="lll"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The value of attribute 'value' from element 'weight' cannot be converted into a float.");

        $marshaller->unmarshall($element);
    }

    public function testUnmarshallNoValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="my-identifier"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'value' is missing from element 'weight'.");

        $marshaller->unmarshall($element);
    }

    public function testUnmarshallMissingIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" value="1.1"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'identifier' is missing from element 'weight'.");

        $marshaller->unmarshall($element);
    }
}
