<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Index;
use qtism\data\expressions\Variable;
use qtismtest\QtiSmTestCase;

/**
 * Class IndexMarshallerTest
 */
class IndexMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new Index(new ExpressionCollection([new Variable('orderedVar')]), 3);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('index', $element->nodeName);
        $this->assertEquals('3', $element->getAttribute('n'));

        $sub1 = $element->getElementsByTagName('variable')->item(0);
        $this->assertEquals('orderedVar', $sub1->getAttribute('identifier'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<index xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" n="3">
				<variable identifier="orderedVar"/>
			</index>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Index::class, $component);
        $this->assertEquals(3, $component->getN());

        $sub1 = $component->getExpressions();
        $sub1 = $sub1[0];
        $this->assertInstanceOf(Variable::class, $sub1);
        $this->assertEquals('orderedVar', $sub1->getIdentifier());
    }
}
