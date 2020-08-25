<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Match;
use qtism\data\expressions\Variable;
use qtism\data\rules\SetDefaultValue;
use qtismtest\QtiSmTestCase;

/**
 * Class SetDefaultValueMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class SetDefaultValueMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $variableExpr = new Variable('var1');
        $boolExpr = new BaseValue(BaseType::BOOLEAN, true);
        $matchExpr = new Match(new ExpressionCollection([$variableExpr, $boolExpr]));

        $setDefaultValue = new SetDefaultValue('tpl1', $matchExpr);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($setDefaultValue)->marshall($setDefaultValue);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<setDefaultValue identifier="tpl1"><match><variable identifier="var1"/><baseValue baseType="boolean">true</baseValue></match></setDefaultValue>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <setDefaultValue identifier="tpl1">
	            <match>
	                <variable identifier="var1"/>
	                <baseValue baseType="boolean">true</baseValue>
	            </match>
	        </setDefaultValue>
	    ');

        $setDefaultValue = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(SetDefaultValue::class, $setDefaultValue);
        $this->assertEquals('tpl1', $setDefaultValue->getIdentifier());
        $this->assertInstanceOf(Match::class, $setDefaultValue->getExpression());
    }
}
