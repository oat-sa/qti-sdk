<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\MathFunctions;
use qtism\data\expressions\operators\MathOperator;
use qtismtest\QtiSmTestCase;

/**
 * Class MathOperatorMarshallerTest
 */
class MathOperatorMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $subExpr = new ExpressionCollection([new BaseValue(BaseType::FLOAT, 1.57)]); // 90°
        $name = MathFunctions::SIN;
        $component = new MathOperator($subExpr, $name);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('mathOperator', $element->nodeName);
        $this::assertEquals('sin', $element->getAttribute('name'));

        $subExprElts = $element->getElementsByTagName('baseValue');
        $this::assertEquals(1, $subExprElts->length);
        $this::assertEquals('float', $subExprElts->item(0)->getAttribute('baseType'));
        $this::assertEquals('1.57', $subExprElts->item(0)->nodeValue);
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<mathOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" name="sin">
				<baseValue baseType="float">1.57</baseValue>
			</mathOperator>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(MathOperator::class, $component);
        $this::assertEquals(MathFunctions::SIN, $component->getName());

        $subExpr = $component->getExpressions();
        $this::assertCount(1, $subExpr);
        $this::assertInstanceOf(BaseValue::class, $subExpr[0]);
        $this::assertIsFloat($subExpr[0]->getValue());
        $this::assertEquals(1.57, $subExpr[0]->getValue());
        $this::assertEquals(BaseType::FLOAT, $subExpr[0]->getBaseType());
    }
}
