<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\DefaultValue;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\data\state\VariableDeclaration;
use qtismtest\QtiSmTestCase;

/**
 * Class VariableDeclarationMarshallerTest
 */
class VariableDeclarationMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new VariableDeclaration('myVar', BaseType::INTEGER, Cardinality::SINGLE);

        $values = new ValueCollection();
        $values[] = new Value(10, BaseType::INTEGER);
        $component->setDefaultValue(new DefaultValue($values));

        $defaultValue = new DefaultValue($values);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('variableDeclaration', $element->nodeName);
        $this::assertEquals('myVar', $element->getAttribute('identifier'));
        $this::assertEquals('integer', $element->getAttribute('baseType'));

        $defaultValueElts = $element->getElementsByTagName('defaultValue');
        $this::assertEquals(1, $defaultValueElts->length);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<variableDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myVar" baseType="integer" cardinality="single">
				<defaultValue>
					<value>10</value>
				</defaultValue>
			</variableDeclaration>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(VariableDeclaration::class, $component);
        $this::assertEquals('myVar', $component->getIdentifier());
        $this::assertEquals(BaseType::INTEGER, $component->getBaseType());
        $this::assertEquals(Cardinality::SINGLE, $component->getCardinality());
        $this::assertInstanceOf(DefaultValue::class, $component->getDefaultValue());

        $values = $component->getDefaultValue()->getValues();
        $this::assertEquals(1, count($values));
        $this::assertIsInt($values[0]->getValue());
    }
}
