<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtismtest\QtiSmTestCase;

/**
 * Class BaseValueMarshallerTest
 */
class BaseValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $baseType = BaseType::FLOAT;
        $value = 27.11;

        $component = new BaseValue($baseType, $value);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('baseValue', $element->nodeName);
        $this->assertEquals('float', $element->getAttribute('baseType'));
        $this->assertEquals($value . '', $element->nodeValue);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<baseValue xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="float">27.11</baseValue>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(BaseValue::class, $component);
        $this->assertEquals(BaseType::FLOAT, $component->getBaseType());
        $this->assertIsFloat($component->getValue());
        $this->assertEquals(27.11, $component->getValue());
    }

    public function testUnmarshallCDATA()
    {
        $element = $this->createDOMElement('<baseValue baseType="string"><![CDATA[A string...]]></baseValue>');
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);

        $this->assertInstanceOf(BaseValue::class, $component);
        $this->assertEquals(BaseType::STRING, $component->getBaseType());
        $this->assertEquals('A string...', $component->getValue());
    }
}
