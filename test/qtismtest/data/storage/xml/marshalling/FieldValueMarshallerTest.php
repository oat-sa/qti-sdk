<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\FieldValue;
use qtism\data\expressions\Variable;
use qtismtest\QtiSmTestCase;

class FieldValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $fieldIdentifier = "myField";

        $component = new FieldValue(new ExpressionCollection([new Variable('recordVar')]), $fieldIdentifier);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);
        $this->assertEquals('fieldValue', $element->nodeName);
        $this->assertEquals($fieldIdentifier, $element->getAttribute('fieldIdentifier'));

        $sub1 = $element->getElementsByTagName('variable')->item(0);
        $this->assertEquals('recordVar', $sub1->getAttribute('identifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<fieldValue xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" fieldIdentifier="myField">
				<variable identifier="recordVar"/>
			</fieldValue>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(FieldValue::class, $component);
        $this->assertEquals('myField', $component->getFieldIdentifier());

        $sub1 = $component->getExpressions();
        $sub1 = $sub1[0];
        $this->assertInstanceOf(Variable::class, $sub1);
        $this->assertEquals('recordVar', $sub1->getIdentifier());
    }
}
