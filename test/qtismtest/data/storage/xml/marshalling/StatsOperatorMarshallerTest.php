<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Statistics;
use qtism\data\expressions\operators\StatsOperator;
use qtismtest\QtiSmTestCase;

/**
 * Class StatsOperatorMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class StatsOperatorMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subExpr = new ExpressionCollection([new BaseValue(BaseType::FLOAT, 12.5468)]);
        $name = Statistics::POP_VARIANCE;
        $component = new StatsOperator($subExpr, $name);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('statsOperator', $element->nodeName);
        $this->assertEquals('popVariance', $element->getAttribute('name'));

        $subExprElts = $element->getElementsByTagName('baseValue');
        $this->assertEquals(1, $subExprElts->length);
        $this->assertEquals('float', $subExprElts->item(0)->getAttribute('baseType'));
        $this->assertEquals('12.5468', $subExprElts->item(0)->nodeValue);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<statsOperator xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" name="popVariance">
				<baseValue baseType="float">12.5468</baseValue>
			</statsOperator>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(StatsOperator::class, $component);
        $this->assertEquals(Statistics::POP_VARIANCE, $component->getName());

        $subExpr = $component->getExpressions();
        $this->assertEquals(1, count($subExpr));
        $this->assertInstanceOf(BaseValue::class, $subExpr[0]);
        $this->assertInternalType('float', $subExpr[0]->getValue());
        $this->assertEquals(12.5468, $subExpr[0]->getValue());
        $this->assertEquals(BaseType::FLOAT, $subExpr[0]->getBaseType());
    }
}
