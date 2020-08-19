<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\LookupOutcomeValue;
use qtismtest\QtiSmTestCase;

class LookupOutcomeValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new LookupOutcomeValue('myVariable1', new BaseValue(BaseType::STRING, 'a value'));

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('lookupOutcomeValue', $element->nodeName);
        $this->assertEquals('myVariable1', $element->getAttribute('identifier'));
        $this->assertEquals(1, $element->getElementsByTagName('baseValue')->length);
        $this->assertEquals('a value', $element->getElementsByTagName('baseValue')->item(0)->nodeValue);
        $this->assertEquals('string', $element->getElementsByTagName('baseValue')->item(0)->getAttribute('baseType'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<lookupOutcomeValue xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myVariable1">
				<baseValue baseType="string">a value</baseValue>
			</lookupOutcomeValue>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(LookupOutcomeValue::class, $component);
        $this->assertInstanceOf(BaseValue::class, $component->getExpression());
        $this->assertInternalType('string', $component->getExpression()->getValue());
        $this->assertEquals('a value', $component->getExpression()->getValue());
        $this->assertEquals(BaseType::STRING, $component->getExpression()->getBaseType());
    }
}
