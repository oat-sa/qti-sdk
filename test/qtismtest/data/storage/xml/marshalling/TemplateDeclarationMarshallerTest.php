<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\DefaultValue;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateDeclarationMarshallerTest
 */
class TemplateDeclarationMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $values = new ValueCollection([new Value('tplx', BaseType::IDENTIFIER)]);
        $defaultValue = new DefaultValue($values);
        $templateDeclaration = new TemplateDeclaration('tpl1', BaseType::IDENTIFIER, Cardinality::SINGLE, $defaultValue);
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateDeclaration)->marshall($templateDeclaration);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
	        <templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>
	    ');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateDeclaration::class, $component);
        $this::assertEquals('tpl1', $component->getIdentifier());
        $this::assertEquals(Cardinality::SINGLE, $component->getCardinality());
        $this::assertEquals(BaseType::IDENTIFIER, $component->getBaseType());

        $default = $component->getDefaultValue();
        $this::assertInstanceOf(DefaultValue::class, $default);
        $values = $default->getValues();
        $this::assertCount(1, $values);
        $this::assertEquals('tplx', $values[0]->getValue());
    }
}
