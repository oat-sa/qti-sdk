<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\NullValue;
use qtism\data\state\TemplateDefault;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateDefaultMarshallerTest
 */
class TemplateDefaultMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $templateIdentifier = 'myTemplate1';
        $expression = new NullValue();

        $component = new TemplateDefault($templateIdentifier, $expression);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('templateDefault', $element->nodeName);
        $this::assertEquals($templateIdentifier, $element->getAttribute('templateIdentifier'));

        $expressionElt = $element->getElementsByTagName('null');
        $this::assertEquals(1, $expressionElt->length);
        $expressionElt = $expressionElt->item(0);
        $this::assertEquals('null', $expressionElt->nodeName);
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<templateDefault xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" templateIdentifier="myTemplate1">
				<null/>
			</templateDefault>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(TemplateDefault::class, $component);
        $this::assertEquals('myTemplate1', $component->getTemplateIdentifier());
        $this::assertInstanceOf(NullValue::class, $component->getExpression());
    }
}
