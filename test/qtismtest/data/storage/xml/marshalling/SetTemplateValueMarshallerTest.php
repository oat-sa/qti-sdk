<?php

declare(strict_types=1);

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\MatchOperator;
use qtism\data\expressions\Variable;
use qtism\data\rules\SetTemplateValue;
use qtismtest\QtiSmTestCase;

/**
 * Class SetTemplateValueMarshallerTest
 */
class SetTemplateValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $variableExpr = new Variable('var1');
        $boolExpr = new BaseValue(BaseType::BOOLEAN, true);
        $matchExpr = new MatchOperator(new ExpressionCollection([$variableExpr, $boolExpr]));

        $setTemplateValue = new SetTemplateValue('tpl1', $matchExpr);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($setTemplateValue)->marshall($setTemplateValue);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals('<setTemplateValue identifier="tpl1"><match><variable identifier="var1"/><baseValue baseType="boolean">true</baseValue></match></setTemplateValue>', $dom->saveXML($element));
    }

    public function testUnmarshall(): void
    {
        $element = $this->createDOMElement('
	        <setTemplateValue identifier="tpl1">
	            <match>
	                <variable identifier="var1"/>
	                <baseValue baseType="boolean">true</baseValue>
	            </match>
	        </setTemplateValue>
	    ');

        $setTemplateValue = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(SetTemplateValue::class, $setTemplateValue);
        $this::assertEquals('tpl1', $setTemplateValue->getIdentifier());
        $this::assertInstanceOf(MatchOperator::class, $setTemplateValue->getExpression());
    }
}
