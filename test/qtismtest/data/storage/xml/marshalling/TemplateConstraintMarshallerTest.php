<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Match;
use qtism\data\expressions\RandomInteger;
use qtism\data\rules\TemplateConstraint;
use qtismtest\QtiSmTestCase;

class TemplateConstraintMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $rand1 = new RandomInteger(0, 5);
        $rand2 = new RandomInteger(0, 5);
        $match = new Match(new ExpressionCollection([$rand1, $rand2]));

        $templateConstraint = new TemplateConstraint($match);

        $element = $this->getMarshallerFactory()->createMarshaller($templateConstraint)->marshall($templateConstraint);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals('<templateConstraint><match><randomInteger min="0" max="5"/><randomInteger min="0" max="5"/></match></templateConstraint>', $dom->saveXML($element));
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

        $templateConstraint = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf(TemplateConstraint::class, $templateConstraint);
        $this->assertInstanceOf(Match::class, $templateConstraint->getExpression());
    }
}
