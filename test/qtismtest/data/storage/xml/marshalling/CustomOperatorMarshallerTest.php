<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\CustomOperator;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmTestCase;

/**
 * Class CustomOperatorMarshallerTest
 */
class CustomOperatorMarshallerTest extends QtiSmTestCase
{
    public function testMarshallNoLaxContent()
    {
        $int1 = new BaseValue(BaseType::INTEGER, 1);
        $int2 = new BaseValue(BaseType::INTEGER, 1);
        $equal = new Equal(new ExpressionCollection([$int1, $int2]));

        $customOperator = new CustomOperator(new ExpressionCollection([$equal]), '<customOperator><equal toleranceMode="exact"><baseValue baseType="integer">1</baseValue><baseValue baseType="integer">1</baseValue></equal></customOperator>');
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($customOperator)->marshall($customOperator);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<customOperator><equal toleranceMode="exact"><baseValue baseType="integer">1</baseValue><baseValue baseType="integer">1</baseValue></equal></customOperator>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('<customOperator><equal toleranceMode="exact"><baseValue baseType="integer">1</baseValue><baseValue baseType="integer">1</baseValue></equal></customOperator>');

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(CustomOperator::class, $component);

        $expressions = $component->getExpressions();
        $this::assertCount(1, $expressions);
        $this::assertInstanceOf(Equal::class, $expressions[0]);
        $this::assertEquals(ToleranceMode::EXACT, $expressions[0]->getToleranceMode());

        $subExpressions = $expressions[0]->getExpressions();
        $this::assertCount(2, $subExpressions);
        $this::assertInstanceOf(BaseValue::class, $subExpressions[0]);
        $this::assertEquals(BaseType::INTEGER, $subExpressions[0]->getBaseType());
        $this::assertEquals(1, $subExpressions[0]->getValue());
        $this::assertInstanceOf(BaseValue::class, $subExpressions[1]);
        $this::assertEquals(BaseType::INTEGER, $subExpressions[1]->getBaseType());
        $this::assertEquals(1, $subExpressions[1]->getValue());
    }
}
