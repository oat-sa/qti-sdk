<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\MatchOperator;
use qtism\data\expressions\RandomInteger;
use qtism\data\rules\TemplateConstraint;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateConstraintMarshallerTest
 */
class TemplateConstraintMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $rand1 = new RandomInteger(0, 5);
        $rand2 = new RandomInteger(0, 5);
        $match = new MatchOperator(new ExpressionCollection([$rand1, $rand2]));

        $templateConstraint = new TemplateConstraint($match);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateConstraint)->marshall($templateConstraint);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this::assertEquals('<templateConstraint><match><randomInteger min="0" max="5"/><randomInteger min="0" max="5"/></match></templateConstraint>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <templateConstraint>
	            <match>
	                <randomInteger min="0" max="5"/>
	                <randomInteger min="0" max="5"/>
	            </match>
	        </templateConstraint>
	    ');

        $templateConstraint = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(TemplateConstraint::class, $templateConstraint);
        $this::assertInstanceOf(MatchOperator::class, $templateConstraint->getExpression());
    }
}
