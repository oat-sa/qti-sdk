<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\RoundingMode;
use qtism\data\expressions\operators\RoundTo;
use qtismtest\QtiSmTestCase;

/**
 * Class RoundToMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class RoundToMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subExpr = new ExpressionCollection([new BaseValue(BaseType::FLOAT, 24.3333)]);
        $component = new RoundTo($subExpr, 2);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('roundTo', $element->nodeName);
        $this->assertEquals('2', $element->getAttribute('figures'));

        $subExprElts = $element->getElementsByTagName('baseValue');
        $this->assertEquals(1, $subExprElts->length);
        $this->assertEquals('float', $subExprElts->item(0)->getAttribute('baseType'));
        $this->assertEquals('24.3333', $subExprElts->item(0)->nodeValue);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<roundTo xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" figures="2">
				<baseValue baseType="float">24.3333</baseValue>
			</roundTo>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(RoundTo::class, $component);
        $this->assertEquals(2, $component->getFigures());
        $this->assertEquals(RoundingMode::SIGNIFICANT_FIGURES, $component->getRoundingMode());

        $subExpr = $component->getExpressions();
        $this->assertEquals(1, count($subExpr));
        $this->assertInstanceOf(BaseValue::class, $subExpr[0]);
        $this->assertInternalType('float', $subExpr[0]->getValue());
        $this->assertEquals(24.3333, $subExpr[0]->getValue());
        $this->assertEquals(BaseType::FLOAT, $subExpr[0]->getBaseType());
    }
}
