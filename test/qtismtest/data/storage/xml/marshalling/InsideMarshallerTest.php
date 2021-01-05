<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Inside;
use qtism\data\expressions\Variable;
use qtismtest\QtiSmTestCase;

/**
 * Class InsideMarshallerTest
 */
class InsideMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $subs = new ExpressionCollection();
        $subs[] = new Variable('pointVariable');

        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, [0, 0, 100, 20]);

        $component = new Inside($subs, $shape, $coords);
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);
        $this->assertEquals('inside', $element->nodeName);
        $this->assertEquals(implode(',', [0, 0, 100, 20]), $element->getAttribute('coords'));
        $this->assertEquals('rect', $element->getAttribute('shape'));
        $this->assertEquals(1, $element->getElementsByTagName('variable')->length);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<inside xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" shape="rect" coords="0,0,100,20">
				<variable identifier="pointVariable"/>
			</inside>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory()->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf(Inside::class, $component);
        $this->assertInstanceOf(QtiCoords::class, $component->getCoords());
        $this->assertIsInt($component->getShape());
        $this->assertEquals(QtiShape::RECT, $component->getShape());
        $this->assertEquals(1, count($component->getExpressions()));
    }
}
